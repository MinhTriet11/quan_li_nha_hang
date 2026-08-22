@extends('layouts.admin_home')

@section('title', 'Chi tiết phiếu chuyển kho #CK' . $transfer->id_tran)

@section('content')

    <div id="content">
        @include('admin.partials.topbar')

        <div class="container-fluid">

            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">Chi tiết phiếu chuyển #CK{{ $transfer->id_tran }}</h1>
                <a href="{{ route('admin.transfer.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Quay lại
                </a>
            </div>

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                </div>
            @endif

            {{-- Thông tin header --}}
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Thông tin phiếu chuyển</h6>
                    <div>
                        @if ((int) $transfer->status === 0)
                            <span class="badge badge-warning badge-lg">Chờ duyệt</span>
                        @elseif((int) $transfer->status === 1)
                            <span class="badge badge-success badge-lg">Đã duyệt</span>
                        @else
                            <span class="badge badge-warning badge-lg">Từ chối</span>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <small class="text-muted">Mã phiếu</small>
                            <p class="font-weight-bold">#CK{{ $transfer->id_tran }}</p>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted">Chi nhánh nguồn</small>
                            <p class="font-weight-bold text-danger">
                                <i class="fas fa-arrow-circle-right mr-1"></i>
                                {{ $transfer->fromBranch->name ?? '—' }}
                            </p>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted">Chi nhánh đích</small>
                            <p class="font-weight-bold text-success">
                                <i class="fas fa-arrow-circle-left mr-1"></i>
                                {{ $transfer->toBranch->name ?? '—' }}
                            </p>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted">Người tạo</small>
                            <p class="font-weight-bold">{{ $transfer->user->name ?? '—' }}</p>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted">Ngày tạo</small>
                            <p class="font-weight-bold">
                                {{ $transfer->created_at ? $transfer->created_at->format('d/m/Y H:i') : '—' }}</p>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted">Ngày cập nhật</small>
                            <p class="font-weight-bold">
                                {{ $transfer->updated_at ? $transfer->updated_at->format('d/m/Y H:i') : '—' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Bảng chi tiết --}}
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Chi tiết nguyên liệu chuyển</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>STT</th>
                                    <th>Nguyên liệu</th>
                                    <th>Đơn vị</th>
                                    <th class="text-center">Tồn kho CN nguồn</th>
                                    <th class="text-center" style="min-width: 160px;">Số lượng chuyển</th>
                                    @if ((int) $transfer->status === 0)
                                        <th class="text-center" style="width: 80px;">Thao tác</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody id="detailsBody">
                                @forelse($transfer->details as $key => $detail)
                                    @php
                                        $stockQty = isset($stocks[$detail->id_ingre])
                                            ? (float) $stocks[$detail->id_ingre]->quantity
                                            : 0;
                                        $isEditable = (int) $transfer->status === 0;
                                    @endphp
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td>{{ $detail->ingredient->name ?? '—' }}</td>
                                        <td>{{ $detail->ingredient->unit ?? '—' }}</td>
                                        <td class="text-center">
                                            <span
                                                class="{{ $stockQty > 0 ? 'text-danger font-weight-bold' : 'text-danger font-weight-bold' }}">
                                                {{ rtrim(rtrim(number_format($stockQty, 2, '.', ''), '0'), '.') }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            @if ($isEditable)
                                                <input type="number" step="0.01" min="0.01"
                                                    max="{{ $stockQty > 0 ? $stockQty : '' }}"
                                                    value="{{ rtrim(rtrim(number_format((float) $detail->quantity, 2, '.', ''), '0'), '.') }}"
                                                    class="form-control form-control-sm text-center inp-qty mx-auto"
                                                    data-detail-id="{{ $detail->id_tran_detail }}"
                                                    style="max-width: 140px;">
                                            @else
                                                {{ rtrim(rtrim(number_format((float) $detail->quantity, 2, '.', ''), '0'), '.') }}
                                            @endif
                                        </td>
                                        @if ($isEditable)
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-danger btn-delete-detail"
                                                    data-detail-id="{{ $detail->id_tran_detail }}" title="Xóa dòng"
                                                    disabled>
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ (int) $transfer->status === 0 ? 6 : 5 }}"
                                            class="text-center text-muted">Phiếu chưa có dòng nguyên liệu</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Form thêm nguyên liệu (chỉ khi chờ duyệt) --}}
                    @if ((int) $transfer->status === 0)
                        <form id="addDetailForm" class="mt-4 border-top pt-3">
                            @csrf
                            <h6 class="font-weight-bold text-primary mb-3">Thêm nguyên liệu vào phiếu</h6>
                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label class="small">Nguyên liệu <span class="text-danger">*</span></label>
                                    <select name="id_ingre" id="addDetailIngredient" class="form-control" required>
                                        <option value="">----- Chọn nguyên liệu -----</option>
                                        @foreach ($ingredients as $ingre)
                                            @php
                                                $stockIngre = isset($stocks[$ingre->id_ingre])
                                                    ? (float) $stocks[$ingre->id_ingre]->quantity
                                                    : 0;
                                            @endphp
                                            @if ($stockIngre > 0)
                                                <option value="{{ $ingre->id_ingre }}" data-stock="{{ $stockIngre }}">
                                                    {{ $ingre->name }} ({{ $ingre->unit }}) — tồn:
                                                    {{ rtrim(rtrim(number_format($stockIngre, 2, '.', ''), '0'), '.') }}
                                                </option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-3">
                                    <label class="small">Số lượng <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" min="0.01" name="quantity"
                                        id="addDetailQuantity" class="form-control" placeholder="SL" required>
                                </div>
                                <div class="form-group col-md-3 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary btn-block" id="btnAddDetail">
                                        <i class="fas fa-plus"></i> Thêm
                                    </button>
                                </div>
                            </div>
                        </form>
                    @endif

                    <div class="mt-4 d-flex justify-content-between">
                        <a href="{{ route('admin.transfer.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Quay lại danh sách
                        </a>
                        <div>
                            @if ((int) $transfer->status === 0)
                                <form action="{{ route('admin.transfer.approve', $transfer->id_tran) }}" method="POST"
                                    class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-success"
                                        onclick="return confirm('Duyệt phiếu sẽ trừ tồn kho chi nhánh nguồn ({{ $transfer->fromBranch->name }}) và cộng cho chi nhánh đích ({{ $transfer->toBranch->name }}). Bạn có chắc chắn?')">
                                        <i class="fas fa-check"></i> Duyệt phiếu
                                    </button>
                                </form>

                                <form action="{{ route('admin.transfer.reject', $transfer->id_tran) }}" method="POST"
                                    class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-warning"
                                        onclick="return confirm('Bạn có chắc chắn muốn từ chối phiếu chuyển kho này?')">
                                        <i class="fas fa-times"></i> Từ chối
                                    </button>
                                </form>

                                <form action="{{ route('admin.transfer.destroy', $transfer->id_tran) }}" method="POST"
                                    class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger"
                                        onclick="return confirm('Bạn có chắc chắn muốn xóa phiếu chuyển này?')">
                                        <i class="fas fa-trash"></i> Xóa
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

