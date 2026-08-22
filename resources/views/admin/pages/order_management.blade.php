@extends('layouts.admin_home')

@section('title', 'Quản lý đơn hàng')

@section('content')
    <div class="d-flex justify-content-between align-items-center mt-3 mb-3 px-3">
        <h4 class="mb-0">📋 Quản lý đơn hàng</h4>
    </div>

    <div class="order-management-container h-100">
        <!-- Panel bên trái: Danh sách bàn theo chi nhánh -->
        <div class="table-list-panel">
            <div class="panel-header">
                <div class="branch-selector">
                    <label class="mb-0"><i class="fas fa-fw fa-building"></i> Chi nhánh:</label>
                    <select class="form-select form-select-sm" id="branchSelect" data-route="{{ route('admin.order.management') }}">
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id_br }}" {{ $selectedBranch == $branch->id_br ? 'selected' : '' }}>
                                {{ $branch->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <h6 class="mb-0">🪑 Danh sách bàn</h6>
            </div>

            <div class="table-grid" id="tableGrid">
                @forelse($tables->where('status', '!=', 1) as $table)
                    
                    <div class="table-card {{ $table->status == 0 ? 'empty' : 'serving' }}"
                         data-table-id="{{ $table->id_table }}"
                         data-table-name="{{ $table->table_name }}"
                         data-status="{{ $table->status }}"
                         onclick="selectTable({{ $table->id_table }})">
                        <div class="table-card-name">{{ $table->table_name }}</div>
                        <div class="table-card-info">
                            @if($table->status == 0)
                                <span class="table-status">Trống</span>
                            @else
                                <span class="table-status">Đang phục vụ</span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="no-tables-message" style="grid-column: span 2; text-align: center; padding: 20px; color: #999;">
                        Không có bàn nào
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Panel bên phải: Chi tiết order -->
        <div class="order-detail-panel">
            <div class="order-detail-header" id="orderDetailHeader">
                <div class="selected-table-title">
                    <h5 id="selectedTableName">Bàn 1</h5>
                    <span class="order-time" id="orderTime"></span>
                </div>
                <div class="order-actions">
                    <button class="btn btn-primary" onclick="openAddFoodModal()">
                        <i class="fas fa-plus"></i> Thêm món
                    </button>
                    <button class="btn btn-warning" onclick="openTransferModal()">
                        <i class="fas fa-exchange-alt"></i> Chuyển bàn
                    </button>
                    <button class="btn btn-info" onclick="openMergeModal()">
                        <i class="fas fa-object-group"></i> Gộp bàn
                    </button>
                    <button class="btn btn-success" onclick="openPaymentModal()">
                        <i class="fas fa-credit-card"></i> Thanh toán
                    </button>
                </div>
            </div>

            <div class="order-items-list" id="orderItemsList">
                <div class="empty-order-detail">
                    <i class="fas fa-clipboard-list"></i>
                    <p>Chọn bàn để xem chi tiết order</p>
                </div>
            </div>

            <div class="order-summary" id="orderSummary" style="display: none;">
                <div class="order-summary-row">
                    <span>Tạm tính:</span>
                    <span id="subtotalAmount">0 ₫</span>
                </div>
                <div class="order-summary-row">
                    <span>Giảm giá:</span>
                    <span id="discountAmount">0 ₫</span>
                </div>
                <div class="order-summary-row total">
                    <span>Tổng cộng:</span>
                    <span id="totalAmount">0 ₫</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Hidden data -->
    <input type="hidden" id="currentOrderId" value="">
    <input type="hidden" id="currentTableId" value="">
@endsection

<!-- Modal Thêm món -->
<div class="modal fade" id="addFoodModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">🍽️ Thêm món</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <input type="text" class="form-control" id="searchMenuItem" placeholder="Tìm món ăn...">
                </div>
                <div class="category-tabs" id="categoryTabs">
                    <button class="category-tab active" data-category-id="all">Tất cả</button>
                    @foreach($categories as $category)
                        <button class="category-tab" data-category-id="{{ $category->id_cate }}">
                            {{ $category->name }}
                        </button>
                    @endforeach
                </div>
                <div class="menu-modal-grid" id="menuModalGrid">
                    <!-- Menu items loaded via AJAX -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-primary" onclick="addFoodToOrder()">
                    <i class="fas fa-plus"></i> Thêm vào order
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Chuyển bàn -->
<div class="modal fade" id="transferTableModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Chuyển bàn</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Chọn bàn chuyển đến:</p>
                <div class="table-selection-grid" id="transferTableGrid">
                    <!-- Tables loaded via AJAX -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-primary" onclick="transferTable()">Chuyển bàn</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Gộp bàn -->
<div class="modal fade" id="mergeTableModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Gộp bàn</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Chọn bàn gộp vào:</p>
                <div class="table-selection-grid" id="mergeTableGrid">
                    <!-- Tables loaded via AJAX -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-warning" onclick="mergeTable()">Gộp bàn</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Thanh toán -->
<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Thanh toán</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Thông tin thanh toán</h6>
                        <div class="mb-3">
                            <label class="form-label">Số điện thoại khách hàng:</label>
                            <div class="input-group">
                                <input type="tel" class="form-control" id="customerPhone" placeholder="VD: 0912345678" maxlength="10">
                                <button class="btn btn-outline-primary" type="button" onclick="lookupCustomer()">
                                    <i class="fas fa-search"></i> Tra cứu
                                </button>
                            </div>
                            <small id="customerLookupResult" class="form-text"></small>
                            <input type="hidden" id="customerId" value="">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tổng tiền:</label>
                            <input type="text" class="form-control" id="paymentTotal" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Giảm giá (%):</label>
                            <input type="number" class="form-control" id="discountPercent" value="0" min="0" max="100" onchange="calculatePayment()">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Số tiền giảm:</label>
                            <input type="text" class="form-control" id="paymentDiscountAmount" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Thành tiền:</label>
                            <input type="text" class="form-control fw-bold text-danger" id="finalAmount" readonly>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6>Phương thức thanh toán</h6>
                        <div class="payment-method-options mb-3">
                            <div class="payment-method-option selected" data-method="cash" onclick="selectPaymentMethod('cash')">
                                <i class="fas fa-money-bill-wave text-success"></i>
                                <div>Tiền mặt</div>
                            </div>
                            <div class="payment-method-option" data-method="transfer" onclick="selectPaymentMethod('transfer')">
                                <i class="fas fa-mobile-alt text-info"></i>
                                <div>Chuyển khoản</div>
                            </div>
                        </div>

                        <!-- QR Code Display for Transfer -->
                        <div id="qrCodeDisplay" class="text-center mt-3" style="display: none;">
                            <div class="mb-2">
                                <img id="vietqrImage" src="" alt="QR Code" style="max-width: 200px; border: 2px solid #dee2e6; border-radius: 8px;">
                            </div>
                            <div class="small text-muted">
                                <p class="mb-1">Quét mã QR để thanh toán</p>
                                <p class="mb-0"><strong>Ngân hàng:</strong> Kiên Long Bank</p>
                                <p class="mb-0"><strong>Số tài khoản:</strong> 101499100004183465</p>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Ghi chú:</label>
                            <textarea class="form-control" id="paymentNote" rows="3"></textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-danger" onclick="markUnpaid()" title="Dành cho khách ăn xong bỏ đi không thanh toán">
                    <i class="fas fa-exclamation-triangle"></i> Chưa thanh toán
                </button>
                <button type="button" class="btn btn-success" onclick="processPayment()">
                    <i class="fas fa-check"></i> Xác nhận thanh toán
                </button>
            </div>
        </div>
    </div>
</div>
<script src="{{ asset('assets/admin/js/order_management.js') }}"></script> 
