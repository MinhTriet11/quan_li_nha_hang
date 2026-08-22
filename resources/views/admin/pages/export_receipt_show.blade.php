@extends('layouts.admin_home')

@section('title', 'Chi tiết phiếu xuất kho #XK' . $receipt->id_ex)

@section('content')

    <div id="content">
        @include('admin.partials.topbar')

        <div class="container-fluid">

            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">Chi tiết phiếu xuất #{{ $receipt->id_ex }}</h1>
                <a href="{{ route('admin.export.index') }}" class="btn btn-secondary">
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
                    <h6 class="m-0 font-weight-bold text-primary">Thông tin phiếu xuất</h6>
                    <div>
                        @if ((int) $receipt->status === 0)
                            <span class="badge badge-warning badge-lg">Chờ duyệt</span>
                        @elseif((int) $receipt->status === 1)
                            <span class="badge badge-success badge-lg">Đã duyệt</span>
                        @else
                            <span class="badge badge-danger badge-lg">Từ chối</span>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <small class="text-muted">Mã phiếu</small>
                            <p class="font-weight-bold">#XK{{ $receipt->id_ex }}</p>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted">Chi nhánh</small>
                            <p class="font-weight-bold">{{ $receipt->branch->name ?? '—' }}</p>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted">Người tạo</small>
                            <p class="font-weight-bold">{{ $receipt->user->name ?? '—' }}</p>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted">Lý do</small>
                            <p class="font-weight-bold">{{ $receipt->reason_label }}</p>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted">Ngày tạo</small>
                            <p class="font-weight-bold">
                                {{ $receipt->created_at ? $receipt->created_at->format('d/m/Y H:i') : '—' }}</p>
                        </div>
                        <div class="col-md-9">
                            <small class="text-muted">Ghi chú</small>
                            <p class="font-weight-bold">{{ $receipt->note ?: '—' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Bảng chi tiết --}}
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Chi tiết nguyên liệu xuất</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>STT</th>
                                    <th>Nguyên liệu</th>
                                    <th>Đơn vị</th>
                                    <th class="text-center">Tồn kho CN</th>
                                    <th class="text-center">Số lượng xuất</th>
                                    @if ((int) $receipt->status === 0 && $receipt->reason !== 'sale')
                                        <th class="text-center" width="11%">Thao tác</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody id="detailsBody">
                                @forelse($receipt->details as $key => $detail)
                                    @php
                                        $stockQty = isset($stocks[$detail->id_ingre])? (float) $stocks[$detail->id_ingre]->quantity : 0;
                                    @endphp
                                    <tr data-detail-id="{{ $detail->id_export_detail }}">
                                        <td>{{ $key + 1 }}</td>
                                        <td>{{ $detail->ingredient->name ?? '—' }}</td>
                                        <td>{{ $detail->ingredient->unit ?? '—' }}</td>
                                        <td class="text-center">
                                            <span
                                                class="{{ $stockQty > 0 ? 'text-danger font-weight-bold' : 'text-danger font-weight-bold' }}">
                                                {{ rtrim(rtrim(number_format($stockQty, 2, '.', ''), '0'), '.') }}
                                            </span>
                                        </td>
                                        <td class="text-center" style="width: 140px;">
                                            <input type="number" step="0.01" min="0.01"
                                                class="form-control form-control-sm text-center inp-qty"
                                                value="{{ rtrim(rtrim(number_format((float) $detail->quantity, 2, '.', ''), '0'), '.') }}"
                                                data-detail-id="{{ $detail->id_export_detail }}"
                                                {{ ((int) $receipt->status === 0 && $receipt->reason !== 'sale') ? '' : 'disabled' }}>
                                        </td>
                                        @if ((int) $receipt->status === 0 && $receipt->reason !== 'sale')
                                            <td class="text-center" style="width: 60px;">
                                                <form
                                                    action="{{ route('admin.export.detail.destroy', [
                                                        $receipt->id_ex,
                                                        $detail->id_export_detail,
                                                    ]) }}"
                                                    method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger btn-delete-detail"
                                                        onclick="return confirm('Bạn có chắc chắn muốn xóa nguyên liệu này?')" disabled>
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ ((int) $receipt->status === 0 && $receipt->reason !== 'sale') ? 5 : 4 }}"
                                            class="text-center text-muted">Phiếu chưa có dòng nguyên liệu</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ((int) $receipt->status === 0 && $receipt->reason !== 'sale')
                        {{-- Form thêm dòng nguyên liệu --}}
                        <form id="addDetailForm" class="mt-4 border-top pt-3">
                            @csrf
                            <h6 class="font-weight-bold text-primary mb-3">Thêm nguyên liệu vào phiếu</h6>
                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label class="small">Nguyên liệu <span class="text-danger">*</span></label>
                                    <select name="id_ingre" class="form-control" required>
                                        <option value="">----- Chọn nguyên liệu -----</option>
                                        @foreach ($ingredients as $ingre)
                                            @php
                                                $stockIngre = isset($stocks[$ingre->id_ingre])
                                                    ? (float) $stocks[$ingre->id_ingre]->quantity
                                                    : 0;
                                            @endphp
                                            @if ($stockIngre > 0)
                                            <option value="{{ $ingre->id_ingre }}">{{ $ingre->name }} ({{ $ingre->unit }})— tồn:
                                                    {{ rtrim(rtrim(number_format($stockIngre, 2, '.', ''), '0'), '.') }}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-3">
                                    <label class="small">Số lượng <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" min="0.01" name="quantity" class="form-control"
                                        placeholder="SL" required>
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
                        <a href="{{ route('admin.export.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Quay lại danh sách
                        </a>
                        <div>
                            @if ((int) $receipt->status === 0)
                                @if ($receipt->reason !== 'sale')
                                    <form action="{{ route('admin.export.approve', $receipt->id_ex) }}" method="POST"
                                        class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-success"
                                            onclick="return confirm('Duyệt phiếu xuất sẽ TRỪ tồn kho chi nhánh {{ $receipt->branch->name }}. Bạn có chắc chắn?')">
                                            <i class="fas fa-check"></i> Duyệt phiếu
                                        </button>
                                    </form>

                                    <form class="d-inline" id="exportReceiptReject">
                                        @csrf
                                        <button type="submit" class="btn btn-warning"
                                            onclick="return confirm('Bạn có chắc chắn muốn từ chối phiếu xuất này?')">
                                            <i class="fas fa-times"></i> Từ chối
                                        </button>
                                    </form>
                                @endif
                            @endif
                            @if ((int) $receipt->status !== 1)
                                    <form action="{{ route('admin.export.destroy', $receipt->id_ex) }}" method="POST"
                                        class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger"
                                            onclick="return confirm('Bạn có chắc chắn muốn xóa phiếu xuất này?')">
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
    const tbody = document.getElementById('detailsBody');

    function updateRemoveButtons() {
                const rows = tbody.querySelectorAll('tr');
                rows.forEach(function(row) {
                    const btn = row.querySelector('.btn-delete-detail');
                    if (btn) btn.disabled = rows.length <= 1;
                });
            }

    updateRemoveButtons();

    // ===== Cập nhật số lượng từng dòng (inline) =====
    document.querySelectorAll('.inp-qty').forEach(function (input) {
        input.addEventListener('change', function (e) {
            const newQty = e.target.value;
            const detailId = e.target.dataset.detailId;
            const idEx = {{ $receipt->id_ex }};

            fetch(`/admin/export-receipts/${idEx}/details/${detailId}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ quantity: newQty }),
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

    // ===== Xử lý form duyệt =====
    const exportReceiptApprove = document.getElementById('exportReceiptApprove');
    if (exportReceiptApprove) {
        exportReceiptApprove.onsubmit = function (e) {
            e.preventDefault();
            fetch(`{{ route('admin.export.approve', $receipt->id_ex) }}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                },
            })
                .then(async (response) => {
                    const data = await response.json();
                    if (!response.ok) throw data;
                    return data;
                })
                .then((data) => {
                    if (data.success) {
                        toastr.success(data.message, 'Thành công');
                        setTimeout(() => window.location.reload(), 1000);
                    } else if (data.error) {
                        toastr.error(data.message, 'Lỗi');
                    }
                })
                .catch((error) => {
                    toastr.error(error.message, 'Lỗi');
                });
        };
    }

    // ===== Xử lý form từ chối =====
    const exportReceiptReject = document.getElementById('exportReceiptReject');
    if (exportReceiptReject) {
        exportReceiptReject.onsubmit = function (e) {
            e.preventDefault();
            fetch(`{{ route('admin.export.reject', $receipt->id_ex) }}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                },
            })
                .then(async (response) => {
                    const data = await response.json();
                    if (!response.ok) throw data;
                    return data;
                })
                .then((data) => {
                    if (data.success) {
                        toastr.success(data.message, 'Thành công');
                        setTimeout(() => window.location.reload(), 1000);
                    } else if (data.error) {
                        toastr.error(data.message, 'Lỗi');
                    }
                })
                .catch((error) => {
                    toastr.error(error.message, 'Lỗi');
                });
        };
    }

    // ===== Thêm dòng nguyên liệu vào phiếu =====
    const addDetailForm = document.getElementById('addDetailForm');
    if (addDetailForm) {
        addDetailForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(addDetailForm);
            const payload = Object.fromEntries(formData.entries());

            if (!payload.id_ingre || !payload.quantity) {
                toastr.error('Vui lòng điền đầy đủ thông tin');
                return;
            }

            const btn = document.getElementById('btnAddDetail');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang thêm';

            fetch(`{{ route('admin.export.detail.store', $receipt->id_ex) }}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
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
</script>

@endsection
