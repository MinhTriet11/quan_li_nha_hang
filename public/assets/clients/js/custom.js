// Category click handler
const categoryItems = document.querySelectorAll('.category-item');
const foodList = document.getElementById('food-list');
const paginationContainer = document.getElementById('pagination-container');
let currentCategoryId = null;

// Tải category đầu tiên khi load trang
const firstCategory = document.querySelector('.category-item.active') || categoryItems[0];
if (firstCategory) {
    const firstCategoryId = firstCategory.getAttribute('data-id');
    renderFoodByCategory(firstCategoryId);
}

categoryItems.forEach(item => {
    item.addEventListener('click', function () {
        // Xoa active
        categoryItems.forEach(cat => cat.classList.remove('active'));

        this.classList.add('active');

        const categoryId = this.getAttribute('data-id');

        renderFoodByCategory(categoryId);
    });
});


//render food theo cate
function renderFoodByCategory(categoryId, page = 1) {
    currentCategoryId = categoryId;

    // Show loading
    foodList.innerHTML = '<div class="col-12 text-center"><p>Đang tải...</p></div>';

    fetch(`/menu-items/${categoryId}?page=${page}`)
        .then(response => response.json())
        .then(response => {
            let html = '';

            if (!response.success || response.data.length === 0) {
                html = '<div class="col-12 text-center"><p>Không có món ăn trong danh mục này</p></div>';
            } else {
                response.data.forEach(food => {
                    const imageUrl = food.image? `/storage/menu_items/${food.image}`: '/assets/clients/image/food1.jpeg';
                    html += `
                        <div class="col-3 food-item" data-category-id="${food.id_cate}">
                            <div class="detail-item">
                                <img class="img-item" src="${imageUrl}" alt="${food.name}" onerror="this.onerror=null;this.src='/assets/clients/image/food1.jpeg';">
                                <div class="content">
                                    <h4 class="title-item">${food.name}</h4>
                                    <p class="price-item mb-0">${new Intl.NumberFormat('vi-VN').format(food.price)}đ</p>
                                </div>
                            </div>
                        </div>`;
                });
            }

            foodList.innerHTML = html;

            // Render pagination
            if (response.pagination && response.pagination.last_page > 1) {
                renderPagination(response.pagination, categoryId);
            } else if (paginationContainer) {
                paginationContainer.innerHTML = '';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            foodList.innerHTML = '<div class="col-12 text-center"><p>Có lỗi xảy ra</p></div>';
        });
}

//render page
function renderPagination(pagination, categoryId) {
    if (!paginationContainer) return;

    const { current_page, last_page } = pagination;
    let html = `
            <div class="pagination-wrapper">
            <nav aria-label="Page navigation example" class="pagination">
                <ul class="pagination justify-content-center">
                    <li class="page-item ${current_page === 1 ? 'disabled' : ''}">
                        <a class="page-link" href="#" data-page="${current_page - 1}">&laquo;</a>
                    </li>`;

    for (let i = 1; i <= last_page; i++) {
        html += `
                <li class="page-item ${i === current_page ? 'active' : ''}">
                    <a class="page-link" href="#" data-page="${i}">${i}</a>
                </li>`;
    }

    html += `
                    <li class="page-item ${current_page === last_page ? 'disabled' : ''}">
                        <a class="page-link" href="#" data-page="${current_page + 1}">&raquo;</a>
                    </li>
                </ul>
            </nav>
            </div>`;

    paginationContainer.innerHTML = html;

    // bat su kien khi click pagination
    paginationContainer.querySelectorAll('.page-link').forEach(link => {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            const page = parseInt(this.getAttribute('data-page'));
            if (page > 0 && page <= last_page && page !== current_page) {
                renderFoodByCategory(categoryId, page);
            }
        });
    });
}


