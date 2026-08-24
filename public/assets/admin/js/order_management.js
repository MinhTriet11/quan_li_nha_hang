// Order Management JavaScript - Table Based Layout
let selectedTableId = null;
let currentOrder = null;
let selectedMenuItems = [];
let paymentMethod = 'cash';
let transferTableId = null;
let mergeTableId = null;

// Khởi tạo
document.addEventListener('DOMContentLoaded', function() {
    // Event listener cho branch change
    const branchSelect = document.getElementById('branchSelect');
    if (branchSelect) {
        branchSelect.addEventListener('change', function() {
            window.location.href = this.dataset.route + '?branch_id=' + this.value;
        });
    }
});

// Chọn bàn từ danh sách
function selectTable(tableId) {
    selectedTableId = tableId;

    // Update UI - đánh dấu bàn được chọn
    document.querySelectorAll('.table-card').forEach(card => {
        card.classList.remove('active');
        if (parseInt(card.dataset.tableId) === tableId) {
            card.classList.add('active');
        }
    });

    // Load chi tiết order của bàn
    loadOrderDetails(tableId);
}

// Load chi tiết order của bàn
function loadOrderDetails(tableId) {
    
    fetch(`/admin/orders/${tableId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                currentOrder = data.data;
                renderOrderDetails(data.data);
            } else {
                // Không có order - bàn trống
                currentOrder = null;
                renderEmptyOrder(tableId);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            currentOrder = null;
            renderEmptyOrder(tableId);
        });
}

// Render chi tiết order
function renderOrderDetails(order) {
    const tableName = document.querySelector(`.table-card[data-table-id="${selectedTableId}"]`)?.dataset.tableName || 'Bàn';

    // Hiển thị header
    const header = document.getElementById('orderDetailHeader');
    header.classList.add('show');
    document.getElementById('selectedTableName').textContent = tableName;
    document.getElementById('orderTime').textContent = order ? new Date(order.order_time).toLocaleTimeString('vi-VN', {hour: '2-digit', minute: '2-digit'}) : '';
    document.getElementById('currentOrderId').value = order ? order.id_order : '';
    document.getElementById('currentTableId').value = selectedTableId;

    const itemsList = document.getElementById('orderItemsList');
    const summary = document.getElementById('orderSummary');
    const detailPanel = document.querySelector('.order-detail-panel');

    if (!order || !order.order_details || order.order_details.length === 0) {
        itemsList.innerHTML = `
            <div class="empty-order-detail">
                <i class="fas fa-utensils"></i>
                <p>Chưa có món nào</p>
            </div>
        `;
        summary.style.display = 'none';
        if (detailPanel) detailPanel.classList.remove('has-items');
        return;
    }

    if (detailPanel) detailPanel.classList.add('has-items');

    let itemsHtml = '';
    let subtotal = 0;

    order.order_details.forEach(item => {
        
        const itemTotal = item.quantity * item.price;
        subtotal += itemTotal;
        const statusClass = item.status == 2 ? 'served' : 'pending';
        const statusText = item.status == 2 ? 'Đã xong' : (item.status == 1 ? 'Đang chế biến' : 'Chờ chế biến');
        const imageUrl = item.menu_item?.image ? item.menu_item.image : '';

        itemsHtml += `
            <div class="order-item" data-item-id="${item.id_ordetail}">
                <div class="order-item-image">
                    ${imageUrl ? `<img src="/storage/menu_items/${imageUrl}" alt="">` : '🍽️'}
                </div>
                <div class="order-item-info">
                    <div class="order-item-name">${item.menu_item?.name || 'Món ăn'}</div>
                    <div class="order-item-price">${formatPrice(item.price)} ₫</div>
                </div>
                ${ (item.status == 2 || item.status == 1) ? `<div class="order-item-quantity">
                    <span class="quantity-value" >x${item.quantity}</span>
                </div>` : `<div class="order-item-quantity">
                    <button class="quantity-btn" onclick="updateItemQuantity(${item.id_ordetail}, -1)">-</button>
                    <input type="number" class="quantity-value" value="${item.quantity}" min="1" onchange="updateItemQuantity(${item.id_ordetail}, this.value, 'input')">
                    <button class="quantity-btn" onclick="updateItemQuantity(${item.id_ordetail}, 1)">+</button>
                </div>` }
                
                <div class="order-item-total">${formatPrice(itemTotal)} ₫</div>
                <span class="order-item-status ${statusClass}" data-status-id = "${item.status}">${statusText}</span>
            </div>
        `;
    });

    itemsList.innerHTML = itemsHtml;

    // Summary
    const discount = 0;
    const total = subtotal - discount;

    document.getElementById('subtotalAmount').textContent = formatPrice(subtotal) + ' ₫';
    document.getElementById('discountAmount').textContent = formatPrice(discount) + ' ₫';
    document.getElementById('totalAmount').textContent = formatPrice(total) + ' ₫';
    summary.style.display = 'block';
}

// Render khi bàn trống
function renderEmptyOrder(tableId) {
    const tableName = document.querySelector(`.table-card[data-table-id="${tableId}"]`)?.dataset.tableName || 'Bàn';

    // Hiển thị header
    const header = document.getElementById('orderDetailHeader');
    header.classList.add('show');
    document.getElementById('selectedTableName').textContent = tableName;
    document.getElementById('orderTime').textContent = '';
    document.getElementById('currentOrderId').value = '';
    document.getElementById('currentTableId').value = tableId;

    document.getElementById('orderItemsList').innerHTML = `
        <div class="empty-order-detail">
            <i class="fas fa-utensils"></i>
            <p>Chưa có món nào - Bàn trống</p>
        </div>
    `;
    document.getElementById('orderSummary').style.display = 'none';
}

// Cập nhật số lượng món
function updateItemQuantity(itemId, change, type = '') {
    fetch(`/admin/orders/item/${itemId}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        },
        body: JSON.stringify({ quantity: change , type: type})
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // toastr.success(data.message, 'Thành công')
            loadOrderDetails(selectedTableId);
            updateTableCard(selectedTableId);
        }
    })
    .catch(error => console.error('Error:', error));
}

