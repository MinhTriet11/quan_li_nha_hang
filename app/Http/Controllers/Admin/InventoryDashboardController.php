<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\BranchIngredient;
use App\Models\ExportReceipts;
use App\Models\ImportReceipts;
use Illuminate\Http\Request;

class InventoryDashboardController extends Controller
{
    /**
     * Trang tổng quan tồn kho (Dashboard/KPI tồn kho).
     */
    public function index(Request $request)
    {
        $branches = Branch::orderBy('name')->get();
        $selectedBranchId = $request->get('branch_id');

        // Khoảng thời gian mặc định: 7 ngày gần nhất
        $fromDate = $request->get('from_date') ?: now()->subDays(6)->toDateString();
        $toDate = $request->get('to_date') ?: now()->toDateString();

        // 1. Bảng tồn kho từng nguyên liệu theo chi nhánh
        $stockQuery = BranchIngredient::with(['ingredient', 'branch']);

        if ($selectedBranchId) {
            $stockQuery->where('id_br', $selectedBranchId);
        }

        $stocks = $stockQuery->get()->map(function ($item) {
            $ingre = $item->ingredient;
            $minStock = $ingre->min_stock ?? 0;
            $qty = (float) $item->quantity;

            return [
                'id_br'        => $item->id_br,
                'branch_name'  => $item->branch->name ?? '—',
                'id_ingre'     => $item->id_ingre,
                'name'         => $ingre->name ?? '—',
                'unit'         => $ingre->unit ?? '',
                'quantity'     => $qty,
                'min_stock'    => $minStock,
                'ratio'        => $minStock > 0 ? round($qty / $minStock, 2) : null,
                'is_low'       => $minStock > 0 && $qty <= $minStock,
                'is_out'       => $qty <= 0,
                'status'       => $ingre->status ?? 1,
            ];
        });

        // 2. KPI tổng
        $totalIngredients = $stocks->pluck('id_ingre')->unique()->count();
        $totalStockQty = $stocks->sum('quantity');
        $lowStockCount = $stocks->where('is_low', true)->count();
        $outOfStockCount = $stocks->where('is_out', true)->count();
        $branchCount = $branches->count();

        // 3. Phiếu nhập gần đây (lọc theo ngày)
        $recentImports = ImportReceipts::with(['branch', 'user'])
            ->whereBetween('created_at', [$fromDate . ' 00:00:00', $toDate . ' 23:59:59'])
            ->when($selectedBranchId, fn ($q) => $q->where('id_br', $selectedBranchId))
            ->orderBy('id_im', 'desc')
            ->limit(5)
            ->get();

        // 5. Phiếu xuất gần đây
        $recentExports = ExportReceipts::with(['branch', 'user'])
            ->whereBetween('create_at', [$fromDate . ' 00:00:00', $toDate . ' 23:59:59'])
            ->when($selectedBranchId, fn ($q) => $q->where('id_br', $selectedBranchId))
            ->orderBy('id_ex', 'desc')
            ->limit(5)
            ->get();

        // 6. Tổng nhập / xuất trong khoảng (tính theo phiếu đã duyệt)
        $importTotal = (clone $recentImports)->where('status', 1)->count();
        $importPending = (clone $recentImports)->where('status', 0)->count();
        $exportTotal = (clone $recentExports)->where('status', 1)->count();
        $exportPending = (clone $recentExports)->where('status', 0)->count();

        // 7. Tồn kho theo chi nhánh (cho biểu đồ cột ngang)
        $stockByBranch = $stocks
            ->groupBy('id_br')
            ->map(function ($items, $branchId) use ($branches) {
                $branch = $branches->firstWhere('id_br', (int) $branchId);
                return [
                    'branch_id'   => (int) $branchId,
                    'branch_name' => $branch->name ?? '—',
                    'total_qty'   => $items->sum('quantity'),
                    'low_count'   => $items->where('is_low', true)->count(),
                ];
            })
            ->values();

        // 8. Chuẩn bị dữ liệu cho biểu đồ nhập/xuất theo ngày
        $importChart = $this->buildDailyChart(
            ImportReceipts::query()
                ->whereBetween('created_at', [$fromDate . ' 00:00:00', $toDate . ' 23:59:59'])
                ->when($selectedBranchId, fn ($q) => $q->where('id_br', $selectedBranchId))
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
                ->whereBetween('create_at', [$fromDate . ' 00:00:00', $toDate . ' 23:59:59'])
                ->when($selectedBranchId, fn ($q) => $q->where('id_br', $selectedBranchId))
                ->where('status', 1)
                ->selectRaw('DATE(create_at) as d, COUNT(*) as c')
                ->groupBy('d')
                ->pluck('c', 'd')
                ->toArray(),
            $fromDate,
            $toDate
        );

        return view('admin.pages.inventory_dashboard', compact(
            'branches',
            'selectedBranchId',
            'totalIngredients',
            'totalStockQty',
            'lowStockCount',
            'outOfStockCount',
            'branchCount',
            'recentImports',
            'recentExports',
            'fromDate',
            'toDate',
            'importTotal',
            'importPending',
            'exportTotal',
            'exportPending',
            'stockByBranch',
            'importChart',
            'exportChart'
        ));
    }

