@extends('layouts.admin_home')

@section('title', 'Tạo phiếu nhập kho')

@section('content')

    <div id="content">
        @include('admin.partials.topbar')

        <div class="container-fluid">

            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">Tạo phiếu nhập kho mới</h1>
                <a href="{{ route('admin.import.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Quay lại
                </a>
            </div>

            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Thông tin phiếu nhập</h6>
                </div>
                <div class="card-body">
                    <form id="formCreateImport">
                        @csrf
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="id_br">Chi nhánh <span class="text-danger">*</span></label>
                                    <select class="form-control" id="id_br" name="id_br" required>
                                        <option value="">----- Chọn chi nhánh -----</option>
                                        @foreach ($branches as $branch)
                                            <option value="{{ $branch->id_br }}">{{ $branch->name }}</option>
                                        @endforeach
                                    </select>
                                    <span class="form-message text-danger small"></span>
                                </div>
                            </div>
                        </div>

                        <h6 class="font-weight-bold text-primary mt-4 mb-3">Chi tiết nguyên liệu</h6>

                        <div class="table-responsive">
                            <table class="table table-bordered" id="detailsTable">
                                <thead class="thead-light">
                                    <tr>
                                        <th style="width: 35%;">Nguyên liệu</th>
                                        <th style="width: 25%;">Nhà cung cấp</th>
                                        <th style="width: 15%;">Số lượng</th>
                                        <th style="width: 15%;">Đơn giá (VNĐ)</th>
                                        <th style="width: 10%;" class="text-center">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody id="detailsBody">
                                    <tr data-row="0">
                                        <td>
                                            <select name="details[0][id_ingre]" class="form-control" required>
                                                <option value="">----- Chọn nguyên liệu -----</option>
                                                @foreach ($ingredients as $ingre)
                                                    <option value="{{ $ingre->id_ingre }}" data-unit="{{ $ingre->unit }}">
                                                        {{ $ingre->name }} ({{ $ingre->unit }})</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <select name="details[0][id_su]" class="form-control" required>
                                                <option value="">----- Chọn NCC -----</option>
                                                @foreach ($suppliers as $sup)
                                                    <option value="{{ $sup->id_su }}">{{ $sup->name }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td><input type="number" step="0.01" min="0.01" name="details[0][quantity]"
                                                class="form-control" placeholder="SL" required></td>
                                        <td><input type="number" step="0.01" min="0" name="details[0][price]"
                                                class="form-control" placeholder="Giá" required></td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-danger btn-remove-row" disabled>
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <button type="button" id="btnAddRow" class="btn btn-sm btn-success mt-2">
                            <i class="fas fa-plus"></i> Thêm dòng
                        </button>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Lưu phiếu nhập
                            </button>
                            <a href="{{ route('admin.import.index') }}" class="btn btn-secondary">Hủy</a>
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
            const ingredientsOptions =
                `@foreach ($ingredients as $ingre)<option value="{{ $ingre->id_ingre }}" data-unit="{{ $ingre->unit }}">{{ $ingre->name }} ({{ $ingre->unit }})</option>@endforeach`;
            const suppliersOptions =
                `@foreach ($suppliers as $sup)<option value="{{ $sup->id_su }}">{{ $sup->name }}</option>@endforeach`;

            function updateRemoveButtons() {
                const rows = tbody.querySelectorAll('tr');
                rows.forEach((row, idx) => {
                    const btn = row.querySelector('.btn-remove-row');
                    if (btn) btn.disabled = rows.length <= 1;
                });
            }

            document.getElementById('btnAddRow').addEventListener('click', function() {
                const tr = document.createElement('tr');
                tr.dataset.row = rowIndex;
                tr.innerHTML = `
            <td>
                <select name="details[${rowIndex}][id_ingre]" class="form-control" required>
                    <option value="">----- Chọn nguyên liệu -----</option>
                    ${ingredientsOptions}
                </select>
            </td>
            <td>
                <select name="details[${rowIndex}][id_su]" class="form-control" required>
                    <option value="">----- Chọn NCC -----</option>
                    ${suppliersOptions}
                </select>
            </td>
            <td><input type="number" step="0.01" min="0.01" name="details[${rowIndex}][quantity]" class="form-control" placeholder="SL" required></td>
            <td><input type="number" step="0.01" min="0" name="details[${rowIndex}][price]" class="form-control" placeholder="Giá" required></td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-danger btn-remove-row">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
                tbody.appendChild(tr);
                rowIndex++;
                updateRemoveButtons();
            });

            tbody.addEventListener('click', function(e) {

                if (e.target.closest('.btn-remove-row')) {
                    const tr = e.target.closest('tr');
                    tr.remove();
                    updateRemoveButtons();
                }
            });

            document.getElementById('formCreateImport').addEventListener('submit', function(e) {
                e.preventDefault();
                const form = e.target;

                const formData = new FormData(form); //lay du lieu form

                const payload = {};
                payload.id_br = formData.get('id_br');
                payload.details = [];
                for (const [key, value] of formData.entries()) {
                    const match = key.match(/^details\[(\d+)\]\[(\w+)\]$/);
                    // [
                    //     "details[0][ingredient_id]",
                    //     "0",
                    //     "ingredient_id"
                    // ]
                    if (match) {
                        const idx = parseInt(match[1]);
                        if (!payload.details[idx]) payload.details[idx] = {};
                        payload.details[idx][match[2]] = value; //payload.details[idx] la obj , obj[key] = value or obj.key = value
                    }
                }
                payload.details = payload.details.filter(Boolean);

                if (payload.details.length === 0) {
                    toastr.error('Vui lòng thêm ít nhất 1 dòng nguyên liệu', )
                    return;
                }

                fetch('{{ route('admin.import.store') }}', {
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
                        toastr.error('Có lỗi xảy ra')
                    });
            });
        });
    </script>
@endsection