// Cập nhật thông tin bàn trong grid
function updateTableCard(tableId) {
    
    fetch(`/admin/orders/table-info/${tableId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const card = document.querySelector(`.table-card[data-table-id="${tableId}"]`);
                if (card) {
                    if (data.has_order) {
                        card.classList.remove('empty');
                        card.classList.add('serving');
                        card.dataset.status = '2';
                        card.querySelector('.table-status').textContent = 'Đang phục vụ';
                    } else {
                        card.classList.remove('serving');
                        card.classList.add('empty');
                        card.dataset.status = '0';
                        card.querySelector('.table-status').textContent = 'Trống';
                    }
                }
            }
        });
}

// ==================== Add Food Modal ====================
function openAddFoodModal() {
    const statusId = document.querySelectorAll('.order-item-status');
    let isCooking = true;

    statusId.forEach(status => {
        if(status.dataset.statusId == 1){
            isCooking = false;
        }
    })

    if(!isCooking){
        alert('Đơn đang chế biến, vui lòng thử lại sau');
        return;
    }

    selectedMenuItems = [];
    document.getElementById('searchMenuItem').value = '';

    // Reset category tabs
    document.querySelectorAll('.category-tab').forEach(tab => {
        tab.classList.remove('active');
    });
    document.querySelector('.category-tab[data-category-id="all"]').classList.add('active');

    loadMenuItems();

    const modal = new bootstrap.Modal(document.getElementById('addFoodModal'));
    modal.show();
}

function loadMenuItems(categoryId = 'all', search = '') {
    fetch(`/admin/orders/menu-items?category_id=${categoryId}&search=${search}`)
        .then(response => response.json())
        .then(data => {
            renderMenuItems(data.data || data);
        });
}

function renderMenuItems(items) {
    const grid = document.getElementById('menuModalGrid');
    
    if (!grid) return;

    if (!items || items.length === 0) {
        grid.innerHTML = '<div style="grid-column: span 4; text-align: center; padding: 20px; color: #999;">Không có món ăn</div>';
        return;
    }

    let html = '';
    items.forEach(item => {
        const isSelected = selectedMenuItems.some(i => i.id === item.id); //some it nhat co 1 phan tu thoa dieu kien
        const imageUrl = item.image || '';

        html += `
            <div class="menu-item-card ${isSelected ? 'selected' : ''}"
                 data-id="${item.id_food}"
                 data-name="${item.name}"
                 data-price="${item.price}"
                 onclick="toggleMenuItem(this)">
                <div class="menu-item-card-image">
                    ${imageUrl ? `<img src="/storage/menu_items/${imageUrl}" alt="">` : '🍽️'}
                </div>
                <div class="menu-item-card-name">${item.name}</div>
                <div class="menu-item-card-price">${formatPrice(item.price)} ₫</div>
            </div>
        `;
    });
    grid.innerHTML = html;
}

function toggleMenuItem(element) {
    const id = parseInt(element.dataset.id);
    const name = element.dataset.name;
    const price = parseFloat(element.dataset.price);

    const index = selectedMenuItems.findIndex(i => i.id === id); //tìm xem có id đã tồn tại trong selected chưa, nếu có thì xóa selected

    if (index > -1) {
        selectedMenuItems.splice(index, 1);
        element.classList.remove('selected');
    } else {
        selectedMenuItems.push({ id, name, price, quantity: 1 });
        element.classList.add('selected');
    }
}

function addFoodToOrder() {
    if (selectedMenuItems.length === 0) {
        alert('Vui lòng chọn ít nhất một món');
        return;
    }

    
    const orderId = document.getElementById('currentOrderId').value;
    //check có order trước đó chưa
    if (orderId) {
        // Thêm vào order hiện tại
        fetch('/admin/orders/add-item', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            },
            body: JSON.stringify({
                order_id: orderId,
                items: selectedMenuItems
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('addFoodModal')).hide();
                loadOrderDetails(selectedTableId);
                updateTableCard(selectedTableId);
            } else {
                alert(data.message || 'Có lỗi xảy ra');
            }
        });
    } else {
        // Tạo order mới trước
        fetch('/admin/orders/create', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            },
            body: JSON.stringify({
                table_id: selectedTableId,
                items: selectedMenuItems
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('addFoodModal')).hide();
                // Reload để cập nhật danh sách bàn
                setTimeout(() => window.location.reload(), 500);
            } else {
                alert(data.message || 'Có lỗi xảy ra');
            }
        });
    }
}

// Search menu items
document.addEventListener('input', function(e) {
    if (e.target.id === 'searchMenuItem') {
        const activeCategory = document.querySelector('.category-tab.active')?.dataset.categoryId || 'all';
        loadMenuItems(activeCategory, e.target.value);
    }
});

// Category tabs click
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('category-tab')) {
        document.querySelectorAll('.category-tab').forEach(t => t.classList.remove('active'));
        e.target.classList.add('active');
        const search = document.getElementById('searchMenuItem')?.value || '';
        loadMenuItems(e.target.dataset.categoryId, search);
    }
});

// ==================== Transfer Table ====================
function openTransferModal() {
    if (!currentOrder) { //currenOrder duoc gan khi selectTable
        alert('Vui lòng chọn bàn có order trước!');
        return;
    }

    transferTableId = null;
    loadAvailableTables('transferTableGrid');

    const modal = new bootstrap.Modal(document.getElementById('transferTableModal'));
    modal.show();
}

function loadAvailableTables(gridId) {
    const branchId = document.getElementById('branchSelect')?.value;

    fetch(`/admin/orders/available-tables?branch_id=${branchId}&exclude=${selectedTableId}`)
        .then(response => response.json())
        .then(data => {
            renderTableSelection(gridId, data.tables || []);
        });
}

function renderTableSelection(gridId, tables) {
    const grid = document.getElementById(gridId);
    if (!grid) return;

    if (!tables || tables.length === 0) {
        grid.innerHTML = '<div style="grid-column: span 4; text-align: center; padding: 20px; color: #999;">Không có bàn trống</div>';
        return;
    }

    let html = '';
    tables.forEach(table => {
        const isSelected = gridId === 'transferTableGrid' ?
            (transferTableId === table.id_table) :
            (mergeTableId === table.id_table);

        html += `
            <div class="table-select-card ${isSelected ? 'selected' : ''}"
                 data-id="${table.id_table}"
                 onclick="selectTableForTransfer(this, '${gridId}')">
                <div style="font-weight: 600;">${table.table_name}</div>
                <div style="font-size: 12px; color: #666;">Trống</div>
            </div>
        `;
    });
    grid.innerHTML = html;
}

function selectTableForTransfer(element, gridId) {
    const tableId = parseInt(element.dataset.id);

    if (gridId === 'transferTableGrid') {
        transferTableId = tableId;
    } else {
        mergeTableId = tableId;
    }

    document.querySelectorAll(`#${gridId} .table-select-card`).forEach(card => {
        card.classList.remove('selected');
    });
    element.classList.add('selected');
}

function transferTable() {
    if (!transferTableId) {
        alert('Vui lòng chọn bàn chuyển đến');
        return;
    }

    const orderId = document.getElementById('currentOrderId').value;
    if (!orderId) {
        alert('Không có order để chuyển');
        return;
    }

    fetch('/admin/orders/transfer', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        },
        body: JSON.stringify({
            order_id: orderId,
            from_table_id: selectedTableId,
            to_table_id: transferTableId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('transferTableModal')).hide();
            window.location.reload();
        } else {
            alert(data.message || 'Có lỗi xảy ra');
        }
    });
}

// ==================== Merge Table ====================
function openMergeModal() {

    if (!currentOrder) {
        alert('Vui lòng chọn bàn có order trước!');
        return;
    }

    let result = true;

    const statusOrderDetail = orderItemsList.querySelectorAll('.order-item-status');
    statusOrderDetail.forEach((detail) => {
        if(detail.dataset.statusId == 0 || detail.dataset.statusId == 1 ){
            result = false;
        }
    })

    console.log(statusOrderDetail)

    if(!result){
        alert('Vui lòng đợi chế biến xong');
        return;
    }


    mergeTableId = null;
    loadAvailableTablesForMerge();

    const modal = new bootstrap.Modal(document.getElementById('mergeTableModal'));
    modal.show();
}

function loadAvailableTablesForMerge() {
    const branchId = document.getElementById('branchSelect')?.value;

    // Lấy tất cả bàn đang phục vụ (trừ bàn hiện tại)
    fetch(`/admin/orders/available-tables?branch_id=${branchId}&exclude=${selectedTableId}&serving=2`)
        .then(response => response.json())
        .then(data => {
            renderMergeTableSelection(data.tables || []);
        });
}

function renderMergeTableSelection(tables) {
    const grid = document.getElementById('mergeTableGrid');
    if (!grid) return;

    if (!tables || tables.length === 0) {
        grid.innerHTML = '<div style="grid-column: span 4; text-align: center; padding: 20px; color: #999;">Không có bàn đang phục vụ để gộp</div>';
        return;
    }

    let html = '';
    tables.forEach(table => {
        html += `
            <div class="table-select-card"
                 data-id="${table.id_table}"
                 onclick="selectTableForMerge(this)">
                <div style="font-weight: 600;">${table.table_name}</div>
                <div style="font-size: 12px; color: #666;">Đang phục vụ</div>
            </div>
        `;
    });
    grid.innerHTML = html;
}

function selectTableForMerge(element) {
    mergeTableId = parseInt(element.dataset.id);

    document.querySelectorAll('#mergeTableGrid .table-select-card').forEach(card => {
        card.classList.remove('selected');
    });
    element.classList.add('selected');
}

function mergeTable() {
    

    if (!mergeTableId) {
        alert('Vui lòng chọn bàn gộp vào');
        return;
    }
    

    const orderId = document.getElementById('currentOrderId').value;
    if (!orderId) {
        alert('Không có order để gộp');
        return;
    }

    fetch('/admin/orders/merge', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        },
        body: JSON.stringify({
            from_order_id: orderId,
            to_order_id: mergeTableId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            toastr.success(data.message, 'Thành công');
            bootstrap.Modal.getInstance(document.getElementById('mergeTableModal')).hide();
            setTimeout(() => {
                window.location.reload();

            }, 1000)
        } else {
            toastr.error(data.message || 'Có lỗi xảy ra');
        }
    });
}

