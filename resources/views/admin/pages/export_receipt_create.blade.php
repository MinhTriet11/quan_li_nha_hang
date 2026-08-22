@extends('layouts.admin_home')

@section('title', 'Tạo phiếu xuất kho')

@section('content')

            <div id="content">
                @include('admin.partials.topbar')

                <div class="container-fluid">

                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Tạo phiếu xuất kho mới</h1>
                        <a href="{{ route('admin.export.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Quay lại
                        </a>
                    </div>

                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Thông tin phiếu xuất</h6>
                        </div>
                        <div class="card-body">
                            <form id="formCreateExport">
                                @csrf
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="id_br">Chi nhánh <span class="text-danger">*</span></label>
                                            <select class="form-control" id="id_br" name="id_br" required>
                                                <option value="">----- Chọn chi nhánh -----</option>
                                                @foreach($branches as $branch)
                                                    <option value="{{ $branch->id_br }}">{{ $branch->name }}</option>
                                                @endforeach
                                            </select>
                                            <span class="form-message text-danger small"></span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="reason">Lý do xuất <span class="text-danger">*</span></label>
                                            <select class="form-control" id="reason" name="reason" required>
                                                <option value="">----- Chọn lý do -----</option>
                                                <option value="waste">Nguyên liệu hư</option>
                                                <option value="broken">Nguyên liệu đổ / bể</option>
                                                <option value="adjustment">Điều chỉnh tồn kho</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="note">Ghi chú</label>
                                    <textarea class="form-control" id="note" name="note" rows="2" placeholder="Mô tả lý do chi tiết, vị trí, người phát hiện..."></textarea>
                                </div>

                                <div id="branchNotice" class="alert alert-info py-2 mb-3" role="alert">
                                    <i class="fas fa-info-circle"></i> Vui lòng chọn chi nhánh trước.
                                </div>

                                <h6 class="font-weight-bold text-primary mt-4 mb-3">Chi tiết nguyên liệu xuất</h6>

                                <div class="table-responsive">
                                    <table class="table table-bordered" id="detailsTable">
                                        <thead class="thead-light">
                                            <tr>
                                                <th style="width: 40%;">Nguyên liệu</th>
                                                <th style="width: 20%;">Tồn kho chi nhánh</th>
                                                <th style="width: 20%;">Số lượng xuất</th>
                                                <th style="width: 10%;">Đơn vị</th>
                                                <th style="width: 10%;" class="text-center">Thao tác</th>
                                            </tr>
                                        </thead>
                                        <tbody id="detailsBody">
                                            <tr data-row="0">
                                                <td>
                                                    <select name="details[0][id_ingre]" class="form-control ingre-select" required disabled>
                                                        <option value="">----- Chọn chi nhánh trước -----</option>
                                                    </select>
                                                </td>
                                                <td>
                                                    <span class="text-muted small stock-cell" data-row="0">—</span>
                                                </td>
                                                <td><input type="number" step="0.01" min="0.01" name="details[0][quantity]" class="form-control qty-input" placeholder="SL" required disabled></td>
                                                <td><span class="unit-cell text-muted">—</span></td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-sm btn-danger btn-remove-row" disabled>
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <button type="button" id="btnAddRow" class="btn btn-sm btn-success mt-2" disabled>
                                    <i class="fas fa-plus"></i> Thêm dòng
                                </button>

                                <div class="mt-4">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Lưu phiếu xuất (chờ duyệt)
                                    </button>
                                    <a href="{{ route('admin.export.index') }}" class="btn btn-secondary">Hủy</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

