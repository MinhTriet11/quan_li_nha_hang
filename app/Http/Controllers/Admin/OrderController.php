<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\BranchScope;
use App\Http\Controllers\Controller;
use App\Models\Bill;
use App\Models\Branch;
use App\Models\Category;
use App\Models\Customer;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Table;
use App\Models\TableTypes;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class OrderController extends Controller
{
    use BranchScope;

    public function __construct(private InventoryService $inventory)
    {
    }

    // Hiển thị giao diện chọn bàn theo chi nhánh
    public function index(Request $request)
    {
        $branches = $this->availableBranches();
        $selectedBranch = $this->currentBranchId($request);

        $tables = [];
        $categories = [];

        if ($selectedBranch) {
            // Lấy danh sách bàn theo chi nhánh
            $tables = Table::with(['branch', 'TableTypes', 'currentOrder'])
                ->where('id_br', $selectedBranch)
                ->where('status', '=', 0) // Không hiển thị bàn bảo trì
                ->orderBy('table_name')
                ->get();

            // Lấy danh sách danh mục món ăn
            $categories = Category::all();
        }

        $showBranchSelector = $this->showBranchSelector();
        return view('admin.pages.order_tables', compact('branches', 'tables', 'categories', 'selectedBranch', 'showBranchSelector'));
    }

    // Lấy món ăn theo danh mục
    public function getMenuItems(Request $request)
    {
        $categoryId = $request->category_id;
        $search = $request->search;

        $query = MenuItem::where('status', 0);

        if ($categoryId && $categoryId !== 'all') {
            $query->where('id_cate', $categoryId);
        }

        if ($search) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        $menuItems = $query->get();

        return response()->json([
            'data' => $menuItems
        ]);
    }

    // Tạo đơn order mới
    public function createOrder(Request $request)
    {
        $request->validate([
            'table_id' => 'required|exists:tables,id_table',
        ], [
            'table_id.required' => 'Vui lòng chọn bàn',
        ]);

        $tableId = $request->table_id;

        // Kiểm tra xem bàn đã có order đang hoạt động chưa
        $existingOrder = Order::where('id_table', $tableId)
            ->whereIn('status', [0, 1, 2])
            ->first();

        if ($existingOrder) {
            return response()->json([
                'error' => false,
                'message' => 'Bàn này đang phục vụ hoặc đang bảo trì',
            ]);
        }

        // Lấy thông tin bàn để biết chi nhánh
        $table = Table::findOrFail($tableId);

        // Tạo đơn hàng mới
        $order = Order::create([
            'id_br' => $table->id_br,
            'id_table' => $tableId,
            'id_user' => auth()->id(),
            'id_cus' => null,
            'order_time' => Carbon::now(),
            'status' => 0, // Đang chờ
            'note' => $request->note ?? ''
        ]);

        // Cập nhật trạng thái bàn thành đang phục vụ
        $table->status = 2;
        $table->save();

        // Lưu chi tiết đơn hàng
        $items = $request->items ?? [];
        foreach ($items as $item) {
            $menuItem = MenuItem::find($item['id']);
            if ($menuItem) {
                OrderDetail::create([
                    'id_order' => $order->id_order,
                    'id_food' => $item['id'],
                    'quantity' => $item['quantity'],
                    'price' => $menuItem->price,
                    'status' => 0
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Tạo đơn hàng thành công',
            'order_id' => $order->id_order
        ]);
    }

    // Xem chi tiết order của một bàn
    public function showOrder($tableId)
    {
        $order = Order::with(['table', 'branch', 'user', 'orderDetails.menuItem']) //Lấy orderDetails, rồi từ mỗi orderDetail lấy tiếp menuItem.
            ->where('id_table', $tableId)
            ->whereIn('status', [0, 1, 2])
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy đơn hàng'
            ]);
        }

        $orderDetails = $order->orderDetails
                ->groupBy(function ($item){
                    return $item->id_food .'_'. $item->status;
                })
                ->map(function($item){
                    $first = $item->first();
                    $first->quantity = $item->sum('quantity');
                    return $first;
                })
                ->values(); //tra ve key 0 1 2 cho de duyet mang

        $order->setRelation('orderDetails', $orderDetails);  //set lại relationship 

        return response()->json([
            'success' => true,
            'data' => $order
        ]);
    }

    // Cập nhật trạng thái order
    public function updateOrderStatus(Request $request, $orderId)
    {
        $order = Order::findOrFail($orderId);

        $newStatus = $request->status;

        // Validate status hợp lệ
        if (!in_array((int) $newStatus, [0, 1, 2, 3, 4], true)) {
            return response()->json([
                'success' => false,
                'message' => 'Trạng thái không hợp lệ.',
            ], 422);
        }

        // Lấy danh sách OrderDetail chưa ở trạng thái "đã phục vụ" (status=2) trước
        // để có thể kiểm tra tồn kho khi chuyển sang trạng thái 1 (chế biến).
        $details = OrderDetail::where('id_order', $orderId)
                    ->where('status', '!=', 2)
                    ->get();

        // Khi bếp bắt đầu chế biến (status 0 -> 1): kiểm tra tồn kho trước.
        if ((int) $newStatus === 1) {
            $check = $this->inventory->checkStockAvailability($order->id_br, $details);

            if (!$check['ok']) {
                return response()->json([
                    'success'   => false,
                    'message'   => 'Không đủ nguyên liệu để chế biến đơn này.',
                    'shortages' => $check['shortages'],
                ], 422);
            }
        }

        // Cập nhật trạng thái order (chỉ tới đây khi đã pass check tồn kho)
        $order->status = $newStatus;
        $order->save();

        $detailsToExport = collect();

        foreach ($details as $detail) {
            $detail->status = $newStatus;
            $detail->save();

            if ((int) $newStatus === 1) {
                $detailsToExport->push($detail);
            }
        }

        if ($detailsToExport->isNotEmpty()) {
            $this->inventory->exportForOrderDetail($detailsToExport, $order);
        }

        // Nếu hoàn thành hoặc hủy, cập nhật lại trạng thái bàn
        if (in_array($newStatus, [3, 4])) { // 3: Hoàn thành, 4: Hủy
            $table = Table::find($order->id_table);
            if ($table) {
                $table->status = 0; // Trả về trống
                $table->save();
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật trạng thái thành công'
        ]);
    }

    // Hủy order
    public function cancelOrder($orderId)
    {
        $order = Order::findOrFail($orderId);

        // Cập nhật trạng thái bàn về trống
        $table = Table::find($order->id_table);
        if ($table) {
            $table->status = 0;
            $table->save();
        }

        // Cập nhật trạng thái order
        $order->status = 4; // Hủy
        $order->save();

        return response()->json([
            'success' => true,
            'message' => 'Hủy đơn hàng thành công'
        ]);
    }

    // Hoàn thành order
    public function completeOrder($orderId)
    {
        $order = Order::findOrFail($orderId);

        // Cập nhật trạng thái bàn về trống
        $table = Table::find($order->id_table);
        if ($table) {
            $table->status = 0;
            $table->save();
        }

        // Cập nhật trạng thái order
        $order->status = 3; // Hoàn thành
        $order->save();

        return response()->json([
            'success' => true,
            'message' => 'Hoàn thành đơn hàng thành công'
        ]);
    }

    // Hiển thị giao diện quản lý đơn hàng
    public function orderManagement(Request $request)
    {
        $branches = $this->availableBranches();
        $categories = Category::all();
        $selectedBranch = $this->currentBranchId($request);

        $tables = [];
        $availableTables = [];

        if ($selectedBranch) {
            // Lấy tất cả bàn theo chi nhánh (trừ bàn bảo trì)
            $tables = Table::with(['branch', 'currentOrder.orderDetails', 'currentOrder.orderDetails.menuItem'])
                ->where('id_br', $selectedBranch)
                ->where('status', '!=', 1) // Không lấy bàn bảo trì
                ->orderBy('table_name')
                ->get();

            // Lấy các bàn trống (để chuyển/gộp)
            $availableTables = Table::where('id_br', $selectedBranch)
                ->where('status', 0)
                ->orderBy('table_name')
                ->get();
        }

        $showBranchSelector = $this->showBranchSelector();
        return view('admin.pages.order_management', compact('branches', 'categories', 'tables', 'availableTables', 'selectedBranch', 'showBranchSelector'));
    }

    // Lấy danh sách bàn trống theo chi nhánh (cho modal chuyển/gộp bàn)
    public function getAvailableTables(Request $request)
    {
        $branchId = $request->branch_id;
        $excludeId = $request->exclude;
        $serving = $request->serving;

        $query = Table::where('id_br', $branchId)
            ->where('id_table', '!=', $excludeId);

        if ($serving) {
            // Lấy bàn đang phục vụ (cho gộp bàn)
            $query->where('status', 2);
        } else {
            // Lấy bàn trống (cho chuyển bàn)
            $query->where('status', 0);
        }

        $tables = $query->orderBy('table_name')->get();

        return response()->json([
            'success' => true,
            'tables' => $tables
        ]);
    }

    // Lấy thông tin bàn (để cập nhật UI sau khi thao tác)
    public function getTableInfo($tableId)
    {
        $table = Table::with(['branch', 'currentOrder.orderDetails', 'currentOrder.orderDetails.menuItem'])
            ->where('id_table', $tableId)
            ->first();

        if (!$table) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy bàn'
            ]);
        }

        $hasOrder = $table->currentOrder && in_array($table->currentOrder->status, [0, 1]);

        return response()->json([
            'success' => true,
            'has_order' => $hasOrder,
            'table' => $table
        ]);
    }

    // Thêm món vào order hiện tại
    public function addItemToOrder(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id_order',
            'items' => 'required|array'
        ]);

        $order = Order::findOrFail($request->order_id);
        
        if($order->status == 2){
            $order->status = 0;
            $order->save();
        }

        foreach ($request->items as $item) {
            $menuItem = MenuItem::find($item['id']);
            if ($menuItem) {
                $existing = OrderDetail::where('id_order', $order->id_order)
                    ->where('id_food', $item['id'])
                    ->where('status', 0)
                    ->first();

                if ($existing) {
                    $existing->quantity += $item['quantity'];
                    $existing->save();
                } else {
                    OrderDetail::create([
                        'id_order' => $order->id_order,
                        'id_food'  => $item['id'],
                        'quantity' => $item['quantity'],
                        'price'    => $menuItem->price,
                        'status'   => 0
                    ]);
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Thêm món thành công'
        ]);
    }

    // Cập nhật số lượng món trong order
    public function updateOrderItem(Request $request, $itemId)
    {
        $orderDetail = OrderDetail::findOrFail($itemId);

        
            if($request->type == 'input'){
                $orderDetail->quantity = $request->quantity;
            }else{
                $orderDetail->quantity += $request->quantity;
            }
            
            $orderDetail->save();

        if ($orderDetail->quantity <= 0){
            $orderDetail->delete();

            // Kiểm tra nếu không còn món nào trong đơn hàng thì cập nhật trạng thái bàn về 0
            $order = Order::find($orderDetail->id_order);
            if ($order) {
                $remainingItems = OrderDetail::where('id_order', $order->id_order)->count();
                if ($remainingItems == 0) {
                    // Cập nhật trạng thái bàn về trống
                    $table = Table::find($order->id_table);
                    if ($table) {
                        $table->status = 0;
                        $table->save();
                    }
                    // Cập nhật trạng thái order thành hủy
                    $order->status = 4;
                    $order->save();
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật số lượng thành công'
        ]);
    }

    // Chuyển bàn
    public function transferTable(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id_order',
            'to_table_id' => 'required|exists:tables,id_table'
        ]);

        $order = Order::findOrFail($request->order_id);
        $newTable = Table::findOrFail($request->to_table_id);

        // Kiểm tra bàn mới có trống không
        if ($newTable->status != 0) {
            return response()->json([
                'success' => false,
                'message' => 'Bàn chuyển đến không trống'
            ]);
        }

        // Cập nhật order
        $order->id_table = $request->to_table_id;
        $order->id_br = $newTable->id_br;
        $order->save();

        // Cập nhật trạng thái bàn cũ
        $oldTable = Table::find($request->from_table_id);
        if ($oldTable) {
            $oldTable->status = 0;
            $oldTable->save();
        }

        // Cập nhật trạng thái bàn mới
        $newTable->status = 2;
        $newTable->save();

        return response()->json([
            'success' => true,
            'message' => 'Chuyển bàn thành công'
        ]);
    }

    // Gộp bàn
    public function mergeTable(Request $request)
    {
        $request->validate([
            'from_order_id' => 'required|exists:orders,id_order',
            'to_order_id' => 'required|exists:orders,id_order'
        ]);

        $fromOrder = Order::findOrFail($request->from_order_id);
        $toOrder = Order::where('id_table', $request->to_order_id)
                        ->where('status', 0)
                        ->first();

        // Di chuyển các món từ order cũ sang order mới
        $fromDetails = OrderDetail::where('id_order', $fromOrder->id_order)->get();

        foreach ($fromDetails as $detail) {
            // Kiểm tra xem món đã có trong order đích chưa
            $existingDetail = OrderDetail::where('id_order', $toOrder->id_order)
                ->where('id_food', $detail->id_food)
                ->first();

            if ($existingDetail) {
                $existingDetail->quantity += $detail->quantity;
                $existingDetail->save();
                $detail->delete();
            } else {
                $detail->id_order = $toOrder->id_order;
                $detail->save();
            }
        }

        // Xóa order cũ và cập nhật bàn cũ
        $fromTable = Table::find($fromOrder->id_table);
        if ($fromTable) {
            $fromTable->status = 0;
            $fromTable->save();
        }

        // $fromOrder->status = 0; // Hủy
        // $fromOrder->save();

        return response()->json([
            'success' => true,
            'message' => 'Gộp bàn thành công',
        ]);
    }

    // Thanh toán
    public function payment(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id_order',
            'customer_phone' => 'nullable|regex:/^(0[35789])[0-9]{8}$/',
        ], [
            'customer_phone.regex' => 'Số điện thoại không hợp lệ',
        ]);

        $order = Order::findOrFail($request->order_id);

        // Gắn khách hàng vào order nếu tìm thấy theo SĐT hoặc id_cus được gửi lên
        $idCus = $request->id_cus ?: null;
        if (!$idCus && $request->filled('customer_phone')) {
            $customer = Customer::where('phone', $request->customer_phone)->first();
            if ($customer) {
                $idCus = $customer->id_cus;
            }
        }

        if ($idCus) {
            $order->id_cus = $idCus;
        }

        // Cập nhật trạng thái order
        $order->status = 3; // Hoàn thành
        $order->save();

        // Cập nhật trạng thái bàn
        $table = Table::find($order->id_table);
        if ($table) {
            $table->status = 0;
            $table->save();
        }

        // Tạo hóa đơn (Bill) - status = 2: đã thanh toán
        $bill = Bill::create([
            'id_order'=> $request->order_id,
            'total' => $request->total,
            'pay_method' => $request->payment_method,
            'pay_time' => Carbon::now(),
            'status' => 2
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Thanh toán thành công',
            'id_cus' => $idCus,
            'bill_id' => $bill->id_bill,
            'redirect' => route('admin.bill.show', $bill->id_bill),
        ]);
    }

    /**
     * Đánh dấu hóa đơn CHƯA THANH TOÁN.
     * Dành cho trường hợp khách ăn xong bỏ đi không thanh toán:
     *   - Đóng order (status = 3) để giải phóng bàn.
     *   - Tạo Bill với status = 1 (chưa thanh toán), không có phương thức/thời gian thanh toán.
     *   - Đánh dấu nợ để admin/quản lý xử lý sau.
     */
    public function markUnpaid(Request $request, $orderId)
    {
        $order = Order::findOrFail($orderId);

        // Không cho phép đánh dấu nếu order đã bị hủy
        if ($order->status == 4) {
            return response()->json([
                'success' => false,
                'message' => 'Đơn hàng đã bị hủy, không thể tạo hóa đơn',
            ], 422);
        }

        // Kiểm tra đã có hóa đơn cho order này chưa
        $existingBill = Bill::where('id_order', $order->id_order)->first();
        if ($existingBill) {
            return response()->json([
                'success' => false,
                'message' => 'Đơn hàng này đã có hóa đơn',
            ], 422);
        }

        // Gắn khách hàng nếu tra cứu được từ request (không bắt buộc)
        $idCus = $request->id_cus ?: null;
        if (!$idCus && $request->filled('customer_phone')) {
            $customer = Customer::where('phone', $request->customer_phone)->first();
            if ($customer) {
                $idCus = $customer->id_cus;
            }
        }
        if ($idCus) {
            $order->id_cus = $idCus;
        }

        // Tính tổng tiền từ OrderDetail nếu client không gửi total
        $details = OrderDetail::where('id_order', $order->id_order)->get();
        $computedTotal = $details->sum(fn ($d) => ((int) $d->quantity) * ((float) $d->price));
        $subtotal = (float) $request->input('total', $computedTotal);
        $tax      = round($subtotal * 0.08, 2);
        $total    = $subtotal + $tax;

        // Đóng order, giải phóng bàn
        $order->status = 3; // Hoàn thành order (nhưng KHÔNG có thanh toán)
        $order->save();

        $table = Table::find($order->id_table);
        if ($table) {
            $table->status = 0; // Bàn trống
            $table->save();
        }

        // Tạo Bill với status = 1: CHƯA THANH TOÁN
        $bill = Bill::create([
            'id_order'   => $order->id_order,
            'total'      => $total,
            'pay_method' => null,
            'pay_time'   => null,
            'status'     => 1, // 1 = chưa thanh toán
        ]);

        return response()->json([
            'success'  => true,
            'message'  => 'Đã ghi nhận hóa đơn chưa thanh toán',
            'id_cus'   => $idCus,
            'bill_id'  => $bill->id_bill,
            'redirect' => route('admin.bill.show', $bill->id_bill),
        ]);
    }

    // Tra cứu khách hàng theo số điện thoại (dùng cho modal thanh toán)
    public function lookupCustomer(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|max:20',
        ], [
            'phone.required' => 'Vui lòng nhập số điện thoại',
        ]);

        $customer = Customer::where('phone', $request->phone)->first();

        if ($customer) {
            return response()->json([
                'found'    => true,
                'customer' => [
                    'id_cus' => $customer->id_cus,
                    'name'   => $customer->name,
                    'phone'  => $customer->phone,
                    'email'  => $customer->email,
                ],
            ]);
        }

        return response()->json([
            'found'   => false,
            'message' => 'Không tìm thấy khách hàng với SĐT này',
        ]);
    }

    // ==================== Kitchen View ====================

    // Hiển thị giao diện bếp (server-side pagination)
    public function kitchen(Request $request)
    {
        $branches = $this->availableBranches();
        $selectedBranch = $this->currentBranchId($request);

        $orders = collect();
        if ($selectedBranch) {
            $orders = Order::with([
                'table',
                'orderDetails.menuItem'
            ])
            ->where('id_br', $selectedBranch)
            ->whereIn('status', [0, 1]) // Chờ, Chế biến, Đang phục vụ
            ->orderBy('order_time', 'asc')
            ->paginate(8)
            ->withQueryString();
        }

        $showBranchSelector = $this->showBranchSelector();
        return view('admin.pages.kitchen', compact('branches', 'selectedBranch', 'orders', 'showBranchSelector'));
    }

    // Cập nhật trạng thái món trong bếp
    // public function updateKitchenItemStatus(Request $request, $orderId, $itemId)
    // {
    //     $request->validate([
    //         'status' => 'required|in:1,2'
    //     ]);

    //     $orderDetail = OrderDetail::where('id_ordetail', $itemId)
    //         ->where('id_order', $orderId)
    //         ->first();

    //     if (!$orderDetail) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Không tìm thấy món'
    //         ]);
    //     }

    //     $orderDetail->status = $request->status;
    //         $orderDetail->save();


    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Cập nhật trạng thái thành công'
    //     ]);
    // }
}
