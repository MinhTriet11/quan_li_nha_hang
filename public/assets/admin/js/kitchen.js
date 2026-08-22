// Kitchen View JavaScript - AJAX update order status only (pagination đã chuyển sang Laravel)

const branchSelect = document.getElementById('branchSelect');

if (branchSelect) {
    branchSelect.onchange = function () {
        this.form.submit();
    };
}



function updateOrderStatus(orderId, newStatus) {
    fetch(`/admin/orders/${orderId}/status`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        },
        body: JSON.stringify({ status: newStatus })
    })
    .then(response => response.json().then(data => ({ status: response.status, body: data })))
    .then(({ status, body }) => {
        if (status >= 200 && status < 300 && body.success) {
            // Reload trang để cập nhật danh sách theo trạng thái mới
            window.location.reload();
            return;
        }

        // Thiếu nguyên liệu: hiển thị danh sách chi tiết
        if (body && Array.isArray(body.shortages) && body.shortages.length > 0) {
            showShortageToastr(body.shortages, body.message);
            return;
        }

        // Lỗi chung
        if (typeof toastr !== 'undefined') {
            toastr.error(body.message || 'Có lỗi xảy ra');
        } else {
            alert(body.message || 'Có lỗi xảy ra');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (typeof toastr !== 'undefined') {
            toastr.error('Có lỗi xảy ra');
        } else {
            alert('Có lỗi xảy ra');
        }
    });
}

function showShortageToastr(shortages, headerMessage) {
    const list = shortages.map(s => {
        const name = s.name || `Nguyên liệu #${s.id_ingre}`;
        const unit = s.unit ? ` ${s.unit}` : '';
        return `• ${name}: cần ${s.required}${unit}, còn ${s.available}${unit}, thiếu ${s.shortage}${unit}`;
    }).join('<br>');

    const html = `<div><strong>${headerMessage || 'Không thể chế biến'}</strong><br>${list}</div>`;

    if (typeof toastr !== 'undefined') {
        toastr.error(html, 'Thiếu nguyên liệu', {
            timeOut: 0,
            extendedTimeOut: 0,
            closeButton: true,
            enableHtml: true,
        });
    } else {
        alert(`${headerMessage || 'Không đủ nguyên liệu'}\n\n` +
              shortages.map(s =>
                  `- ${s.name}: cần ${s.required}${s.unit || ''}, còn ${s.available}${s.unit || ''}`
              ).join('\n'));
    }
}