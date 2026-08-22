@extends('layouts.admin_home')

@section('title', 'Quản lý Order')

@section('content')
    <div class="d-flex justify-content-between align-items-center mt-3 mb-3 px-3">
        <h4 class="mb-0">📋 Order Món Ăn</h4>
    </div>

    <div class="order-container h-100">
        <!-- Panel bên trái: Menu món ăn -->
        <div class="menu-panel">
            <div class="panel-header">
                <div class="branch-selector">
                    <label class="mb-0"><i class="fas fa-fw fa-building"></i> Chi nhánh:</label>
                    <select class="form-select form-select-sm" id="branchSelect"
                        data-route="{{ route('admin.order.tables') }}">
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id_br }}" {{ $selectedBranch == $branch->id_br ? 'selected' : '' }}>
                                {{ $branch->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <h6 class="mb-0">🍽️ Thực đơn</h6>
            </div>

            <!-- Search -->
            <div class="search-box px-3 py-2">
                <input type="text" id="searchInput" class="form-control form-control-sm" placeholder="🔍 Tìm món ăn...">
            </div>

            <!-- Danh mục tabs -->
            <div class="category-tabs" id="categoryTabs">
                <button class="category-tab active" data-category-id="all">Tất cả</button>
                @foreach ($categories as $category)
                    <button class="category-tab" data-category-id="{{ $category->id_cate }}">
                        {{ $category->name }}
                    </button>
                @endforeach
            </div>

            <!-- Danh sách món ăn -->
            <div class="panel-body">
                <div class="menu-grid" id="menuGrid" data-route="{{ route('admin.order.menuItems') }}">
                    <!-- Menu items sẽ được load bằng AJAX -->
                </div>
            </div>
        </div>

        <!-- Panel bên phải: Bàn và giỏ hàng -->
        <div class="table-panel">
            <!-- Chọn bàn -->
            <div class="panel-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">🪑 Chọn bàn</h6>
                </div>
                <div class="mt-2">
                    <select class="form-select form-select-sm" id="tableSelect">
                        <option value="">-- Chọn bàn --</option>
                        @foreach ($tables as $table)
                            @if ($table->status != 1 || $table->status != 2)
                                <option value="{{ $table->id_table }}" data-table-name="{{ $table->table_name }}">
                                    {{ $table->table_name }}
                                </option>
                            @endif
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Giỏ hàng / Order -->
            <div class="cart-section">
                <div class="cart-header">
                    <span>🛒 Order món</span>
                    <span id="cartCount"><span class="cart-badge">0</span></span>
                </div>

                <div id="selectedTableInfo" class="selected-table-info" style="display: none;">
                    <i class="fas fa-check-circle"></i>
                    <span>Đang order cho bàn: <strong id="selectedTableName"></strong></span>
                </div>

                <div id="cartItems">
                    <div class="empty-cart">
                        <i class="fas fa-shopping-basket"></i>
                        <p>Chọn bàn và thêm món vào order</p>
                    </div>
                </div>

                

                <div class="cart-summary" id="cartSummary" style="display: none;">
                    <!-- Ghi chú đơn hàng -->
                <div class="order-note px-3 py-2">
                    <textarea class="form-control form-control-sm" id="orderNote" name="note" rows="2"
                        placeholder="Nhập ghi chú cho đơn hàng..."></textarea>
                </div>
                    <div class="cart-total">
                        <span>Tổng cộng:</span>
                        <span class="cart-total-amount" id="cartTotalAmount">0 ₫</span>
                    </div>
                    <button class="btn btn-success btn-confirm-order" id="confirmOrderBtn">
                        <i class="fas fa-check"></i> Xác nhận gọi món
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal xác nhận -->
    <div class="modal fade" id="confirmModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Xác nhận đơn hàng</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Bạn có chắc chắn muốn xác nhận gọi món cho bàn <strong id="confirmTableName"></strong>?</p>
                    <div class="alert alert-info">
                        <strong>Tổng tiền:</strong> <span id="confirmTotal"></span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="button" class="btn btn-success" id="submitOrderBtn"
                        data-route="{{ route('admin.order.create') }}">Xác nhận</button>
                </div>
            </div>
        </div>
    </div>
@endsection
<script src="{{ asset('assets/admin/js/order_admin.js') }}"></script>