@endsection

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let rowIndex = 1;
    const tbody = document.getElementById('detailsBody');
    const btnAddRow = document.getElementById('btnAddRow');
    const branchSelect = document.getElementById('id_br');
    const branchNotice = document.getElementById('branchNotice');

    // Nguyên liệu hiện tại của chi nhánh đang chọn
    let currentIngredients = [];

    // Tạo option HTML cho một danh sách nguyên liệu
    function buildIngredientOptions(list) {
        let html = '<option value="">----- Chọn nguyên liệu -----</option>';
        list.forEach(function (ingre) {
            html += '<option value="' + ingre.id_ingre + '" data-unit="' + ingre.unit + '" data-stock="' + ingre.quantity + '">' + ingre.name + '</option>';
        });
        return html;
    }

    // Bật / tắt các ô nhập theo trạng thái chọn chi nhánh
    function setRowsEnabled(enabled) {
        tbody.querySelectorAll('tr').forEach(function (tr) {
            const sel = tr.querySelector('.ingre-select');
            const qty = tr.querySelector('.qty-input');
            if (sel) sel.disabled = !enabled;
            if (qty) qty.disabled = !enabled;
        });
        btnAddRow.disabled = !enabled;
    }

    // Reset các dòng về trạng thái ban đầu (1 dòng rỗng)
    function resetRows() {
        tbody.innerHTML = `
            <tr data-row="0">
                <td>
                    <select name="details[0][id_ingre]" class="form-control ingre-select" required>
                        ${buildIngredientOptions(currentIngredients)}
                    </select>
                </td>
                <td><span class="text-muted small stock-cell" data-row="0">—</span></td>
                <td><input type="number" step="0.01" min="0.01" name="details[0][quantity]" class="form-control qty-input" placeholder="SL" required></td>
                <td><span class="unit-cell text-muted">—</span></td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-danger btn-remove-row" disabled>
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;
        rowIndex = 1;
    }

    // Cập nhật hiển thị tồn kho và đơn vị cho tất cả các dòng đã chọn 
    function refreshStockDisplay() {
        tbody.querySelectorAll('tr').forEach(function (tr) {
            const selectedIngre = tr.querySelector('.ingre-select');
            const stockCell = tr.querySelector('.stock-cell');
            const unitCell = tr.querySelector('.unit-cell');
            const ingreId = selectedIngre ? selectedIngre.value : '';
            const ingre = currentIngredients.find(function (i) { return String(i.id_ingre) === String(ingreId); }); //tim nguyen lieu trong currentIngre
            if (ingre) {
                stockCell.textContent = ingre.quantity;
                unitCell.textContent = ingre.unit || '—';
            } else {
                stockCell.textContent = '—';
                unitCell.textContent = '—';
            }
        });
    }

    function updateRemoveButtons() {
        const rows = tbody.querySelectorAll('tr');
        rows.forEach(function (row) {
            const btn = row.querySelector('.btn-remove-row');
            if (btn) btn.disabled = rows.length <= 1;
        });
    }

    btnAddRow.addEventListener('click', function() {
        if (branchSelect.value === '') return;
        const tr = document.createElement('tr');
        tr.dataset.row = rowIndex;
        tr.innerHTML = `
            <td>
                <select name="details[${rowIndex}][id_ingre]" class="form-control ingre-select" required>
                    ${buildIngredientOptions(currentIngredients)}
                </select>
            </td>
            <td><span class="text-muted small stock-cell" data-row="${rowIndex}">—</span></td>
            <td><input type="number" step="0.01" min="0.01" name="details[${rowIndex}][quantity]" class="form-control qty-input" placeholder="SL" required></td>
            <td><span class="unit-cell text-muted">—</span></td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-danger btn-remove-row"><i class="fas fa-trash"></i></button>
            </td>
        `;
        tbody.appendChild(tr);
        rowIndex++;
        updateRemoveButtons();
    });

    tbody.addEventListener('click', function(e) {
        if (e.target.closest('.btn-remove-row')) {
            e.target.closest('tr').remove();
            updateRemoveButtons();
        }
    });

    tbody.addEventListener('change', function(e) {
        if (e.target.classList.contains('ingre-select')) {
            refreshStockDisplay();
        }
    });

    // Load nguyen lieu khi chon chi nhanh
    branchSelect.addEventListener('change', function() {
        const branchId = this.value;

        if (!branchId) {
            currentIngredients = [];
            branchNotice.classList.remove('d-none'); //hien thong bao
            resetRows();
            setRowsEnabled(false);
            return;
        }

        branchNotice.classList.add('d-none'); //an thong bao
        fetch('/admin/export-receipts/branch-ingredients/' + branchId, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(function (res) { return res.json(); })
        .then(function (data) {
            if (data.success) {
                currentIngredients = data.data || [];
                resetRows();
                setRowsEnabled(true);
                refreshStockDisplay();
                updateRemoveButtons();
            } else {
                toastr.error('Không lấy được nguyên liệu của chi nhánh.');
            }
        })
        .catch(function (err) {
            console.error(err);
            toastr.error('Lỗi khi tải nguyên liệu chi nhánh.');
        });
    });

    document.getElementById('formCreateExport').addEventListener('submit', function(e) {
        e.preventDefault();
        const form = e.target;

        if (!branchSelect.value) { alert('Vui lòng chọn chi nhánh'); return; }

        const formData = new FormData(form);

        const payload = {};
        payload.id_br = formData.get('id_br');
        payload.reason = formData.get('reason');
        payload.note = formData.get('note');
        payload.details = [];
        for (const [key, value] of formData.entries()) {
            const match = key.match(/^details\[(\d+)\]\[(\w+)\]$/);
            if (match) {
                const idx = parseInt(match[1]);
                if (!payload.details[idx]) payload.details[idx] = {};
                payload.details[idx][match[2]] = value;
            }
        }
        payload.details = payload.details.filter(Boolean);

        if (!payload.reason) { alert('Vui lòng chọn lý do xuất'); return; }
        if (payload.details.length === 0) { alert('Vui lòng thêm ít nhất 1 dòng nguyên liệu'); return; }

        fetch('{{ route("admin.export.store") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
        })
        .then(async res => {
            const data = await res.json();
            if (res.ok && data.success) {
                toastr.success(data.message, 'Thành công')
                setTimeout(() => {
                    window.location.href = data.redirect;
                }, 1000);

            } else {
                toastr.error(data.message, 'Có lỗi xảy ra')
            }
        })
        .catch(err => {
            console.error(err);
            alert('Có lỗi xảy ra');
        });
    });
});
</script>
@endsection