@endsection

@section('js')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const idTran = {{ $transfer->id_tran }};
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
            const tbody = document.getElementById('detailsBody');

            // ===== Cập nhật số lượng từng dòng (inline) =====
            document.querySelectorAll('.inp-qty').forEach(function(input) {
                input.addEventListener('change', function(e) {
                    const newQty = e.target.value;
                    const detailId = e.target.dataset.detailId;

                    fetch(`/admin/transfers/${idTran}/details/${detailId}`, {
                            method: 'PUT',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({
                                quantity: newQty
                            }),
                        })
                        .then(async (res) => {
                            const data = await res.json();
                            if (!res.ok) throw data;
                            return data;
                        })
                        .then((data) => {
                            if (data.success) {
                                toastr.success(data.message, 'Thành công');
                            } else if (data.error) {
                                toastr.error(data.message, 'Lỗi');
                                setTimeout(() => window.location.reload(), 1000);
                            }
                        })
                        .catch((err) => {
                            toastr.error(err.message || 'Có lỗi xảy ra', 'Lỗi');
                            setTimeout(() => window.location.reload(), 1000);
                        });
                });
            });

            function updateRemoveButtons() {
                const rows = tbody.querySelectorAll('tr');
                rows.forEach(function(row) {
                    const btn = row.querySelector('.btn-delete-detail');
                    if (btn) btn.disabled = rows.length <= 1;
                });
            }

            updateRemoveButtons()

            // ===== Xóa 1 dòng nguyên liệu =====
            document.querySelectorAll('.btn-delete-detail').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    if (!confirm('Bạn có chắc chắn muốn xóa nguyên liệu này khỏi phiếu?')) return;
                    const detailId = btn.dataset.detailId;

                    fetch(`/admin/transfers/${idTran}/details/${detailId}`, {
                            method: 'DELETE',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json',
                            },
                        })
                        .then(async (res) => {
                            const data = await res.json();
                            if (!res.ok) throw data;
                            return data;
                        })
                        .then((data) => {
                            if (data.success) {
                                toastr.success(data.message, 'Thành công');
                                setTimeout(() => window.location.reload(), 800);
                            } else if (data.error) {
                                toastr.error(data.message, 'Lỗi');
                            }
                        })
                        .catch((err) => {
                            toastr.error(err.message || 'Có lỗi xảy ra', 'Lỗi');
                        });
                });
            });

            // ===== Khi chọn nguyên liệu -> tự đặt max cho ô số lượng = tồn kho =====
            const addIngredient = document.getElementById('addDetailIngredient');
            const addQuantity = document.getElementById('addDetailQuantity');
            // if (addIngredient && addQuantity) {
            //     addIngredient.addEventListener('change', function() {
            //         const opt = addIngredient.options[addIngredient.selectedIndex];
            //         const stock = opt && opt.dataset.stock ? parseFloat(opt.dataset.stock) : 0;
            //         addQuantity.max = stock > 0 ? stock : '';
            //         addQuantity.value = '';
            //     });
            // }

            // ===== Thêm dòng nguyên liệu vào phiếu =====
            const addDetailForm = document.getElementById('addDetailForm');
            if (addDetailForm) {
                addDetailForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const payload = {
                        id_ingre: addIngredient.value,
                        quantity: addQuantity.value,
                    };


                    if (!payload.id_ingre || !payload.quantity) {
                        toastr.error('Vui lòng điền đầy đủ thông tin');
                        return;
                    }

                    const btn = document.getElementById('btnAddDetail');
                    btn.disabled = true;
                    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang thêm';

                    fetch(`/admin/transfers/${idTran}/details`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify(payload),
                        })
                        .then(async (res) => {
                            const data = await res.json();
                            if (!res.ok) throw data;
                            return data;
                        })
                        .then((data) => {
                            toastr.success(data.message, 'Thành công');
                            setTimeout(() => {
                                window.location.href = data.redirect || window.location.href;
                            }, 800);
                        })
                        .catch((err) => {
                            toastr.error(err.message || 'Có lỗi xảy ra', 'Lỗi');
                            btn.disabled = false;
                            btn.innerHTML = '<i class="fas fa-plus"></i> Thêm';
                        });
                });
            }
        });
    </script>
@endsection
