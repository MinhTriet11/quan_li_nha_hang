@extends('layouts.admin_home')

@section('title', 'Chi tiết phiếu kiểm kê')

@section('content')

    <div id="content">
        @include('admin.partials.topbar')

        <div class="container-fluid">

            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">
                    Phiếu kiểm kê #KK{{ $receipt->id_stock }}
                </h1>
                <a href="{{ route('admin.stock_check.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Quay lại
                </a>
            </div>

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
            @endif

            {{-- Thông tin phiếu --}}
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Thông tin phiếu</h6>
                    <span class="badge badge-{{ $receipt->status_badge }} badge-lg">{{ $receipt->status_label }}</span>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-2">
                            <small class="text-muted">Chi nhánh</small>
                            <p class="font-weight-bold">{{ $receipt->branch->name ?? '—' }}</p>
                        </div>
                        <div class="col-md-4 mb-2">
                            <small class="text-muted">Người tạo</small>
                            <p class="font-weight-bold">{{ $receipt->user->name ?? '—' }}</p>
                        </div>
                        <div class="col-md-4 mb-2">
                            <small class="text-muted">Ngày tạo</small>
                            <p class="font-weight-bold">{{ $receipt->created_at?->format('d/m/Y H:i') }}</p>
                        </div>
                        @if ($receipt->note)
                            <div class="col-md-12 mt-2">
                                <small class="text-muted">Ghi chú</small>
                                <div>{{ $receipt->note }}</div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Bảng chi tiết --}}
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Chi tiết kiểm kê</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                            <thead class="thead-light">
                                <tr>
                                    <th width="50">STT</th>
                                    <th>Nguyên liệu</th>
                                    <th>Đơn vị</th>
                                    <th class="text-right">Tồn hệ thống</th>
                                    <th class="text-right text-center" style="min-width: 160px;">Số lượng thực tế</th>
                                </tr>
                            </thead>
                            <tbody id="detailsBody">
                                @forelse($receipt->details as $key => $d)
                                    @php
                                        $isEditable = (int) $receipt->status === 0;
                                        $systemQty = (float) ($d->system_quantity ?? 0);
                                        $actualQty = (float) $d->actual_quantity;

                                        // $diffQty   = (float) ($d->difference ?? ($actualQty - $systemQty));

                                        $stockIngre = isset($stocks[$d->id_ingre]) ? (float) $stocks[$d->id_ingre]->quantity : 0;
                                    @endphp
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td><strong>{{ $d->ingredient->name ?? '—' }}</strong></td>
                                        <td>{{ $d->ingredient->unit ?? '' }}</td>
                                        <td class="text-right">{{ number_format($systemQty, 2) }}</td>
                                        <td class="text-center">
                                            @if ($isEditable)
                                                <input type="number" step="0.01" min="0"
                                                    value="{{ rtrim(rtrim(number_format($actualQty, 2, '.', ''), '0'), '.') }}"
                                                    class="form-control form-control-sm text-center inp-qty mx-auto"
                                                    data-detail-id="{{ $d->id_sto_detail }}"
                                                    style="max-width: 140px;">
                                            @else
                                                {{ number_format($actualQty, 2) }}
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">Phiếu chưa có dòng nguyên liệu nào.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4 d-flex justify-content-between">
                        <a href="{{ route('admin.stock_check.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Quay lại danh sách
                        </a>
                        <div>
                            @if ((int) $receipt->status === 0)
                                <form action="{{ route('admin.stock_check.approve', $receipt->id_stock) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-success"
                                        onclick="return confirm('Duyệt phiếu sẽ cập nhật tồn kho chi nhánh {{ $receipt->branch->name ?? '' }} theo số lượng thực tế. Bạn có chắc chắn?')">
                                        <i class="fas fa-check"></i> Duyệt phiếu
                                    </button>
                                </form>

                                <form action="{{ route('admin.stock_check.reject', $receipt->id_stock) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-warning"
                                        onclick="return confirm('Bạn có chắc chắn muốn từ chối phiếu kiểm kê này?')">
                                        <i class="fas fa-times"></i> Từ chối
                                    </button>
                                </form>

                                <form action="{{ route('admin.stock_check.destroy', $receipt->id_stock) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger"
                                        onclick="return confirm('Bạn có chắc chắn muốn xóa phiếu kiểm kê này?')">
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
        document.addEventListener('DOMContentLoaded', function () {
            const idStock  = {{ $receipt->id_stock }};
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

            // ===== Cập nhật số lượng từng dòng (inline) — chỉ khi phiếu chưa duyệt =====
            document.querySelectorAll('.inp-qty').forEach(function (input) {
                input.addEventListener('change', function (e) {
                    const newQty   = e.target.value;
                    const detailId = e.target.dataset.detailId;

                    fetch(`/admin/stock-checks/${idStock}/details/${detailId}`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ actual_quantity: newQty }),
                    })
                    .then(async (res) => {
                        const data = await res.json();
                        if (!res.ok) throw data;
                        return data;
                    })
                    .then((data) => {
                        if (data.success) toastr.success(data.message, 'Thành công');
                        else if (data.error) toastr.error(data.message, 'Lỗi');
                    })
                    .catch((err) => {
                        toastr.error(err.message || 'Có lỗi xảy ra', 'Lỗi');
                        setTimeout(() => window.location.reload(), 800);
                    });
                });
            });
        });
    </script>
@endsection