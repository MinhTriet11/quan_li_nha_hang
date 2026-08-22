@extends('layouts.admin_home')

@section('title', 'Tạo phiếu chuyển kho')

@section('content')

    <div id="content">
        @include('admin.partials.topbar')

        <div class="container-fluid">

            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">Tạo phiếu chuyển kho mới</h1>
                <a href="{{ route('admin.transfer.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Quay lại
                </a>
            </div>

            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Thông tin phiếu chuyển</h6>
                </div>
                <div class="card-body">
                    <form id="formCreateTransfer">
                        @csrf
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="from_branch">Chi nhánh nguồn <span class="text-danger">*</span></label>
                                    <select class="form-control" id="from_branch" name="from_branch" required>
                                        <option value="">----- Chọn chi nhánh nguồn -----</option>
                                        @foreach ($branches as $branch)
                                            <option value="{{ $branch->id_br }}">{{ $branch->name }}</option>
                                        @endforeach
                                    </select>
                                    <span class="form-message text-danger small"></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="to_branch">Chi nhánh đích <span class="text-danger">*</span></label>
                                    <select class="form-control" id="to_branch" name="to_branch" required disabled>
                                        <option value="">----- Chọn chi nhánh nguồn trước -----</option>
                                    </select>
                                    <span class="form-message text-danger small"></span>
                                </div>
                            </div>
                        </div>
                        <div id="branchNotice" class="alert alert-info py-2 mb-3" role="alert">
                            <i class="fas fa-info-circle"></i> Vui lòng chọn chi nhánh nguồn trước.
                        </div>

                        <h6 class="font-weight-bold text-primary mt-4 mb-3">Chi tiết nguyên liệu chuyển</h6>

                        <div class="table-responsive">
                            <table class="table table-bordered" id="detailsTable">
                                <thead class="thead-light">
                                    <tr>
                                        <th style="width: 38%;">Nguyên liệu</th>
                                        <th style="width: 20%;">Tồn kho CN nguồn</th>
                                        <th style="width: 20%;">Số lượng chuyển</th>
                                        <th style="width: 10%;">Đơn vị</th>
                                        <th style="width: 12%;" class="text-center">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody id="detailsBody">
                                    <tr data-row="0">
                                        <td>
                                            <select name="details[0][id_ingre]" class="form-control ingre-select" required
                                                disabled>
                                                <option value="">----- Chọn nguyên liệu -----</option>

                                            </select>
                                        </td>
                                        <td>
                                            <span class="text-muted small stock-cell">—</span>
                                        </td>
                                        <td><input type="number" step="0.01" min="0.01" name="details[0][quantity]"
                                                class="form-control qty-input" placeholder="SL" required disabled></td>
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
                                <i class="fas fa-save"></i> Lưu phiếu chuyển (chờ duyệt)
                            </button>
                            <a href="{{ route('admin.transfer.index') }}" class="btn btn-secondary">Hủy</a>
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
            const branchFromSelect = document.getElementById('from_branch');
            const branchToSelect = document.getElementById('to_branch');
            const btnAddRow = document.getElementById('btnAddRow');
            const branchNotice = document.getElementById('branchNotice');
            const ingredients = @json($ingredients);

            let currentBranchFrom = [];

            // Tồn kho: stocks[ingreId][branchId] = quantity
            const stocks = @json(
                $stocks->groupBy('id_ingre')->map(function ($rows) {
                        return $rows->mapWithKeys(fn($r) => [$r->id_br => (float) $r->quantity])->all();
                    })->all());

            /**
             * Trả về chuỗi <option> chỉ gồm các nguyên liệu có tồn kho > 0 tại fromBranch.
             * Nếu chưa chọn fromBranch thì trả về tất cả (giống mặc định ban đầu).
             */
            function buildIngredientOptions(fromBranch) {
                let html = '<option value="">----- Chọn nguyên liệu -----</option>';
                ingredients.forEach((ingre) => {
                    const id = ingre.id_ingre;
                    const name = ingre.name;
                    const unit = ingre.unit;
                    const stockByBranch = stocks[id] || {};
                    const totalInFromBranch = fromBranch ? (parseFloat(stockByBranch[fromBranch]) || 0) :
                        null;
                    const hasStock = fromBranch ? totalInFromBranch > 0 :
                        true; //neu khong co totalInform thi false , chuyen qua cai khac
                    if (!hasStock) return;
                    const stockHint = (fromBranch && totalInFromBranch !== null) ? ' (tồn: ' +
                        totalInFromBranch + ')' : '';
                    html += '<option value="' + id + '" data-unit="' + unit + '">' + name + stockHint +
                        '</option>';
                })
                return html;
            }

            function setRowsEnabled(enabled) {
                tbody.querySelectorAll('tr').forEach(function(tr) {
                    const sel = tr.querySelector('.ingre-select');
                    const qty = tr.querySelector('.qty-input');
                    if (sel) sel.disabled = !enabled;
                    if (qty) qty.disabled = !enabled;
                });
                branchToSelect.disabled = !enabled;
                btnAddRow.disabled = !enabled;
            }

            //cap nhat danh sach nguyen lieu trong tat ca cac dong
            function refreshIngredientOptions() {
                const fromBranch = branchFromSelect.value;
                tbody.querySelectorAll('tr').forEach(function(tr) {
                    const sel = tr.querySelector('.ingre-select');
                    if (!sel) return;
                    const prevValue = sel.value;
                    sel.innerHTML = buildIngredientOptions(fromBranch);
                    // Nếu nguyên liệu đã chọn trước đó không còn tồn kho ở fromBranch mới -> reset
                    if (prevValue) {
                        const stillHas = Array.from(sel.options).some(o => o.value === prevValue);
                        sel.value = stillHas ? prevValue : '';
                    }
                });
                refreshStockDisplay();
            }

            //cap nhat ton kho nguyen lieu cho tung dong
            function refreshStockDisplay() {
                const fromBranch = branchFromSelect.value;
                tbody.querySelectorAll('tr').forEach(function(tr) {
                    const sel = tr.querySelector('.ingre-select');
                    const stockCell = tr.querySelector('.stock-cell');
                    const ingreId = sel ? sel.value : '';
                    if (ingreId && fromBranch && stocks[ingreId] && stocks[ingreId][fromBranch] !==
                        undefined) {
                        stockCell.textContent = stocks[ingreId][fromBranch];
                        stockCell.classList.remove('text-danger');
                        stockCell.classList.add('text-success', 'font-weight-bold');
                    } else if (ingreId && fromBranch) {
                        stockCell.textContent = '0';
                        stockCell.classList.add('text-danger', 'font-weight-bold');
                        stockCell.classList.remove('text-success');
                    } else {
                        stockCell.textContent = '—';
                        stockCell.classList.remove('text-danger', 'text-success', 'font-weight-bold');
                    }
                    // đơn vị
                    const unitCell = tr.querySelector('.unit-cell');
                    const opt = sel ? sel.options[sel.selectedIndex] : null; //selectedIndex vi tri da chon
                    unitCell.textContent = opt && opt.dataset.unit ? opt.dataset.unit : '—';
                });
            }

            function updateRemoveButtons() {
                const rows = tbody.querySelectorAll('tr');
                rows.forEach(function(row) {
                    const btn = row.querySelector('.btn-remove-row');
                    if (btn) btn.disabled = rows.length <= 1;
                });
            }


            document.getElementById('btnAddRow').addEventListener('click', function() {
                const fromBranch = document.getElementById('from_branch').value;
                const tr = document.createElement('tr');
                tr.dataset.row = rowIndex;
                tr.innerHTML = `
            <td>
                <select name="details[${rowIndex}][id_ingre]" class="form-control ingre-select" required>
                    ${buildIngredientOptions(fromBranch)}
                </select>
            </td>
            <td><span class="text-muted small stock-cell">—</span></td>
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
                    refreshStockDisplay(); //cap nhat lai ton kho
                }
            });

            //chon chi nhanh nguon 
            branchFromSelect.addEventListener('change', function() {
                const branchId = this.value;

                refreshIngredientOptions();

                if (!branchId) {
                    branchNotice.classList.remove('d-none'); //hien thong bao
                    setRowsEnabled(false);
                    return;
                }

                branchNotice.classList.add('d-none');
                fetch('/admin/transfer-receipts/branch/' + branchId, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    })
                    .then(function(res) {
                        return res.json()
                    })
                    .then(function(data) {
                        if (data.success) {
                            currentBranchFrom = data.data || [];
                            setRowsEnabled(true);
                            let html = '<option value="">----- Chọn chi nhánh đích -----</option>';
                            currentBranchFrom.forEach(function(br) {
                                html += '<option value="' + br.id_br + '">' + br.name +
                                    '</option>';
                            });
                            branchToSelect.innerHTML = html;
                        } else {
                            toastr.error('Không lấy được chi nhánh.');
                        }

                    })
            })

            document.getElementById('formCreateTransfer').addEventListener('submit', function(e) {
                e.preventDefault();
                const form = e.target;
                const formData = new FormData(form);
                const payload = {};
                payload.from_branch = formData.get('from_branch');
                payload.to_branch = formData.get('to_branch');
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

                if (!payload.from_branch) {
                    toastr.error('Vui lòng chọn chi nhánh nguồn');
                    return;
                }
                if (!payload.to_branch) {
                    toastr.error('Vui lòng chọn chi nhánh đích');
                    return;
                }
                if (payload.from_branch === payload.to_branch) {
                    toastr.error('Chi nhánh đích phải khác chi nhánh nguồn');
                    return;
                }
                if (payload.details.length === 0) {
                    toastr.error('Vui lòng thêm ít nhất 1 dòng nguyên liệu');
                    return;
                }

                // Kiểm tra trùng nguyên liệu
                // const ids = payload.details.map(d => d.id_ingre);
                // if (new Set(ids).size !== ids.length) {
                //     alert('Có nguyên liệu bị trùng trong danh sách, vui lòng kiểm tra lại');
                //     return;
                // }

                //cong don nguyen lieu trung
                const totals = {};
                const ingredientName = {};

                for (const d of payload.details) {
                    const id = d.id_ingre;
                    const quantity = parseFloat(d.quantity) || 0;

                    totals[id] = (totals[id] || 0) + quantity;

                    ingredientName[id] = ingredients.find(ing => String(ing.id_ingre) === String(d
                        .id_ingre))
                }


                // Kiểm tra không vượt quá tồn kho chi nhánh nguồn
                for (const d of payload.details) {
                    const stock = stocks[d.id_ingre] && stocks[d.id_ingre][payload.from_branch];
                    if (stock === undefined || parseFloat(stock) <= 0) {
                        toastr.error(
                            'Nguyên liệu đã chọn không còn tồn kho ở chi nhánh nguồn, vui lòng chọn lại'
                            )
                        return;
                    }
                    if (parseFloat(totals[d.id_ingre]) > parseFloat(stock)) {
                        toastr.error('Số lượng chuyển ' + ingredientName[d.id_ingre].name +
                            ' vượt quá tồn kho (' + stock + ')')
                        return;
                    }
                }

                fetch('{{ route('admin.transfer.store') }}', {
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
                            toastr.success(data.message, 'Thành công');
                            setTimeout(() => {
                                window.location.href = data.redirect;
                            }, 1000);
                        } else {
                            alert(data.message || 'Có lỗi xảy ra');
                            toastr.error(data.message || 'Có lỗi xảy ra');
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
