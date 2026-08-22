@extends('layouts.admin_home')

@section('title', 'Danh sách hóa đơn')

@section('content')
    <div id="content">
        @include('admin.partials.topbar')

        <div class="container-fluid">

            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">
                    Danh sách hóa đơn
                </h1>
                <div>
                    <a href="{{ route('admin.bill.show', ['id' => 0, 'orderId' => request('preview_order')]) }}"
                       class="btn btn-sm btn-warning shadow-sm {{ request('preview_order') ? '' : 'd-none' }}">
                        <i class="fas fa-file-invoice"></i> Xem tạm tính
                    </a>
                </div>
            </div>

            {{-- Bộ lọc --}}
            <div class="card shadow mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.bill.index') }}" class="mb-0">
                        <div class="row">
                            <div class="col-md-2 mb-2">
                                <label class="small font-weight-bold">Mã hóa đơn</label>
                                <input type="text" name="search" class="form-control"
                                       placeholder="#HD..." value="{{ request('search') }}">
                            </div>
                            <div class="col-md-2 mb-2">
                                <label class="small font-weight-bold">Chi nhánh</label>
                                <select name="branch" class="form-control">
                                    <option value="">Tất cả</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id_br }}" {{ request('branch') == $branch->id_br ? 'selected' : '' }}>
                                            {{ $branch->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 mb-2">
                                <label class="small font-weight-bold">Trạng thái</label>
                                <select name="status" class="form-control">
                                    <option value="all" {{ request('status') == 'all' || request('status') === null ? 'selected' : '' }}>Tất cả</option>
                                    <option value="2" {{ request('status') == '2' ? 'selected' : '' }}>Đã thanh toán</option>
                                    <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>Chưa thanh toán</option>
                                </select>
                            </div>
                            <div class="col-md-2 mb-2">
                                <label class="small font-weight-bold">Phương thức</label>
                                <select name="pay_method" class="form-control">
                                    <option value="">Tất cả</option>
                                    <option value="cash"   {{ request('pay_method') == 'cash' ? 'selected' : '' }}>Tiền mặt</option>
                                    <option value="card"   {{ request('pay_method') == 'card' ? 'selected' : '' }}>Thẻ</option>
                                    <option value="transfer" {{ request('pay_method') == 'transfer' ? 'selected' : '' }}>Chuyển khoản</option>
                                    <option value="qr"     {{ request('pay_method') == 'qr' ? 'selected' : '' }}>QR Code</option>
                                </select>
                            </div>
                            <div class="col-md-2 mb-2">
                                <label class="small font-weight-bold">Từ ngày</label>
                                <input type="date" name="from" class="form-control" value="{{ request('from') }}">
                            </div>
                            <div class="col-md-2 mb-2">
                                <label class="small font-weight-bold">Đến ngày</label>
                                <input type="date" name="to" class="form-control" value="{{ request('to') }}">
                            </div>
                            <div class="col-md-2 mb-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary mr-2"><i class="fas fa-search"></i> Tìm</button>
                                <a href="{{ route('admin.bill.index') }}" class="btn btn-secondary"><i class="fas fa-redo"></i></a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Bảng --}}
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Danh sách hóa đơn</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>STT</th>
                                    <th>Mã HĐ</th>
                                    <th>Bàn</th>
                                    <th>Chi nhánh</th>
                                    <th>Nhân viên</th>
                                    <th>Trạng thái</th>
                                    <th>Phương thức</th>
                                    <th class="text-right">Tổng tiền</th>
                                    <th>Thời gian</th>
                                    <th class="text-center">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($bills as $key => $bill)
                                    <tr>
                                        <td>{{ $key + 1 + ($bills->currentPage() - 1) * $bills->perPage() }}</td>
                                        <td><strong>#HD{{ $bill->id_bill }}</strong></td>
                                        <td>{{ $bill->order->table->table_name ?? '—' }}</td>
                                        <td>{{ $bill->order->branch->name ?? '—' }}</td>
                                        <td>{{ $bill->order->user->name ?? '—' }}</td>
                                        <td>
                                            @if((int) $bill->status === 1)
                                                <span class="badge badge-danger"><i class="fas fa-exclamation-triangle mr-1"></i>Chưa thanh toán</span>
                                            @else
                                                <span class="badge badge-success"><i class="fas fa-check mr-1"></i>Đã thanh toán</span>
                                            @endif
                                        </td>
                                        <td>
                                            @php
                                                $payLabel = match($bill->pay_method) {
                                                    'cash'     => 'Tiền mặt',
                                                    'card'     => 'Thẻ',
                                                    'transfer' => 'Chuyển khoản',
                                                    'qr'       => 'QR Code',
                                                    default    => $bill->pay_method ?: '—',
                                                };
                                            @endphp
                                            <span class="badge badge-info">{{ $payLabel }}</span>
                                        </td>
                                        <td class="text-right {{ (int) $bill->status === 1 ? 'text-danger font-weight-bold' : 'text-success font-weight-bold' }}">
                                            {{ number_format((float) $bill->total, 0, ',', '.') }} ₫
                                        </td>
                                        <td>
                                            {{ $bill->pay_time ? \Carbon\Carbon::parse($bill->pay_time)->format('d/m/Y H:i') : '—' }}
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route('admin.bill.show', $bill->id_bill) }}"
                                               class="btn btn-sm btn-info" title="Xem chi tiết">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center text-muted py-4">
                                            <i class="fas fa-inbox fa-2x mb-2"></i><br>
                                            Chưa có hóa đơn nào
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Phân trang --}}
                    <div class="d-flex justify-content-between align-items-center mt-2">
                        <small class="text-muted">
                            Hiển thị {{ $bills->count() }} / {{ $bills->total() }} hóa đơn
                        </small>
                        {{ $bills->links() }}
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection