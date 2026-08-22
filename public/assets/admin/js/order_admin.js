// Order Admin JavaScript

let selectedTable = null;
let cart = [];
let currentCategory = 'all';
let currentSearch = '';
let menuItemsData = [];
let currentBranch = null;


// Khởi tạo
document.addEventListener('DOMContentLoaded', function() {
   

    const menuGrid = document.getElementById('menuGrid');


    const categoryTabs = document.querySelectorAll('.category-tab');


    // Chỉ chạy code này nếu đang ở trang order tables
    if (menuGrid) {
        // Lấy branch hiện tại từ data attribute
        currentBranch = menuGrid.dataset.branchId || null;

        // Load menu items
        loadMenuItems();
    }

    // Event listener cho branch change
    const branchSelect = document.getElementById('branchSelect');
    if (branchSelect) {
        branchSelect.addEventListener('change', function() {
            window.location.href = this.dataset.route + '?branch_id=' + this.value;
        });
    }

    // Event listener cho category tabs (chỉ chạy nếu có category tabs)
    if (categoryTabs.length > 0) {
        categoryTabs.forEach(tab => {
            tab.addEventListener('click', function() {
                categoryTabs.forEach(t => t.classList.remove('active'));
                this.classList.add('active');
                currentCategory = this.dataset.categoryId;
                if (menuGrid) {
                    filterMenuItems();
                }
            });
        });
    }

    // Event listener cho table select
    const tableSelect = document.getElementById('tableSelect');
    if (tableSelect) {
        tableSelect.addEventListener('change', function() {
            const tableId = this.value;
            const tableName = this.options[this.selectedIndex]?.dataset?.tableName || '';
            selectTable(tableId, tableName);
        });
    }

    // Event listener cho search input
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout); //clearTimeout khi nguoi dùng nhập dưới 300ms, khi nào ngưng nhập hơn 300ms thì chạy settimeout
            searchTimeout = setTimeout(() => {
                currentSearch = this.value.trim();
                filterMenuItems();
            }, 300);
        });
    }

    // Event listener cho confirm order
    const confirmOrderBtn = document.getElementById('confirmOrderBtn');
    if (confirmOrderBtn) {
        confirmOrderBtn.addEventListener('click', openConfirmModal);
    }

    // Submit order
    const submitOrderBtn = document.getElementById('submitOrderBtn');
    if (submitOrderBtn) {
        submitOrderBtn.addEventListener('click', submitOrder);
    }
});

// Load menu items
function loadMenuItems() {
    fetch('/admin/orders/menu-items?category_id=' + (currentCategory === 'all' ? '' : currentCategory))
        .then(response => {
        
            return response.json();
        })
        .then(data => {
        
            menuItemsData = data.data || data;
            filterMenuItems();
        })
        .catch(error => console.error('Error loading menu items:', error));
}


// Lọc món theo danh mục và search
function filterMenuItems() {
    let filtered = menuItemsData;

    // Lọc theo danh mục
    if (currentCategory !== 'all') {
        filtered = filtered.filter(item => item.id_cate == currentCategory);
    }

    // Lọc theo search
    if (currentSearch) {
        const searchLower = currentSearch.toLowerCase();
        filtered = filtered.filter(item =>
            item.name && item.name.toLowerCase().includes(searchLower)  //includes kiem tra tu khoa cua chuoi
        );
    }

    renderMenuItems(filtered);
}

// Render menu items
function renderMenuItems(items) {
    
    const grid = document.getElementById('menuGrid');
    
    if (!grid) return;

    if (items.length === 0) {
        grid.innerHTML = '<div class="no-items-message">Không có món trong danh mục này</div>';
        return;
    }

    let html = '';
    items.forEach(item => {
        const imageUrl = item.image ? '/storage/menu_items/' + item.image : '';
        html += `
            <div class="menu-item-card" onclick="addToCart(${item.id_food})">
                ${imageUrl ? `<img src="${imageUrl}" alt="${item.name}" class="menu-item-image" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex'">` : ''}
                <div class="menu-item-image" style="display: ${imageUrl ? 'none' : 'flex'}">🍽️</div>
                <div class="menu-item-name">${item.name}</div>
                <div class="menu-item-price">${formatPrice(item.price)}</div>
            </div>
        `;
    });

    grid.innerHTML = html;
    grid.style.display = 'grid';

}

// Chọn bàn từ dropdown
function selectTable(tableId, tableName) {
    if (!tableId) {
        selectedTable = null;
        const selectedTableInfo = document.getElementById('selectedTableInfo');
        const cartSummary = document.getElementById('cartSummary');
        if (selectedTableInfo) selectedTableInfo.style.display = 'none';
        if (cartSummary) cartSummary.style.display = 'none';
        renderCart();
        return;
    }

    selectedTable = { id: tableId, name: tableName };

    const selectedTableInfo = document.getElementById('selectedTableInfo');
    const selectedTableName = document.getElementById('selectedTableName');
    const cartSummary = document.getElementById('cartSummary');

    if (selectedTableInfo) selectedTableInfo.style.display = 'flex';
    if (selectedTableName) selectedTableName.textContent = tableName;
    if (cartSummary) cartSummary.style.display = 'block';

    renderCart();
}

// Thêm vào giỏ
function addToCart(foodId) {
    if (!selectedTable) {
        alert('Vui lòng chọn bàn trước khi thêm món!');
        return;
    }

    const item = menuItemsData.find(i => i.id_food == foodId);
    if (!item) return;

    const existingItem = cart.find(c => c.id == foodId); //Tìm kiếm trong mảng cart , có tồn tại id food đó chưa

    if (existingItem) {
        existingItem.quantity++;
    } else {
        cart.push({
            id: item.id_food,
            name: item.name,
            price: item.price,
            quantity: 1
        });
    }

    renderCart();
}

// Tăng số lượng
function increaseQuantity(foodId) {
    const item = cart.find(c => c.id == foodId);
    if (item) {
        item.quantity++;
        renderCart();
    }
}

// Giảm số lượng
function decreaseQuantity(foodId) {
    const item = cart.find(c => c.id == foodId);
    if (item) {
        if (item.quantity > 1) {
            item.quantity--;
        } else {
            removeFromCart(foodId);
        }
        renderCart();
    }
}

// Cập nhật số lượng khi nhập trực tiếp
function updateQuantity(foodId, value) {
    const item = cart.find(c => c.id == foodId);
    if (!item) return;

    // Parse số, mặc định 1 nếu không hợp lệ
    let qty = parseInt(value, 10);
    if (isNaN(qty) || qty < 1) {
        qty = 1;
    }

    item.quantity = qty;
    renderCart();
}

// Xóa khỏi giỏ
function removeFromCart(foodId) {
    cart = cart.filter(c => c.id != foodId);
    renderCart();
}

// Render giỏ hàng
function renderCart() {
    const cartItems = document.getElementById('cartItems');
    const cartCount = document.getElementById('cartCount');
    const cartTotalAmount = document.getElementById('cartTotalAmount');

    if (!cartItems || !cartCount || !cartTotalAmount) return;

    // Update badge
    // const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
    const totalItems = cart.length;
    cartCount.innerHTML = `<span class="cart-badge">${totalItems}</span>`;

    if (cart.length === 0) {
        cartItems.innerHTML = `
            <div class="empty-cart">
                <i class="fas fa-shopping-basket"></i>
                <p>Chọn bàn và thêm món vào order</p>
            </div>
        `;
        cartTotalAmount.textContent = '0 ₫';
        return;
    }

    let html = '';
    let total = 0;

    cart.forEach(item => {
        const itemTotal = item.price * item.quantity;
        total += itemTotal;

        html += `
            <div class="cart-item">
                <div class="cart-item-info">
                    <div class="cart-item-name">${item.name}</div>
                    <div class="cart-item-price">${formatPrice(item.price)}</div>
                </div>
                <div class="cart-item-quantity mr-1">
                    <button class="quantity-btn" onclick="decreaseQuantity(${item.id})">-</button>
                    <input type="number" class="quantity-input" min="1" value="${item.quantity}"
                           onchange="updateQuantity(${item.id}, this.value)"
                           onclick="this.select()" />
                    <button class="quantity-btn" onclick="increaseQuantity(${item.id})">+</button>
                </div>
                <div class="cart-item-total">${formatPrice(itemTotal)}</div>
                <div class="cart-remove" onclick="removeFromCart(${item.id})">
                    <i class="fas fa-trash"></i>
                </div>
            </div>
        `;
    });

    cartItems.innerHTML = html;
    cartTotalAmount.textContent = formatPrice(total);
}

// Mở modal xác nhận
function openConfirmModal() {
    if (!selectedTable) {
        alert('Vui lòng chọn bàn!');
        return;
    }

    if (cart.length === 0) {
        alert('Vui lòng chọn món!');
        return;
    }

    const confirmTableName = document.getElementById('confirmTableName');
    const confirmTotal = document.getElementById('confirmTotal');
    const confirmModal = document.getElementById('confirmModal');

    if (confirmTableName) confirmTableName.textContent = selectedTable.name;
    if (confirmTotal) confirmTotal.textContent = document.getElementById('cartTotalAmount').textContent;

    if (confirmModal) {
        const modal = new bootstrap.Modal(confirmModal);
        modal.show();
    }
}

// Submit order
function submitOrder() {
    const note = document.getElementById('orderNote');

    //tao mảng items để lưu chi tiết món ăn vào 
    const items = cart.map(item => ({
        id: item.id,
        quantity: item.quantity,
    }));

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ||
                      document.querySelector('input[name="_token"]')?.value;
        
    

    fetch(document.getElementById('submitOrderBtn').dataset.route, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({
            table_id: selectedTable.id,
            items: items,
            note: note.value
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {

            toastr.success('Tạo đơn hàng thành công!')

            setTimeout(()=>{
                window.location.reload();
            } , 1000)

            
        } else {
            toastr.error(data.message || 'Có lỗi xảy ra!')
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Có lỗi xảy ra!');
    });
}

// Format tiền
function formatPrice(price) {
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(price);
}