    //Tồn kho
    public function stock(Request $request)
    {
        $branches = Branch::orderBy('name')->get();
        $selectedBranchId = $request->get('branch_id');
        $search = $request->get('search');
        $status = $request->get('status');

        $stockQuery = BranchIngredient::with(['ingredient', 'branch']);

        // Lọc theo chi nhánh
        if ($selectedBranchId) {
            $stockQuery->where('id_br', $selectedBranchId);
        }

        // Lọc theo ô tìm kiếm (tên nguyên liệu)
        if ($search) {
            $kw = mb_strtolower($search);
                $stockQuery->whereHas('ingredient', function ($q) use ($kw) {
                    $q->whereRaw('LOWER(name) LIKE ?', ["%{$kw}%"]);
                });
        }

        // Lọc theo trạng thái tồn kho
        if ($status === 'out') {
            $stockQuery->where('quantity', '<=', 0);
        } elseif ($status === 'low') {
            $stockQuery->where('quantity', '>', 0)
                ->whereHas('ingredient', function ($q) {
                    $q->whereColumn('branch_ingredient.quantity', '<=', 'ingredients.min_stock');
                });
        } elseif ($status === 'ok') {

            $stockQuery->whereHas('ingredient', function($q){
                $q->whereColumn('branch_ingredient.quantity', '>', 'ingredients.min_stock');
            });
            
        }


        $stockQuery->select('branch_ingredient.*')
            ->selectRaw('CASE
        WHEN branch_ingredient.quantity <= 0 THEN 0
        WHEN ingredients.min_stock > 0
             AND branch_ingredient.quantity <= ingredients.min_stock THEN 1
        ELSE 2
                END as stock_group')
            ->leftJoin('ingredients', 'ingredients.id_ingre', '=', 'branch_ingredient.id_ingre')
            ->orderByRaw('stock_group ASC')
            ->orderBy('branch_ingredient.quantity', 'asc')
            ->orderBy('ingredients.name', 'asc');

        $stocks = $stockQuery->paginate(10)->withQueryString();

        // Sort đơn giản: số lượng tăng dần (hết hàng → sắp hết → ổn định tự nhiên).
        // Tie-break theo tên nguyên liệu để thứ tự ổn định giữa các lần load.
        /** @var \Illuminate\Database\Eloquent\Collection $collection */
        $collection = $stocks->getCollection();
        $collection->transform(function ($item) {
            $item->is_low = $item->ingredient && $item->ingredient->min_stock > 0
                && $item->quantity <= $item->ingredient->min_stock;

            $item->is_out = $item->quantity <= 0;

            return $item;
        });


        // Tổng hợp cho tiêu đề (đếm trên toàn bộ data theo chi nhánh, không theo search/status)
        $baseQuery = BranchIngredient::with('ingredient');
        if ($selectedBranchId) {
            $baseQuery->where('id_br', $selectedBranchId);
        }
        $baseRows = $baseQuery->get();

        $totalItems = $baseRows->count();
        $lowStockCount = $baseRows->filter(fn ($r) => $r->ingredient && $r->ingredient->min_stock > 0
            && $r->quantity <= $r->ingredient->min_stock
            && $r->quantity > 0)->count();
        $outOfStockCount = $baseRows->where('quantity', '<=', 0)->count();

        return view('admin.pages.inventory_stock', compact(
            'branches',
            'selectedBranchId',
            'search',
            'status',
            'stocks',
            'totalItems',
            'lowStockCount',
            'outOfStockCount'
        ));
    }

    /**
     * Trả về mảng [labels[], values[]] lấp đầy khoảng ngày $from → $to.
     */
    private function buildDailyChart(array $data, string $from, string $to): array
    {
        $labels = [];
        $values = [];
        $start = \Carbon\Carbon::parse($from);
        $end = \Carbon\Carbon::parse($to);

        while ($start->lte($end)) {
            $key = $start->toDateString();
            $labels[] = $start->format('d/m');
            $values[] = (int) ($data[$key] ?? 0);
            $start->addDay();
        }

        return ['labels' => $labels, 'values' => $values];
    }
}
