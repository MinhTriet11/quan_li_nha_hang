@extends('layouts.admin_home')

@section('title', 'Tạo phiếu kiểm kê')

@section('content')

<div id="content">
    @include('admin.partials.topbar')

    <div class="container-fluid">

        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">
                Tạo phiếu kiểm kê
            </h1>
            <a href="{{ route('admin.stock_check.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
        </div>

        {{-- Chọn chi nhánh (GET) --}}
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Bước 1 — Chọn chi nhánh cần kiểm kê</h6>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('admin.stock_check.create') }}" class="mb-0">
                    <div class="row align-items-end">
                        <div class="col-md-8 mb-2">
                            <label class="small font-weight-bold">Chi nhánh <span class="text-danger">*</span></label>
                            <select name="branch_id" class="form-control" onchange="this.form.submit()">
                                <option value="">----- Chọn chi nhánh -----</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id_br }}" {{ (string)$preselectedBranchId === (string)$branch->id_br ? 'selected' : '' }}>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 mb-2">
                            @if($preselectedBranchId)
                                <span class="text-success"><i class="fas fa-check-circle"></i> Đã chọn chi nhánh — nhập số liệu bên dưới</span>
                            @else
                                <span class="text-muted small">Chọn chi nhánh để hiển thị danh sách nguyên liệu.</span>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>

        @if($preselectedBranchId)
            {{-- Bước 2: nhập số thực tế --}}
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Bước 2 — Nhập số lượng thực tế</h6>
                </div>
                <div class="card-body">
                    @if($stocks && $stocks->count())
                        <form id="formCreateStockCheck">
                            @csrf
                            <input type="hidden" name="id_br" value="{{ $preselectedBranchId }}">

                            {{-- <div class="form-group mb-3">
                                <label class="small font-weight-bold">Ghi chú</label>
                                <textarea name="note" class="form-control" rows="2" placeholder="Ví dụ: Kiểm kê cuối ca tối..."></textarea>
                            </div> --}}

                            <div class="table-responsive">
                                <table class="table table-bordered" id="stockCheckTable">
                                    <thead class="thead-light">
                                        <tr>
                                            <th width="50">STT</th>
                                            <th>Nguyên liệu</th>
                                            <th>Đơn vị</th>
                                            <th class="text-right">Tồn hệ thống</th>
                                            <th class="text-right" style="width:180px;">Số lượng thực tế</th>
                                            <th class="text-right" style="width:140px;">Chênh lệch</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($stocks as $key => $s)
                                            <tr>
                                                <td>{{ $key + 1 }}</td>
                                                <td>
                                                    <strong>{{ $s->ingredient->name ?? '—' }}</strong>
                                                    <input type="hidden" name="details[{{ $key }}][id_ingre]" value="{{ $s->id_ingre }}">
                                                </td>
                                                <td>{{ $s->ingredient->unit ?? '' }}</td>
                                                <td class="text-right">
                                                    <span class="text-muted">{{ number_format((float)$s->quantity, 2) }}</span>
                                                </td>
                                                <td>
                                                    <input type="number" step="0.01" required
                                                        name="details[{{ $key }}][actual_quantity]"
                                                        class="form-control text-right actual-input"
                                                        data-system="{{ (float)$s->quantity }}"
                                                        value="{{ number_format((float)$s->quantity, 2, '.', '') }}">
                                                </td>
                                                <td class="text-right diff-cell">
                                                    <span class="diff-badge badge badge-secondary">0</span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-3">
                                <button type="submit" class="btn btn-warning">
                                    <i class="fas fa-save"></i> Lưu phiếu (chờ duyệt)
                                </button>
                                <a href="{{ route('admin.stock_check.index') }}" class="btn btn-secondary">Hủy</a>
                            </div>
                        </form>
                    @else
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-inbox fa-2x mb-2"></i>
                            <p>Chi nhánh này chưa có nguyên liệu nào trong kho.</p>
                        </div>
                    @endif
                </div>
            </div>
        @endif

    </div>
</div>

@endsection

@section('js')
<script>
    // ===== Tính chênh lệch realtime =====
    const DIFF_CLASSES = ['badge-secondary', 'badge-success', 'badge-danger'];

    function setBadge(badge, diff) {
        // Bỏ hết class màu cũ
        badge.classList.remove(...DIFF_CLASSES);

        const value = diff === 0 ? '0' : (diff > 0 ? '+' + diff.toFixed(2) : diff.toFixed(2));

        badge.textContent = value;

        let cls = 'badge-secondary';
        if (diff > 0) cls = 'badge-success';
        else if (diff < 0) cls = 'badge-danger';
        badge.classList.add(cls);
    }

    function recalcDiff(row) {
        if (!row) return;
        const input = row.querySelector('.actual-input');
        const badge = row.querySelector('.diff-badge');
        if (!input || !badge) return;

        const system = parseFloat(input.dataset.system) || 0;
        const actual = parseFloat(input.value) || 0;
        const diff   = actual - system;

        setBadge(badge, diff);
    }

    function recalcAll() {
        document.querySelectorAll('#stockCheckTable tbody tr').forEach(recalcDiff);
    }

    // Lắng nghe input bằng event delegation (không jQuery)
    document.addEventListener('input', function (e) {
        console.log(e)
        if (e.target && e.target.classList.contains('actual-input')) {
            const row = e.target.closest('tr');
            recalcDiff(row);
        }
    });

    // Tính lần đầu khi load trang
    document.addEventListener('DOMContentLoaded', recalcAll);

    // ===== Submit form bằng fetch (không jQuery) =====
    const form = document.getElementById('formCreateStockCheck');
    if (form) {
        form.addEventListener('submit', async function (e) {
            e.preventDefault();

            const url  = '{{ route('admin.stock_check.store') }}';
            const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            try {
                const res  = await fetch(url, {
                    method:  'POST',
                    headers: {
                        'Accept':        'application/json',
                        'X-CSRF-TOKEN':  csrf,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: new FormData(form),
                });
                const data = await res.json().catch(() => ({}));

                if (res.ok && data.success) {
                    toastr.success(data.message);
                    setTimeout(() => window.location.href = data.redirect, 800);
                    return;
                }

                // Lỗi validate (422) hoặc lỗi khác — chỉ hiện 1 toastr tóm tắt
                toastr.error(data.message || 'Có lỗi xảy ra');
            } catch (err) {
                toastr.error('Không thể gửi yêu cầu: ' + err.message);
            }
        });
    }
</script>
@endsection