// ==================== Payment ====================
function openPaymentModal() {
    const orderItemsList = document.getElementById('orderItemsList');

    const statusOrderDetail = orderItemsList.querySelectorAll('.order-item-status');

    const orderId = document.getElementById('currentOrderId').value;
    const statusTable = document.querySelector('.table-card.active').dataset.status;

    if (!orderId || statusTable == 0) {
        alert('Không có order để thanh toán');
        return;
    }

    let result = true;

    statusOrderDetail.forEach((detail) => {
        if(detail.dataset.statusId == 0 || detail.dataset.statusId == 1 ){
            result = false
        }
    })

    if(!result){
        alert('Vui lòng đợi chế biến xong');
        return;
    }

    // Lấy tổng tiền hiện tại
    const totalText = document.getElementById('totalAmount')?.textContent || '0 ₫';
    const total = parseFloat(totalText.replace(/[^\d]/g, '')) || 0;

    document.getElementById('paymentTotal').value = formatPrice(total) + ' ₫';
    document.getElementById('discountPercent').value = 0;
    document.getElementById('paymentDiscountAmount').value = '0 ₫';
    document.getElementById('finalAmount').value = formatPrice(total) + ' ₫';
    document.getElementById('paymentNote').value = '';

    // Reset ô tra cứu khách hàng
    document.getElementById('customerPhone').value = '';
    document.getElementById('customerId').value = '';
    const lookupResult = document.getElementById('customerLookupResult');
    lookupResult.textContent = '';
    lookupResult.className = 'form-text';

    selectPaymentMethod('cash');

    const modal = new bootstrap.Modal(document.getElementById('paymentModal'));
    modal.show();
}

// Tra cứu khách hàng theo số điện thoại (gõ Enter hoặc bấm nút)
function lookupCustomer() {
    const phone = document.getElementById('customerPhone').value.trim();
    const resultEl = document.getElementById('customerLookupResult');
    const idEl = document.getElementById('customerId');

    resultEl.className = 'form-text';
    idEl.value = '';

    if (!phone) {
        resultEl.textContent = 'Vui lòng nhập số điện thoại';
        resultEl.classList.add('text-danger');
        return;
    }

    fetch(`/admin/customers/lookup?phone=${encodeURIComponent(phone)}`, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
    })
        .then(res => {
            if (!res.ok) {
                throw new Error(`HTTP ${res.status}`);
            }
            return res.json();
        })
        .then(data => {
            if (data.found) {
                idEl.value = data.customer.id_cus;
                resultEl.textContent = `✓ ${data.customer.name} (${data.customer.phone})`;
                resultEl.classList.add('text-success');
            } else {
                resultEl.textContent = '✗ Không tìm thấy khách hàng. Đơn sẽ thanh toán với tư cách khách vãng lai.';
                resultEl.classList.add('text-warning');
            }
        })
        .catch(err => {
            console.error(err);
            resultEl.textContent = 'Có lỗi khi tra cứu. Vui lòng đăng nhập lại.';
            resultEl.classList.add('text-danger');
        });
}

// Cho phép bấm Enter trong ô SĐT để tra cứu luôn
document.addEventListener('DOMContentLoaded', function () {
    const phoneInput = document.getElementById('customerPhone');
    if (phoneInput) {
        phoneInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                lookupCustomer();
            }
        });
    }
});

function selectPaymentMethod(method) {
    paymentMethod = method;
    document.querySelectorAll('.payment-method-option').forEach(opt => {
        opt.classList.remove('selected');
    });
    document.querySelector(`.payment-method-option[data-method="${method}"]`)?.classList.add('selected');

    // Hiển thị QR Code khi chọn chuyển khoản
    const qrDisplay = document.getElementById('qrCodeDisplay');
    if (method === 'transfer') {
        const finalAmount = document.getElementById('finalAmount').value;
        const amount = parseFloat(finalAmount.replace(/[^\d]/g, '')) || 0;
        const orderId = document.getElementById('currentOrderId').value;
        const tableName = document.querySelector('.table-grid .table-card-name').textContent;

        // Tạo URL VietQR với số tiền và nội dung
        const bankCode = 'KienLongBank';
        const accountNumber = '101499100004183465';
        const description = 'Bàn :' + tableName + ' HD' + orderId;

        const qrUrl = `https://vietqr.app/img?acc=${accountNumber}&bank=${bankCode}&amount=${amount}&des=${description}`;
        document.getElementById('vietqrImage').src = qrUrl;
        qrDisplay.style.display = 'block';
    } else {
        qrDisplay.style.display = 'none';
    }
}

