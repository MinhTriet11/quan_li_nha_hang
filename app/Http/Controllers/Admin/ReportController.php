<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bill;
use App\Models\Branch;
use App\Models\BranchIngredient;
use App\Models\Category;
use App\Models\ExportDetail;
use App\Models\ExportReceipts;
use App\Models\ImportDetail;
use App\Models\ImportReceipts;
use App\Models\Ingredient;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderDetail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Báo cáo doanh thu theo ngày / tháng / năm + lọc chi nhánh.
     * - daily   : thống kê từng ngày trong khoảng [from, to]
     * - monthly : gộp theo tháng (YYYY-MM)
     * - yearly  : gộp theo năm (YYYY)
     */
    public function revenue(Request $request)
    {
        $branches = Branch::orderBy('name')->get();

        $branchId   = $request->get('branch_id');          // null = tất cả chi nhánh
        $reportType = $request->get('report_type', 'daily'); // daily | monthly | yearly
        $fromDate   = $request->get('from_date') ?: now()->subDays(13)->toDateString();
        $toDate     = $request->get('to_date')   ?: now()->toDateString();

        // Query cơ sở: chỉ tính bill đã thanh toán (status = 2) trong khoảng ngày
        $billsQuery = Bill::query()
            ->where('status', 2)
            ->whereBetween('pay_time', [
                Carbon::parse($fromDate)->startOfDay(),
                Carbon::parse($toDate)->endOfDay(),
            ])
            ->whereHas('order', function ($q) use ($branchId) {
                if ($branchId) {
                    $q->where('id_br', $branchId);
                }
            });

        $bills = (clone $billsQuery)->with('order')->get();

        // Tổng quan
        $totalRevenue   = (float) $bills->sum('total');
        $totalBills     = $bills->count();
        $avgBill        = $totalBills > 0 ? $totalRevenue / $totalBills : 0;

        // Doanh thu theo phương thức thanh toán
        $revenueByMethod = $bills->groupBy('pay_method')->map(function ($items, $key) {
            return [
                'method' => $key ?: 'Khác',
                'count'  => $items->count(),
                'total'  => (float) $items->sum('total'),
            ];
        })->values();

        // Gom nhóm theo loại báo cáo
        $grouped = $bills->groupBy(function ($bill) use ($reportType) {
            $time = $bill->pay_time;
            if (!$time) {
                return 'unknown';
            }
            return match ($reportType) {
                'yearly'  => $time->format('Y'),
                'monthly' => $time->format('Y-m'),
                default   => $time->format('Y-m-d'),
            };
        });

        $labels = [];
        $values = [];
        $counts = [];

        if ($reportType === 'daily') {
            // Lấp đầy các ngày trong khoảng, kể cả ngày không có doanh thu
            $start = Carbon::parse($fromDate);
            $end   = Carbon::parse($toDate);
            while ($start->lte($end)) {
                $key = $start->format('Y-m-d');
                $labels[] = $start->format('d/m');
                $values[] = (float) ($grouped[$key]->sum('total') ?? 0);
                $counts[] = (int) ($grouped[$key]->count() ?? 0);
                $start->addDay();
            }
        } elseif ($reportType === 'monthly') {
            $start = Carbon::parse($fromDate)->startOfMonth();
            $end   = Carbon::parse($toDate)->endOfMonth();
            while ($start->lte($end)) {
                $key = $start->format('Y-m');
                $labels[] = 'T' . $start->format('m/Y');
                $values[] = (float) ($grouped[$key]->sum('total') ?? 0);
                $counts[] = (int) ($grouped[$key]->count() ?? 0);
                $start->addMonth();
            }
        } else {
            $start = Carbon::parse($fromDate)->startOfYear();
            $end   = Carbon::parse($toDate)->endOfYear();
            while ($start->lte($end)) {
                $key = (string) $start->format('Y');
                $labels[] = $key;
                $values[] = (float) ($grouped[$key]->sum('total') ?? 0);
                $counts[] = (int) ($grouped[$key]->count() ?? 0);
                $start->addYear();
            }
        }

        // Doanh thu theo chi nhánh (trong cùng khoảng)
        $revenueByBranch = (clone $billsQuery)
            ->join('orders', 'orders.id_order', '=', 'bill.id_order')
            ->join('branch', 'branch.id_br', '=', 'orders.id_br')
            ->select(
                'orders.id_br',
                'branch.name as branch_name',
                DB::raw('COUNT(*) as bill_count'),
                DB::raw('SUM(bill.total) as revenue')
            )
            ->groupBy('orders.id_br', 'branch.name')
            ->orderByDesc('revenue')
            ->get()
            ->map(function ($row) {
                return [
                    'id_br'       => $row->id_br,
                    'branch_name' => $row->branch_name,
                    'bill_count'  => (int) $row->bill_count,
                    'revenue'     => (float) $row->revenue,
                ];
            });

        // Top 10 hóa đơn lớn nhất
        $topBills = $bills->sortByDesc('total')->take(10)->values();

        return view('admin.pages.report_revenue', compact(
            'branches',
            'branchId',
            'reportType',
            'fromDate',
            'toDate',
            'totalRevenue',
            'totalBills',
            'avgBill',
            'labels',
            'values',
            'counts',
            'revenueByMethod',
            'revenueByBranch',
            'topBills'
        ));
    }

    /**
     * Báo cáo món ăn bán chạy.
     */
    public function bestSelling(Request $request)
    {
        $branches  = Branch::orderBy('name')->get();
        $categories = Category::orderBy('name')->get();

        $branchId   = $request->get('branch_id');
        $categoryId = $request->get('category_id');
        $fromDate   = $request->get('from_date') ?: now()->subDays(29)->toDateString();
        $toDate     = $request->get('to_date')   ?: now()->toDateString();
        $sortBy     = $request->get('sort_by', 'quantity'); // quantity | revenue
        $topLimit   = (int) $request->get('limit', 10);
        $topLimit   = $topLimit > 0 && $topLimit <= 50 ? $topLimit : 10;

        // Lấy tất cả order_detail thuộc các order đã hoàn thành (status = 3) + có bill hợp lệ
        $detailsQuery = OrderDetail::query()
            ->select(
                'order_detail.id_food',
                DB::raw('SUM(order_detail.quantity) as total_qty'),
                DB::raw('SUM(order_detail.quantity * order_detail.price) as total_revenue')
            )
            ->join('orders', 'orders.id_order', '=', 'order_detail.id_order')
            ->join('menu_items', 'menu_items.id_food', '=', 'order_detail.id_food')
            ->whereIn('orders.status', [3]) // chỉ order đã hoàn thành
            ->whereBetween('orders.order_time', [
                Carbon::parse($fromDate)->startOfDay(),
                Carbon::parse($toDate)->endOfDay(),
            ])
            ->when($branchId, function ($q) use ($branchId) {
                $q->where('orders.id_br', $branchId);
            })
            ->when($categoryId, function ($q) use ($categoryId) {
                $q->where('menu_items.id_cate', $categoryId);
            })
            ->groupBy('order_detail.id_food');

        $rows = $detailsQuery->get()->map(function ($row) {
            $menuItem = MenuItem::with('category')->find($row->id_food);
            return [
                'id_food'      => $row->id_food,
                'name'         => $menuItem->name ?? '—',
                'image'        => $menuItem->image ?? null,
                'category'     => $menuItem->category->name ?? '—',
                'price'        => (float) ($menuItem->price ?? 0),
                'total_qty'    => (int) $row->total_qty,
                'total_revenue'=> (float) $row->total_revenue,
            ];
        });

        // Sắp xếp
        $rows = $sortBy === 'revenue'
            ? $rows->sortByDesc('total_revenue')->values()
            : $rows->sortByDesc('total_qty')->values();

        $topItems = $rows->take($topLimit)->values();
        $totalQtyAll     = (int) $rows->sum('total_qty');
        $totalRevenueAll = (float) $rows->sum('total_revenue');

        // Phân bố doanh thu theo danh mục
        $revenueByCategory = $rows->groupBy('category')->map(function ($items, $cat) {
            return [
                'category'   => $cat ?: 'Khác',
                'quantity'   => (int) $items->sum('total_qty'),
                'revenue'    => (float) $items->sum('total_revenue'),
            ];
        })->sortByDesc('revenue')->values();

        return view('admin.pages.report_best_selling', compact(
            'branches',
            'categories',
            'branchId',
            'categoryId',
            'fromDate',
            'toDate',
            'sortBy',
            'topLimit',
            'topItems',
            'rows',
            'totalQtyAll',
            'totalRevenueAll',
            'revenueByCategory'
        ));
    }

    /**
     * Báo cáo nguyên liệu: tồn kho, nhập, xuất, cảnh báo sắp hết.
     */
    public function ingredients(Request $request)
    {
        $branches = Branch::orderBy('name')->get();

        $branchId = $request->get('branch_id');
        $fromDate = $request->get('from_date') ?: now()->subDays(29)->toDateString();
        $toDate   = $request->get('to_date')   ?: now()->toDateString();

        // 1. Bảng tồn kho hiện tại
        $stockQuery = BranchIngredient::with(['ingredient', 'branch']);
        if ($branchId) {
            $stockQuery->where('id_br', $branchId);
        }

        $stocks = $stockQuery->get()->map(function ($item) {
            $ingre = $item->ingredient;
            $minStock = (float) ($ingre->min_stock ?? 0);
            $qty      = (float) $item->quantity;
            $value    = $qty * 0; // giá trị tồn nếu cần tính sau
            return [
                'id_br'        => $item->id_br,
                'id_ingre'     => $item->id_ingre,
                'branch_name'  => $item->branch->name ?? '—',
                'name'         => $ingre->name ?? '—',
                'unit'         => $ingre->unit ?? '',
                'quantity'     => $qty,
                'min_stock'    => $minStock,
                'is_low'       => $minStock > 0 && $qty <= $minStock,
                'is_out'       => $qty <= 0,
                'status'       => $ingre->status ?? 1,
                'value'        => $value,
            ];
        });

        $totalStockQty    = (float) $stocks->sum('quantity');
        $lowStockCount    = $stocks->where('is_low', true)->count();
        $outOfStockCount  = $stocks->where('is_out', true)->count();
        $totalIngredients = $stocks->pluck('id_ingre')->unique()->count();

        // 2. Nhập kho trong khoảng (chỉ tính phiếu đã duyệt status = 1)
        $importsQuery = ImportDetail::query()
            ->join('import_receipts', 'import_receipts.id_im', '=', 'import_detail.id_im')
            ->join('ingredients', 'ingredients.id_ingre', '=', 'import_detail.id_ingre')
            ->where('import_receipts.status', 1)
            ->whereBetween('import_receipts.created_at', [
                Carbon::parse($fromDate)->startOfDay(),
                Carbon::parse($toDate)->endOfDay(),
            ])
            ->when($branchId, fn ($q) => $q->where('import_receipts.id_br', $branchId))
            ->select(
                'import_detail.id_ingre',
                'ingredients.name as ingredient_name',
                'ingredients.unit',
                DB::raw('SUM(import_detail.quantity) as import_qty'),
                DB::raw('SUM(import_detail.quantity * import_detail.price) as import_value')
            )
            ->groupBy('import_detail.id_ingre', 'ingredients.name', 'ingredients.unit');

        $imports = $importsQuery->get()->keyBy('id_ingre');

        // 3. Xuất kho trong khoảng (chỉ tính phiếu đã duyệt status = 1, lý do sale - tự động từ bếp)
        $exportsQuery = ExportDetail::query()
            ->join('export_receipts', 'export_receipts.id_ex', '=', 'export_detail.id_ex')
            ->join('ingredients', 'ingredients.id_ingre', '=', 'export_detail.id_ingre')
            ->where('export_receipts.status', 1)
            ->where('export_receipts.reason', 'sale')
            ->whereBetween('export_receipts.created_at', [
                Carbon::parse($fromDate)->startOfDay(),
                Carbon::parse($toDate)->endOfDay(),
            ])
            ->when($branchId, fn ($q) => $q->where('export_receipts.id_br', $branchId))
            ->select(
                'export_detail.id_ingre',
                DB::raw('SUM(export_detail.quantity) as export_qty')
            )
            ->groupBy('export_detail.id_ingre');

        $exports = $exportsQuery->get()->keyBy('id_ingre');

        // 4. Xuất khác (waste, broken, adjustment) trong khoảng
        $wasteQuery = ExportDetail::query()
            ->join('export_receipts', 'export_receipts.id_ex', '=', 'export_detail.id_ex')
            ->where('export_receipts.status', 1)
            ->whereIn('export_receipts.reason', ['waste', 'broken', 'adjustment'])
            ->whereBetween('export_receipts.created_at', [
                Carbon::parse($fromDate)->startOfDay(),
                Carbon::parse($toDate)->endOfDay(),
            ])
            ->when($branchId, fn ($q) => $q->where('export_receipts.id_br', $branchId))
            ->select(
                'export_detail.id_ingre',
                DB::raw('SUM(export_detail.quantity) as waste_qty')
            )
            ->groupBy('export_detail.id_ingre');

        $wastes = $wasteQuery->get()->keyBy('id_ingre');

        // 5. Hợp nhất theo nguyên liệu
        $ingredientIds = $stocks->pluck('id_ingre')
            ->merge($imports->keys())
            ->merge($exports->keys())
            ->unique();

        $ingredientReport = $ingredientIds->map(function ($id) use ($stocks, $imports, $exports, $wastes) {
            $stockRow = $stocks->firstWhere('id_ingre', $id);
            $name  = $stockRow['name']        ?? optional($imports[$id])->ingredient_name ?? '—';
            $unit  = $stockRow['unit']        ?? optional($imports[$id])->unit ?? '';
            $branchesList = $stockRow
                ? collect([$stockRow['branch_name']])
                : collect();

            return [
                'id_ingre'     => $id,
                'name'         => $name,
                'unit'         => $unit,
                'stock_qty'    => (float) ($stockRow['quantity'] ?? 0),
                'min_stock'    => (float) ($stockRow['min_stock'] ?? 0),
                'import_qty'   => (float) (optional($imports[$id])->import_qty   ?? 0),
                'import_value' => (float) (optional($imports[$id])->import_value ?? 0),
                'export_qty'   => (float) (optional($exports[$id])->export_qty   ?? 0),
                'waste_qty'    => (float) (optional($wastes[$id])->waste_qty     ?? 0),
                'is_low'       => (bool)  ($stockRow['is_low'] ?? false),
                'is_out'       => (bool)  ($stockRow['is_out'] ?? false),
            ];
        })->sortByDesc('export_qty')->values();

        // 6. KPI
        $totalImportQty   = (float) $ingredientReport->sum('import_qty');
        $totalImportValue = (float) $ingredientReport->sum('import_value');
        $totalExportQty   = (float) $ingredientReport->sum('export_qty');
        $totalWasteQty    = (float) $ingredientReport->sum('waste_qty');

        // 7. Biểu đồ nhập/xuất theo ngày
        $importChart = $this->buildDailyChart(
            ImportReceipts::query()
                ->whereBetween('created_at', [
                    Carbon::parse($fromDate)->startOfDay(),
                    Carbon::parse($toDate)->endOfDay(),
                ])
                ->when($branchId, fn ($q) => $q->where('id_br', $branchId))
                ->where('status', 1)
                ->selectRaw('DATE(created_at) as d, COUNT(*) as c')
                ->groupBy('d')
                ->pluck('c', 'd')
                ->toArray(),
            $fromDate,
            $toDate
        );

        $exportChart = $this->buildDailyChart(
            ExportReceipts::query()
                ->whereBetween('created_at', [
                    Carbon::parse($fromDate)->startOfDay(),
                    Carbon::parse($toDate)->endOfDay(),
                ])
                ->when($branchId, fn ($q) => $q->where('id_br', $branchId))
                ->where('status', 1)
                ->selectRaw('DATE(created_at) as d, COUNT(*) as c')
                ->groupBy('d')
                ->pluck('c', 'd')
                ->toArray(),
            $fromDate,
            $toDate
        );

        return view('admin.pages.report_ingredients', compact(
            'branches',
            'branchId',
            'fromDate',
            'toDate',
            'totalStockQty',
            'lowStockCount',
            'outOfStockCount',
            'totalIngredients',
            'totalImportQty',
            'totalImportValue',
            'totalExportQty',
            'totalWasteQty',
            'ingredientReport',
            'importChart',
            'exportChart'
        ));
    }

    /**
     * Trang /admin/reports: gộp 3 báo cáo con (doanh thu, món bán chạy, nguyên liệu)
     * để admin xem tổng quan một lượt. Dùng chung bộ lọc chi nhánh + từ ngày / đến ngày.
     */
    public function index(Request $request)
    {
        $branches = Branch::orderBy('name')->get();
        $categories = Category::orderBy('name')->get();

        $branchId = $request->get('branch_id');
        $fromDate = $request->get('from_date') ?: now()->subDays(13)->toDateString();
        $toDate   = $request->get('to_date')   ?: now()->toDateString();

        $from = Carbon::parse($fromDate)->startOfDay();
        $to   = Carbon::parse($toDate)->endOfDay();

        // ===== 1. DOANH THU =====
        $billsQuery = Bill::query()
            ->where('status', 2)
            ->whereBetween('pay_time', [$from, $to])
            ->whereHas('order', function ($q) use ($branchId) {
                if ($branchId) {
                    $q->where('id_br', $branchId);
                }
            });

        $bills = (clone $billsQuery)->get();
        $totalRevenue = (float) $bills->sum('total');
        $totalBills   = $bills->count();
        $avgBill      = $totalBills > 0 ? $totalRevenue / $totalBills : 0;

        $groupedByDate = $bills->groupBy(fn ($bill) => optional(Carbon::parse($bill->pay_time)
        )->format('Y-m-d'));
        $revenueLabels = [];
        $revenueValues = [];
        $revenueCounts = [];
        $cursor = Carbon::parse($fromDate);
        $end    = Carbon::parse($toDate);
        while ($cursor->lte($end)) {
            $key = $cursor->format('Y-m-d');
            $bucket = $groupedByDate->get($key);
            $revenueLabels[] = $cursor->format('d/m');
            $revenueValues[] = (float) ($bucket ? $bucket->sum('total') : 0);
            $revenueCounts[] = (int) ($bucket ? $bucket->count() : 0);
            $cursor->addDay();
        }


        // ===== 2. MÓN ĂN BÁN CHẠY (Top 10) =====
        $menuRows = OrderDetail::query()
            ->select(
                'order_detail.id_food',
                DB::raw('SUM(order_detail.quantity) as total_qty'),
                DB::raw('SUM(order_detail.quantity * order_detail.price) as total_revenue'),
                DB::raw('COUNT(DISTINCT order_detail.id_order) as order_count')
            )
            ->join('orders', 'orders.id_order', '=', 'order_detail.id_order')
            ->join('bill', 'bill.id_order', '=', 'orders.id_order')
            ->where('orders.status', 3)
            ->where('bill.status', 2)
            ->whereBetween('orders.order_time', [$from, $to])
            ->when($branchId, fn ($q) => $q->where('orders.id_br', $branchId))
            ->groupBy('order_detail.id_food')
            ->get()
            ->map(function ($row) {
                $item = MenuItem::with('category')->find($row->id_food);
                return [
                    'id_food'       => (int) $row->id_food,
                    'name'          => $item->name ?? '—',
                    'image'         => $item->image ?? null,
                    'category'      => optional($item->category)->name ?? '—',
                    'price'         => (float) ($item->price ?? 0),
                    'total_qty'     => (int)    $row->total_qty,
                    'total_revenue' => (float)  $row->total_revenue,
                    'order_count'   => (int)    $row->order_count,
                ];
            })
            ->sortByDesc('total_revenue')
            ->values();

        $topBestSellers   = $menuRows->take(10)->values();
        $totalQtyAll      = (int)   $menuRows->sum('total_qty');
        $totalRevenueAll  = (float) $menuRows->sum('total_revenue');

        // ===== 3. NGUYÊN LIỆU =====
        // 3a. Tồn kho hiện tại
        $stockQuery = BranchIngredient::with(['ingredient', 'branch']);
        if ($branchId) {
            $stockQuery->where('id_br', $branchId);
        }
        $stocks = $stockQuery->get()->map(function ($item) {
            $ingre = $item->ingredient;
            $minStock = (float) ($ingre->min_stock ?? 0);
            $qty      = (float) $item->quantity;
            return [
                'id_br'       => $item->id_br,
                'id_ingre'    => $item->id_ingre,
                'branch_name' => $item->branch->name ?? '—',
                'name'        => $ingre->name ?? '—',
                'unit'        => $ingre->unit ?? '',
                'quantity'    => $qty,
                'min_stock'   => $minStock,
                'is_low'      => $minStock > 0 && $qty <= $minStock,
                'is_out'      => $qty <= 0,
                'status'      => $ingre->status ?? 1,
            ];
        });
        $totalStockQty    = (float) $stocks->sum('quantity');
        $lowStockCount    = $stocks->where('is_low', true)->count();
        $outOfStockCount  = $stocks->where('is_out', true)->count();
        $totalIngredients = $stocks->pluck('id_ingre')->unique()->count();

        // 3b. Nhập kho trong khoảng
        $imports = ImportDetail::query()
            ->join('import_receipts', 'import_receipts.id_im', '=', 'import_detail.id_im')
            ->where('import_receipts.status', 1)
            ->whereBetween('import_receipts.created_at', [$from, $to])
            ->when($branchId, fn ($q) => $q->where('import_receipts.id_br', $branchId))
            ->select(
                'import_detail.id_ingre',
                DB::raw('SUM(import_detail.quantity) as import_qty'),
                DB::raw('SUM(import_detail.quantity * import_detail.price) as import_value')
            )
            ->groupBy('import_detail.id_ingre')
            ->get()
            ->keyBy('id_ingre');

        // 3c. Xuất kho (sale) trong khoảng
        $exports = ExportDetail::query()
            ->join('export_receipts', 'export_receipts.id_ex', '=', 'export_detail.id_ex')
            ->where('export_receipts.status', 1)
            ->where('export_receipts.reason', 'sale')
            ->whereBetween('export_receipts.created_at', [$from, $to])
            ->when($branchId, fn ($q) => $q->where('export_receipts.id_br', $branchId))
            ->select(
                'export_detail.id_ingre',
                DB::raw('SUM(export_detail.quantity) as export_qty')
            )
            ->groupBy('export_detail.id_ingre')
            ->get()
            ->keyBy('id_ingre');

        // 3d. Xuất khác (waste/broken/adjustment)
        $wastes = ExportDetail::query()
            ->join('export_receipts', 'export_receipts.id_ex', '=', 'export_detail.id_ex')
            ->where('export_receipts.status', 1)
            ->whereIn('export_receipts.reason', ['waste', 'broken', 'adjustment'])
            ->whereBetween('export_receipts.created_at', [$from, $to])
            ->when($branchId, fn ($q) => $q->where('export_receipts.id_br', $branchId))
            ->select(
                'export_detail.id_ingre',
                DB::raw('SUM(export_detail.quantity) as waste_qty')
            )
            ->groupBy('export_detail.id_ingre')
            ->get()
            ->keyBy('id_ingre');

        // 3e. Hợp nhất — ép tất cả id về int để khớp key của các Collection đã keyBy
        $ingredientIds = $stocks->pluck('id_ingre')
            ->merge($imports->keys())
            ->merge($exports->keys())
            ->merge($wastes->keys())
            ->map(fn ($v) => (int) $v)
            ->unique();

        $ingredientReport = $ingredientIds->map(function ($id) use ($stocks, $imports, $exports, $wastes) {
            $id = (int) $id;
            $stockRow = $stocks->firstWhere('id_ingre', $id);
            $importRow = $imports->firstWhere('id_ingre', $id);
            $exportRow = $exports->firstWhere('id_ingre', $id);
            $wasteRow  = $wastes->firstWhere('id_ingre', $id);

            $name  = $stockRow['name'] ?? $importRow->ingredient_name ?? '—';
            $unit  = $stockRow['unit'] ?? $importRow->unit ?? '';
            return [
                'id_ingre'     => $id,
                'name'         => $name,
                'unit'         => $unit,
                'branch_name'  => $stockRow['branch_name'] ?? '—',
                'stock_qty'    => (float) ($stockRow['quantity'] ?? 0),
                'min_stock'    => (float) ($stockRow['min_stock'] ?? 0),
                'import_qty'   => (float) ($importRow->import_qty   ?? 0),
                'import_value' => (float) ($importRow->import_value ?? 0),
                'export_qty'   => (float) ($exportRow->export_qty   ?? 0),
                'waste_qty'    => (float) ($wasteRow->waste_qty     ?? 0),
                'is_low'       => (bool)  ($stockRow['is_low'] ?? false),
                'is_out'       => (bool)  ($stockRow['is_out'] ?? false),
            ];
        })->sortByDesc('export_qty')->values();

        $totalImportQty   = (float) $ingredientReport->sum('import_qty');
        $totalImportValue = (float) $ingredientReport->sum('import_value');
        $totalExportQty   = (float) $ingredientReport->sum('export_qty');
        $totalWasteQty    = (float) $ingredientReport->sum('waste_qty');

        return view('admin.pages.reports_index', compact(
            'branches',
            'categories',
            'branchId',
            'fromDate',
            'toDate',
            // revenue
            'totalRevenue',
            'totalBills',
            'avgBill',
            'revenueLabels',
            'revenueValues',
            'revenueCounts',
            // best selling
            'topBestSellers',
            'totalQtyAll',
            'totalRevenueAll',
            // ingredients
            'totalStockQty',
            'lowStockCount',
            'outOfStockCount',
            'totalIngredients',
            'totalImportQty',
            'totalImportValue',
            'totalExportQty',
            'totalWasteQty',
            'ingredientReport'
        ));
    }

    /**
     * Báo cáo tổng hợp: doanh thu + top 5 món bán chạy + chi tiết doanh thu theo món.
     * Trang gộp duy nhất cho admin, lọc theo chi nhánh + từ ngày / đến ngày.
     */
    public function combined(Request $request)
    {
        $branches = Branch::orderBy('name')->get();

        $branchId = $request->get('branch_id');
        $fromDate = $request->get('from_date') ?: now()->subDays(13)->toDateString();
        $toDate   = $request->get('to_date')   ?: now()->toDateString();

        $from = Carbon::parse($fromDate)->startOfDay();
        $to   = Carbon::parse($toDate)->endOfDay();

        // 1. Tổng quan doanh thu (giống revenue() nhưng lọc theo order_time cho khớp với món)
        $billsQuery = Bill::query()
            ->where('status', 2)
            ->whereBetween('pay_time', [$from, $to])
            ->whereHas('order', function ($q) use ($branchId) {
                if ($branchId) {
                    $q->where('id_br', $branchId);
                }
            });

        $bills = (clone $billsQuery)->get();

        $totalRevenue = (float) $bills->sum('total');
        $totalBills   = $bills->count();
        $avgBill      = $totalBills > 0 ? $totalRevenue / $totalBills : 0;

        // Biểu đồ doanh thu theo ngày
        $groupedByDate = $bills->groupBy(fn ($bill) => optional($bill->pay_time)->format('Y-m-d'));

        $labels = [];
        $values = [];
        $counts = [];

        $cursor = Carbon::parse($fromDate);
        $end    = Carbon::parse($toDate);
        while ($cursor->lte($end)) {
            $key = $cursor->format('Y-m-d');
            $bucket = $groupedByDate->get($key);
            $labels[] = $cursor->format('d/m');
            $values[] = (float) ($bucket ? $bucket->sum('total') : 0);
            $counts[] = (int) ($bucket ? $bucket->count() : 0);
            $cursor->addDay();
        }

        // 2. Doanh thu & số lượng theo món (đã hoàn thành, bill đã thanh toán)
        $detailsQuery = OrderDetail::query()
            ->select(
                'order_detail.id_food',
                DB::raw('SUM(order_detail.quantity) as total_qty'),
                DB::raw('SUM(order_detail.quantity * order_detail.price) as total_revenue'),
                DB::raw('COUNT(DISTINCT order_detail.id_order) as order_count')
            )
            ->join('orders', 'orders.id_order', '=', 'order_detail.id_order')
            ->join('bill', 'bill.id_order', '=', 'orders.id_order')
            ->where('orders.status', 3)
            ->where('bill.status', 2)
            ->whereBetween('orders.order_time', [$from, $to])
            ->when($branchId, fn ($q) => $q->where('orders.id_br', $branchId))
            ->groupBy('order_detail.id_food');

        $menuRows = $detailsQuery->get()->map(function ($row) {
            $item = MenuItem::with('category')->find($row->id_food);
            return [
                'id_food'       => (int) $row->id_food,
                'name'          => $item->name ?? '—',
                'image'         => $item->image ?? null,
                'category'      => optional($item->category)->name ?? '—',
                'price'         => (float) ($item->price ?? 0),
                'total_qty'     => (int)    $row->total_qty,
                'total_revenue' => (float)  $row->total_revenue,
                'order_count'   => (int)    $row->order_count,
            ];
        });

        // Sắp xếp theo doanh thu giảm dần
        $menuRowsSorted = $menuRows->sortByDesc('total_revenue')->values();

        // Top 5 món bán chạy (theo doanh thu) cho bảng xếp hạng
        $topItems = $menuRowsSorted->take(5)->values();

        // Bảng chi tiết doanh thu theo món — lấy tất cả, kèm phân trang nhẹ
        $perPage = 15;
        $page    = max(1, (int) $request->get('page', 1));
        $menuRowsPage = $menuRowsSorted->forPage($page, $perPage);
        $lastPage = max(1, (int) ceil($menuRowsSorted->count() / $perPage));

        $totalQtyAll     = (int)   $menuRowsSorted->sum('total_qty');
        $totalRevenueAll = (float) $menuRowsSorted->sum('total_revenue');

        return view('admin.pages.report_combined', compact(
            'branches',
            'branchId',
            'fromDate',
            'toDate',
            'totalRevenue',
            'totalBills',
            'avgBill',
            'totalQtyAll',
            'totalRevenueAll',
            'labels',
            'values',
            'counts',
            'topItems',
            'menuRowsPage',
            'menuRowsSorted',
            'page',
            'lastPage'
        ));
    }

    /**
     * Trả về [labels[], values[]] lấp đầy khoảng ngày $from → $to.
     */
    private function buildDailyChart(array $data, string $from, string $to): array
    {
        $labels = [];
        $values = [];
        $start  = Carbon::parse($from);
        $end    = Carbon::parse($to);

        while ($start->lte($end)) {
            $key = $start->toDateString();
            $labels[] = $start->format('d/m');
            $values[] = (int) ($data[$key] ?? 0);
            $start->addDay();
        }

        return ['labels' => $labels, 'values' => $values];
    }
}
