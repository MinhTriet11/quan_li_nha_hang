@extends('layouts.admin_home')

@section('title', 'Chi tiết phiếu nhập kho #NK' . $receipt->id_im)

@section('content')

    <div id="content">
        @include('admin.partials.topbar')

        <div class="container-fluid">

            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">Chi tiết phiếu nhập #NK{{ $receipt->id_im }}</h1>
                <a href="{{ route('admin.import.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Quay lại
                </a>
            </div>

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            {{-- Thông tin header --}}
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Thông tin phiếu nhập</h6>
                    @if ($receipt->status == 0)
                        <span class="badge badge-warning badge-lg">Chờ duyệt</span>
                    @elseif ($receipt->status == 2)
                        <span class="badge badge-danger badge-lg">Từ chối</span>
                    @else
                        <span class="badge badge-success badge-lg">Đã duyệt</span>
                    @endif
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <small class="text-muted">Mã phiếu</small>
                            <p class="font-weight-bold">#NK{{ $receipt->id_im }}</p>
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
                            <small class="text-muted">Ngày tạo</small>
                            <p class="font-weight-bold">
                                {{ $receipt->created_at ? $receipt->created_at->format('d/m/Y H:i') : '—' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Bảng chi tiết --}}
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Chi tiết nguyên liệu</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>STT</th>
                                    <th>Nguyên liệu</th>
                                    <th>Đơn vị</th>
                                    <th>Nhà cung cấp</th>
                                    <th class="text-center">Số lượng</th>
                                    <th class="text-right">Đơn giá</th>
                                    <th class="text-right">Thành tiền</th>
                                    @if ($receipt->status == 0)
                                        <th class="text-center" width="11%">Thao tác</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody id="detailsBody">
                                @foreach ($receipt->details as $key => $detail)
                                    <tr data-detail-id="{{ $detail->id_import_detail }}">
                                        <td>{{ $key + 1 }}</td>
                                        <td>{{ $detail->ingredient->name ?? '—' }}</td>
                                        <td>{{ $detail->ingredient->unit ?? '—' }}</td>
                                        <td>{{ $detail->supplier->name ?? '—' }}</td>
                                        <td class="text-center" style="width: 140px;">
                                            <input type="number" step="0.01" min="0.01" id="qntReceipt"
                                                class="form-control form-control-sm text-center inp-qty"
                                                value="{{ $detail->quantity }}"
                                                data-detail-id="{{ $detail->id_import_detail }}"
                                                {{ $receipt->status == 0 ? '' : 'disabled' }}>
                                        </td>
                                        <td class="text-right">{{ number_format($detail->price) }} đ</td>
                                        <td class="text-right cell-total" data-price="{{ $detail->price }}">
                                            {{ number_format($detail->quantity * $detail->price) }} đ
                                        </td>
                                        @if ($receipt->status == 0)
                                            <td class="text-center" style="width: 60px;">
                                                <form
                                                    action="{{ route('admin.import.detail.destroy', [
                                                        $receipt->id_im ,
                                                        $detail->id_import_detail
                                                    ]) }}"
                                                    method="POST" >
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger btn-delete-detail"
                                                        onclick="return confirm('Bạn có chắc chắn muốn xóa nguyên liệu này?')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="{{ $receipt->status == 0 ? 7 : 6 }}" class="text-right font-weight-bold">
                                        Tổng cộng:</td>
                                    <td class="text-right font-weight-bold text-primary" id="grandTotal">
                                        {{ number_format($receipt->total) }} đ</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    @if ($receipt->status == 0)
                        {{-- Form thêm dòng nguyên liệu --}}
                        <form id="addDetailForm" class="mt-4 border-top pt-3">
                            @csrf
                            <h6 class="font-weight-bold text-primary mb-3">Thêm nguyên liệu vào phiếu</h6>
                            <div class="row">
                                <div class="form-group col-md-3">
                                    <label class="small">Nguyên liệu <span class="text-danger">*</span></label>
                                    <select name="id_ingre" class="form-control" required>
                                        <option value="">----- Chọn nguyên liệu -----</option>
                                        @foreach ($ingredients as $ingre)
                                            <option value="{{ $ingre->id_ingre }}">{{ $ingre->name }} ({{ $ingre->unit }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-3">
                                    <label class="small">Nhà cung cấp <span class="text-danger">*</span></label>
                                    <select name="id_su" class="form-control" required>
                                        <option value="">----- Chọn NCC -----</option>
                                        @foreach ($suppliers as $sup)
                                            <option value="{{ $sup->id_su }}">{{ $sup->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-2">
                                    <label class="small">Số lượng <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" min="0.01" name="quantity" class="form-control"
                                        placeholder="SL" required>
                                </div>
                                <div class="form-group col-md-2">
                                    <label class="small">Đơn giá (VNĐ) <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" min="0" name="price" class="form-control"
                                        placeholder="Giá" required>
                                </div>
                                <div class="form-group col-md-2 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary btn-block" id="btnAddDetail">
                                        <i class="fas fa-plus"></i> Thêm
                                    </button>
                                </div>
                            </div>
                        </form>
                    @endif

                    <div class="mt-4 d-flex justify-content-between">
                        <a href="{{ route('admin.import.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Quay lại danh sách
                        </a>
                        <div>
                            @if ($receipt->status == 0)
                                <form class="d-inline" id="importReceiptApprove">
                                    @csrf
                                    <button type="submit" class="btn btn-success"
                                        onclick="return confirm('Duyệt phiếu nhập sẽ cộng tồn kho cho chi nhánh {{ $receipt->branch->name }}. Bạn có chắc chắn?')">
                                        <i class="fas fa-check"></i> Duyệt phiếu
                                    </button>
                                </form>
                                <form class="d-inline" id="importReceiptReject">
                                    @csrf
                                    <button type="submit" class="btn btn-warning"
                                        onclick="return confirm('Bạn có chắc chắn muốn từ chối phiếu nhập này?')">
                                        <i class="fas fa-times"></i> Từ chối
                                    </button>
                                </form>
                            <form action="{{ route('admin.import.destroy', $receipt->id_im) }}" method="POST"
                                class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger"
                                    onclick="return confirm('Bạn có chắc chắn muốn xóa phiếu nhập này?')">
                                    <i class="fas fa-trash"></i> Xóa phiếu
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
        //xu ly form duyet
        const importReceiptApprove = document.getElementById('importReceiptApprove');

        if (importReceiptApprove) {

            importReceiptApprove.onsubmit = function(e) {
                e.preventDefault();
                fetch(`{{ route('admin.import.approve', $receipt->id_im) }}`, {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": document.querySelector(
                                'meta[name="csrf-token"]',
                            )?.content || "",
                        },
                    })
                    .then(async (response) => {
                        const data = await response.json();

                        if (!response.ok) {
                            throw data
                        }

                        return data
                    })

                    .then((data) => {
                        if (data.success) {
                            toastr.success(data.message, "Thành công");
                            setTimeout(() => {
                                window.location.reload();
                            }, 1000);

                        } else if (data.error) {
                            toastr.error(data.message, "Lỗi");
                        }
                    })
                    .catch((error) => {
                        toastr.error(error.message, "Lỗi", );

                    });
            }
        }

        //xu ly form tu choi
        const importReceiptReject = document.getElementById('importReceiptReject');

        if (importReceiptReject) {

            importReceiptReject.onsubmit = function(e) {
                e.preventDefault();
                fetch(`{{ route('admin.import.reject', $receipt->id_im) }}`, {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": document.querySelector(
                                'meta[name="csrf-token"]',
                            )?.content || "",
                        },
                    })
                    .then(async (response) => {
                        const data = await response.json();

                        if (!response.ok) {
                            throw data
                        }

                        return data
                    })

                    .then((data) => {
                        if (data.success) {
                            toastr.success(data.message, "Thành công");
                            setTimeout(() => {
                                window.location.reload();
                            }, 1000);

                        } else if (data.error) {
                            toastr.error(data.message, "Lỗi");
                        }
                    })
                    .catch((error) => {
                        toastr.error(error.message, "Lỗi", );

                    });
            }
        }


        //xu ly cap nhat so luong
        const qntReceipt = document.getElementById('qntReceipt');

        if (qntReceipt) {
            const detailId = qntReceipt.closest('tr').dataset.detailId;

            qntReceipt.onchange = function(e) {
                let qntReceiptNew = e.target.value;

                let idImport = {{ $receipt->id_im }}

                fetch(`/admin/import-receipts/${idImport}/details/${detailId}`, {
                        method: "PUT",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')?.content || "",
                        },
                        body: JSON.stringify({
                            quantity: qntReceiptNew
                        }),
                    })
                    .then(async (response) => {
                        const data = await response.json();

                        if (!response.ok) {
                            throw data
                        }

                        return data
                    })

                    .then((data) => {
                        if (data.success) {
                            toastr.success(data.message, "Thành công");
                            setTimeout(() => {
                                window.location.reload();
                            }, 1000);

                        } else if (data.error) {
                            toastr.error(data.message, "Lỗi");
                        }
                    })
                    .catch((error) => {
                        toastr.error(error.message, "Lỗi", );

                    });
            }
        }


        // ===== Thêm dòng nguyên liệu vào phiếu =====
        const addDetailForm = document.getElementById('addDetailForm');
        if (addDetailForm) {
            addDetailForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(addDetailForm);
                const payload = Object.fromEntries(formData.entries());

                if (!payload.id_ingre || !payload.id_su || !payload.quantity || payload.price === '') {
                    toastr.error('Vui lòng điền đầy đủ thông tin');
                    return;
                }

                const btn = document.getElementById('btnAddDetail');
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang thêm';

                fetch(`{{ route('admin.import.detail.store', $receipt->id_im) }}`, {
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
