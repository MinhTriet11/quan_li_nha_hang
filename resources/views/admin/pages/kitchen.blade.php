@extends('layouts.admin_home')

@section('title', 'Bếp - Quản lý món ăn')

@section('css')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/kitchen.css') }}">
@endsection

@section('content')
    <!-- Main Content -->
    <div id="content">



        <!-- Begin Page Content -->
        <div class="container-fluid">

            <div class="kitchen-container" id="kitchenContainer">
                <div class="kitchen-header">
                    <div class="d-flex align-items-center gap-3">
                        <h4>Bếp - Quản lý món</h4>
                        <span class="order-count" id="orderCount">
                            {{ $orders->total() ?? 0 }} đơn
                        </span>
                        @if (!($showBranchSelector ?? true))
                            <span class="badge badge-info"><i class="fas fa-map-marker-alt"></i>
                                {{ $branches->first()->name ?? '' }}</span>
                        @endif
                    </div>
                    <form method="GET" action="{{ route('admin.kitchen') }}" class="d-flex align-items-center gap-3 m-0">
                        @if ($showBranchSelector ?? true)
                            <div class="branch-selector-kitchen">
                                <label class="text-dark me-2 fw-bold">Chi nhánh:</label>
                                <select id="branchSelect" name="branch_id"
                                    class="form-select form-select-sm d-inline-block w-auto">
                                    @foreach ($branches as $branch)
                                        <option value="{{ $branch->id_br }}"
                                            {{ (string) $selectedBranch === (string) $branch->id_br ? 'selected' : '' }}>
                                            {{ $branch->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @else
                            <input type="hidden" name="branch_id" value="{{ $selectedBranch }}">
                        @endif
                        <button type="submit" class="refresh-btn">
                            <i class="fas fa-sync-alt"></i> Làm mới
                        </button>
                    </form>
                </div>

                <div class="order-cards-container" id="orderCardsContainer">

                    @forelse($orders as $order)
                        @php
                            $pendingItems = $order->orderDetails->where('status', '!=', 2);
                        @endphp

                        @if ($pendingItems->count() > 0)
                            @php
                                $orderTime = \Carbon\Carbon::parse($order->order_time)->format('H:i');
                                $tableName = $order->table->table_name ?? 'Bàn';
                                $status = $order->status;
                                $statusClass = "status-{$status}";

                                // Bản đồ trạng thái đơn: 0 chờ, 1 chế biến, 2 đang phục vụ, 3 hoàn thành, 4 hủy
                                $statusMap = [
                                    0 => ['badge' => 'waiting', 'text' => 'Đang chờ'],
                                    1 => ['badge' => 'cooking', 'text' => 'Đang chế biến'],
                                    2 => ['badge' => 'serving', 'text' => 'Đang phục vụ'],
                                    3 => ['badge' => 'completed', 'text' => 'Hoàn thành'],
                                    4 => ['badge' => 'cancelled', 'text' => 'Đã hủy'],
                                ];
                                $statusBadgeClass = $statusMap[$status]['badge'] ?? 'waiting';
                                $statusText = $statusMap[$status]['text'] ?? 'Đang chờ';

                                // State machine: 0->1, 1->2, 2->3. Trạng thái 3 và 4 là cuối, không có nút chuyển tiếp.
                                $actions = [
                                    0 => ['next' => 1, 'btn' => 'btn-waiting', 'label' => 'Bắt đầu chế biến'],
                                    1 => ['next' => 2, 'btn' => 'btn-cooking', 'label' => 'Chế biến xong'],
                                    2 => ['next' => 3, 'btn' => 'btn-completed', 'label' => 'Hoàn thành chế biến'],
                                ];
                                $action = $actions[$status] ?? null;
                                $nextStatus = $action['next'] ?? null;
                                $btnClass = $action['btn'] ?? '';
                                $btnText = $action['label'] ?? '';
                                $isDisabled = $action ? '' : 'disabled';
                            @endphp
                            <div class="order-card {{ $statusClass }}">
                                <div class="order-card-header">
                                    <span class="table-name">🍽️ {{ $tableName }}</span>
                                    <span class="order-time">⏰ {{ $orderTime }}</span>
                                </div>
                                <div class="order-card-body">
                                    @if ($order->orderDetails && $order->orderDetails->count() > 0)
                                        @foreach ($order->orderDetails as $item)
                                            @php
                                                $isServed = $item->status;
                                                $itemClass =
                                                    $isServed == 2
                                                        ? 'served'
                                                        : ($isServed == 0
                                                            ? 'waiting'
                                                            : 'cooking');
                                                $itemName = $item->menuItem->name ?? 'Món ăn';
                                            @endphp
                                            @if ($isServed !== 2)
                                                <div class="food-item {{ $itemClass }}">
                                                    <div class="food-info">
                                                        <div class="food-name">{{ $itemName }}</div>
                                                        <div class="food-quantity">SL: {{ $item->quantity }}</div>
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach
                                    @else
                                        <p class="text-muted text-center">Không có món</p>
                                    @endif
                                </div>
                                <div class="order-card-footer">
                                    <span class="status-badge {{ $statusBadgeClass }}">{{ $statusText }}</span>
                                    @if ($action)
                                        <button class="order-status-btn {{ $btnClass }}"
                                            onclick="updateOrderStatus({{ $order->id_order }}, {{ $nextStatus }})"
                                            {{ $status == 2 ? 'disabled' : '' }}>
                                            {{ $btnText }}
                                        </button>
                                        @if ($status == 0)
                                            <button class="btn-cancel-order"
                                                onclick="updateOrderStatus({{ $order->id_order }}, 4)" title="Hủy đơn">
                                                <i class="fas fa-times"></i> Hủy
                                            </button>
                                        @endif
                                    @endif
                                </div>
                            </div>
                            @endif
                        @empty
                            <div class="empty-kitchen">
                                <i class="fas fa-utensils"></i>
                                <p>Không có đơn hàng nào đang chờ</p>
                            </div>
                        @endforelse
                </div>

                @if ($orders->hasPages())
                    <div class="kitchen-pagination">
                        {{ $orders->appends(request()->query())->links() }}
                    </div>
                @endif
            </div>

        </div>
        <!-- /.container-fluid -->

    </div>
    <!-- End of Main Content -->
@endsection

@section('js')
    <script src="{{ asset('assets/admin/js/kitchen.js') }}"></script>
@endsection