function calculatePayment() {
    const totalText = document.getElementById('paymentTotal').value;
    const total = parseFloat(totalText.replace(/[^\d]/g, '')) || 0;
    const discountPercent = parseFloat(document.getElementById('discountPercent').value) || 0;

    const discountAmount = total * (discountPercent / 100);
    const finalAmount = total - discountAmount;

    document.getElementById('paymentDiscountAmount').value = formatPrice(discountAmount) + ' ₫';
    document.getElementById('finalAmount').value = formatPrice(finalAmount) + ' ₫';

    // Cập nhật QR code nếu đang chọn chuyển khoản
    if (paymentMethod === 'transfer') {
        const orderId = document.getElementById('currentOrderId').value;
        const bankCode = 'KienLongBank';
        const accountNumber = '101499100004183465';
        const description = 'DH' + orderId;

        const qrUrl = `https://vietqr.app/img?acc=${accountNumber}&bank=${bankCode}&amount=${finalAmount}&des=${description}`;
        document.getElementById('vietqrImage').src = qrUrl;
    }
}

function processPayment() {
    const orderId = document.getElementById('currentOrderId').value;
    const totalText = document.getElementById('paymentTotal').value;
    const total = parseFloat(totalText.replace(/[^\d]/g, '')) || 0;

    if (!orderId) {
        alert('Không có order để thanh toán');
        return;
    }

    const discountPercent = parseFloat(document.getElementById('discountPercent').value) || 0;
    const note = document.getElementById('paymentNote').value;
    const customerPhone = document.getElementById('customerPhone').value.trim();
    const customerId = document.getElementById('customerId').value;

    fetch('/admin/orders/payment', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        },
        body: JSON.stringify({
            order_id: orderId,
            total: total,
            discount_percent: discountPercent,
            payment_method: paymentMethod,
            note: note,
            customer_phone: customerPhone,
            id_cus: customerId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('paymentModal')).hide();
            toastr.success(data.message, 'Thành công');

            // Hỏi có in hóa đơn không. Nếu có thì mở tab in, không thì reload.
            const billId = data.bill_id || (data.redirect ? (data.redirect.match(/\/(\d+)(?:\?|$)/) || [])[1] : null);
            setTimeout(() => {
                if (billId && confirm('Thanh toán thành công! Bạn có muốn IN HÓA ĐƠN không?')) {
                    const printUrl = `/admin/bills/${billId}/print`;
                    const printWindow = window.open(printUrl, '_blank');
                    if (printWindow) {
                        printWindow.addEventListener('load', function () {
                            try {
                                printWindow.focus();
                                printWindow.print();
                            } catch (e) {
                                console.error('Print error:', e);
                            }
                        });
                    }
                    setTimeout(() => window.location.reload(), 500);
                } else {
                    window.location.reload();
                }
            }, 800);
        } else {
            toastr.error(data.message, 'Có lỗi xảy ra');
        }
    });
}

// Đánh dấu hóa đơn chưa thanh toán (khách ăn xong bỏ đi)
function markUnpaid() {
    const orderId = document.getElementById('currentOrderId').value;
    const totalText = document.getElementById('paymentTotal').value;
    const total = parseFloat(totalText.replace(/[^\d]/g, '')) || 0;

    if (!orderId) {
        alert('Không có order để ghi nhận');
        return;
    }

    if (!confirm('Xác nhận: Khách ăn xong bỏ đi, KHÔNG thanh toán?\nHóa đơn sẽ được tạo với trạng thái "Chưa thanh toán" để xử lý sau.')) {
        return;
    }

    const customerPhone = document.getElementById('customerPhone').value.trim();
    const customerId = document.getElementById('customerId').value;

    fetch(`/admin/orders/${orderId}/mark-unpaid`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        },
        body: JSON.stringify({
            total: total,
            customer_phone: customerPhone,
            id_cus: customerId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('paymentModal')).hide();
            toastr.warning(data.message || 'Đã ghi nhận hóa đơn chưa thanh toán', 'Cảnh báo');

            setTimeout(() => {
                if (data.redirect) {
                    window.location.href = data.redirect;
                } else {
                    window.location.reload();
                }
            }, 1200)
        } else {
            toastr.error(data.message, 'Có lỗi xảy ra');
        }
    })
    .catch(err => {
        console.error(err);
        toastr.error('Có lỗi khi ghi nhận hóa đơn chưa thanh toán', 'Lỗi');
    });
}

// Utility functions
function formatPrice(num) {
    return new Intl.NumberFormat('vi-VN').format(num);
}
