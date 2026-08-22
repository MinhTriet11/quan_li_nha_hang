<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bill;
use App\Models\BranchIngredient;
use App\Models\ImportDetail;
use App\Models\ImportReceipts;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // ===== 1. Tổng doanh thu =====
        // Tính từ các bill đã thanh toán (status = 2) - lấy trên tất cả chi nhánh.
        $totalRevenue = (float) Bill::query()
            ->where('status', 2)
            ->sum('total');

        // ===== 2. Tổng vốn nhập =====
        // Tổng giá trị các phiếu nhập đã duyệt (status = 1).
        $totalImportValue = (float) ImportDetail::query()
            ->join('import_receipts', 'import_receipts.id_im', '=', 'import_detail.id_im')
            ->where('import_receipts.status', 1)
            ->select(DB::raw('SUM(import_detail.quantity * import_detail.price) as total_value'))
            ->value('total_value');

        // ===== 3. Số phiếu chưa duyệt (nhập + xuất + chuyển) =====
        $pendingImportCount  = ImportReceipts::where('status', 0)->count();
        $pendingExportCount  = \App\Models\ExportReceipts::where('status', 0)->count();
        $pendingTransferCount = \App\Models\Transfer::where('status', 0)->count();
        $pendingReceiptsCount = $pendingImportCount + $pendingExportCount + $pendingTransferCount;

        // ===== 4. Nguyên liệu cảnh báo (sắp hết / hết) =====
        $lowStockRows = BranchIngredient::with('ingredient')->get()->map(function ($row) {
            $min = (float) ($row->ingredient->min_stock ?? 0);
            $qty = (float) $row->quantity;
            $row->is_low = $min > 0 && $qty <= $min;
            $row->is_out = $qty <= 0;
            return $row;
        });
        $lowStockCount = $lowStockRows->where('is_low', true)->count();

        // ===== 5. Doanh thu theo tuần (7 ngày gần nhất) =====
        $today    = Carbon::today();
        $weekFrom = (clone $today)->subDays(6); // bao gồm hôm nay

        $weekBills = Bill::query()
            ->where('status', 2)
            ->whereBetween('pay_time', [$weekFrom->startOfDay(), $today->endOfDay()])
            ->get(['pay_time', 'total']);


        $weeklyLabels = [];
        $weeklyValues = [];
        for ($i = 6; $i >= 0; $i--) {
            $day = (clone $today)->subDays($i);
            $key = $day->format('Y-m-d');
            $weeklyLabels[] =  $day->format('d/m'); 
            $weeklyValues[] = (float) $weekBills
                ->filter(fn ($b) => optional(Carbon::parse($b->pay_time))->format('Y-m-d') === $key)
                ->sum('total');
        }

        // ===== 6. Doanh thu từng chi nhánh (toàn bộ thời gian, bill đã thanh toán) =====
        $revenueByBranch = Bill::query()
            ->join('orders', 'orders.id_order', '=', 'bill.id_order')
            ->join('branches', 'branches.id_br', '=', 'orders.id_br')
            ->where('bill.status', 2)
            ->groupBy('orders.id_br', 'branches.name')
            ->select(
                'orders.id_br',
                'branches.name as branch_name',
                DB::raw('SUM(bill.total) as revenue')
            )
            ->orderByDesc('revenue')
            ->get();

        $branchLabels = $revenueByBranch->pluck('branch_name')->all();
        $branchValues = $revenueByBranch->pluck('revenue')->map(fn ($v) => (float) $v)->all();

        return view('admin.pages.dashboard', compact(
            'totalRevenue',
            'totalImportValue',
            'pendingReceiptsCount',
            'pendingImportCount',
            'pendingExportCount',
            'pendingTransferCount',
            'lowStockCount',
            'weeklyLabels',
            'weeklyValues',
            'branchLabels',
            'branchValues'
        ));
    }
}
