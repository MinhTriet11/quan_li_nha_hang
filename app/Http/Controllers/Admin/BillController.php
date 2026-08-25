<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bill;
use App\Models\Branch;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class BillController extends Controller
{
    /**
     * Danh sách hóa đơn (cả đã thanh toán và chưa thanh toán).
     * Lọc theo chi nhánh, phương thức thanh toán, ngày, trạng thái.
     */
    public function index(Request $request)
    {
        $query = Bill::with(['order.table', 'order.branch', 'order.user']);

        if ($request->filled('branch')) {
            $query->whereHas('order', fn ($q) => $q->where('id_br', $request->branch));
        }
        if ($request->filled('pay_method')) {
            $query->where('pay_method', $request->pay_method);
        }
        if ($request->filled('from')) {
            $query->whereDate('pay_time', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('pay_time', '<=', $request->to);
        }
        if ($request->filled('search')) {
            $kw = $request->search;
            $query->where('id_bill', $kw);
        }

        // Lọc theo trạng thái:
        // 1 = chưa thanh toán, 2 = đã thanh toán
        // Mặc định: bỏ qua nếu status = 'all' hoặc rỗng
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', (int) $request->status);
        }

        $bills = $query->orderBy('pay_time', 'desc')->paginate(10)->withQueryString();
        $branches = Branch::orderBy('name')->get();

        return view('admin.pages.bills', compact('bills', 'branches'));
    }

    /**
     * Chi tiết hóa đơn: in theo kiểu bill nhà hàng.
     * Hỗ trợ 2 chế độ:
     *   - Có id_bill  -> hóa đơn chính thức đã thanh toán.
     *   - Có orderId -> hóa đơn tạm tính (chưa thanh toán), lấy OrderDetail.
     */
    public function show(Request $request, $id = null)
    {
        // Hóa đơn tạm tính theo order (chưa thanh toán)
        if ($request->filled('orderId')) {
            $order = Order::with([
                'table.TableTypes',
                'branch',
                'user',
                'orderDetails.menuItem',
            ])->findOrFail($request->orderId);

            $grouped = $order->orderDetails
                ->groupBy(fn ($d) => $d->id_food . '_' . $d->status)
                ->map(function ($row) {
                    $first = $row->first();
                    $first->quantity = $row->sum('quantity');
                    return $first;
                })
                ->values();

            $subtotal = $grouped->sum(fn ($d) => ((int) $d->quantity) * ((float) $d->price));
            $tax      = round($subtotal * 0.08, 2);
            $total    = $subtotal + $tax;

            $bill = null;
            return view('admin.pages.bill_show', compact('order', 'grouped', 'subtotal', 'tax', 'total', 'bill'));
        }

        // Hóa đơn chính thức (đã thanh toán)
        $bill = Bill::with(['order.table', 'order.branch', 'order.user', 'order.orderDetails.menuItem'])
            ->findOrFail($id);

        $order = $bill->order;

        $grouped = $order->orderDetails
            ->groupBy(fn ($d) => $d->id_food . '_' . $d->status)
            ->map(function ($row) {
                $first = $row->first();
                $first->quantity = $row->sum('quantity');
                return $first;
            })
            ->values();

        $subtotal = $grouped->sum(fn ($d) => ((int) $d->quantity) * ((float) $d->price)) ;
        $discount_percent = $bill->discount_percent;
        $tax      = round($subtotal * 0.08, 2);
        $total    = $subtotal - $bill->discount_amount;

        return view('admin.pages.bill_show', compact('bill', 'order', 'grouped', 'subtotal', 'tax', 'total', 'discount_percent'));
    }

    /**
     * Trang in hóa đơn dạng máy in bill (khổ 80mm).
     * Hỗ trợ:
     *   - ?orderId=X  : in tạm tính theo đơn
     *   - /{id}       : in bill chính thức
     */
    public function print(Request $request, $id = null)
    {
        if ($request->filled('orderId')) {
            $order = Order::with([
                'table.TableTypes',
                'branch',
                'user',
                'orderDetails.menuItem',
            ])->findOrFail($request->orderId);

            $grouped = $order->orderDetails
                ->groupBy(fn ($d) => $d->id_food . '_' . $d->status)
                ->map(function ($row) {
                    $first = $row->first();
                    $first->quantity = $row->sum('quantity');
                    return $first;
                })
                ->values();

            $subtotal = $grouped->sum(fn ($d) => ((int) $d->quantity) * ((float) $d->price));
            $tax      = round($subtotal * 0.08, 2);
            $total    = $subtotal + $tax;
            $bill     = null;

            return view('admin.pages.bill_print', compact('order', 'grouped', 'subtotal', 'tax', 'total', 'bill'));
        }

        $bill = Bill::with(['order.table', 'order.branch', 'order.user', 'order.orderDetails.menuItem'])
            ->findOrFail($id);

        $order = $bill->order;

        $grouped = $order->orderDetails
            ->groupBy(fn ($d) => $d->id_food . '_' . $d->status)
            ->map(function ($row) {
                $first = $row->first();
                $first->quantity = $row->sum('quantity');
                return $first;
            })
            ->values();

        $subtotal = $grouped->sum(fn ($d) => ((int) $d->quantity) * ((float) $d->price));
        $tax      = round($subtotal * 0.08, 2);
        $total    = $subtotal + $tax;

        return view('admin.pages.bill_print', compact('bill', 'order', 'grouped', 'subtotal', 'tax', 'total'));
    }
}