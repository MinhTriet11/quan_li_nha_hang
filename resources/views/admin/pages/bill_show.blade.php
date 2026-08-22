@extends('layouts.admin_home')

@section('title', $bill ? 'Chi tiết hóa đơn #HD' . $bill->id_bill : 'Chi tiết tạm tính đơn #' . ($order->id_order ?? ''))

@section('content')
    <div id="content">
        @include('admin.partials.topbar')

        <div class="container-fluid">

            {{-- ============== Page heading + nút quay lại ============== --}}
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <div>
                    <h1 class="h3 mb-1 text-gray-800 font-weight-bold">
                        {{-- <i class="fas fa-receipt text-primary mr-2"></i> --}}
                        {{ $bill ? 'Hóa đơn HD#' . $bill->id_bill : 'Tạm tính đơn #' . ($order->id_order ?? '') }}
                    </h1>
                    {{-- <small class="text-muted">
                        <i class="far fa-clock mr-1"></i>
                        @php
                            $billDate = $bill && $bill->pay_time
                                ? \Carbon\Carbon::parse($bill->pay_time)
                                : ($order->order_time ? \Carbon\Carbon::parse($order->order_time) : \Carbon\Carbon::now());
                        @endphp
                        Cập nhật lúc: {{ $billDate->format('d/m/Y H:i') }}
                    </small> --}}
                </div>
                <div>
                    @if($bill)
                        <span class="badge badge-pill badge-{{ (int) $bill->status === 1 ? 'danger' : 'success' }} px-3 py-2 mr-2"
                              style="font-size: 13px;">
                            <i class="fas {{ (int) $bill->status === 1 ? 'fa-exclamation-triangle' : 'fa-check-circle' }} mr-1"></i>
                            {{ (int) $bill->status === 1 ? 'CHƯA THANH TOÁN' : 'ĐÃ THANH TOÁN' }}
                        </span>
                    @else
                        <span class="badge badge-pill badge-warning px-3 py-2 mr-2" style="font-size: 13px;">
                            <i class="fas fa-file-invoice mr-1"></i> TẠM TÍNH
                        </span>
                    @endif
                    <a href="{{ $bill ? route('admin.bill.index') : route('admin.order.management') }}"
                       class="btn btn-sm btn-secondary shadow-sm">
                        <i class="fas fa-arrow-left fa-sm text-white-50"></i> Quay lại
                    </a>
                </div>
            </div>

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                </div>
            @endif

            {{-- Cảnh báo hóa đơn chưa thanh toán --}}
            @if($bill && (int) $bill->status === 1)
                <div class="alert alert-warning border-left-warning d-flex align-items-center" role="alert">
                    <i class="fas fa-exclamation-triangle fa-2x mr-3 text-warning"></i>
                    <div>
                        <strong>Hóa đơn chưa thanh toán.</strong>
                        Khách đã rời đi mà chưa thanh toán — vui lòng theo dõi và xử lý sau.
                    </div>
                </div>
            @endif

            <div class="row">
                {{-- ============== Cột trái: Thông tin hóa đơn ============== --}}
                <div class="col-lg-6 mb-4">
                    <div class="card shadow h-100 border-0">
                        <div class="card-header py-3 bg-primary text-white">
                            <h6 class="m-0 font-weight-bold">
                                <i class="fas fa-info-circle mr-2"></i>Thông tin hóa đơn
                            </h6>
                        </div>
                        <div class="card-body">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <span class="text-muted"><i class="fas fa-hashtag mr-2 text-primary"></i>Mã hóa đơn</span>
                                    <strong>
                                        @if($bill)
                                            #HD{{ $bill->id_bill }}
                                        @else
                                            <span class="text-muted font-weight-normal">— (Tạm tính)</span>
                                        @endif
                                    </strong>
                                </li>
                                {{-- <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <span class="text-muted"><i class="fas fa-shopping-bag mr-2 text-primary"></i>Mã đơn hàng</span>
                                    <strong>#{{ $order->id_order ?? '—' }}</strong>
                                </li> --}}
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <span class="text-muted"><i class="fas fa-chair mr-2 text-primary"></i>Bàn</span>
                                    <span>
                                        <strong>{{ $order->table->table_name ?? '—' }}</strong>
                                        @if($order->table && $order->table->TableTypes)
                                            <small class="text-muted">({{ $order->table->TableTypes->name ?? '' }})</small>
                                        @endif
                                    </span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <span class="text-muted"><i class="fas fa-store mr-2 text-primary"></i>Chi nhánh</span>
                                    <span class="text-right">
                                        <strong>{{ $order->branch->name ?? '—' }}</strong>
                                        @if($order->branch && $order->branch->address)
                                            <br><small class="text-muted">{{ $order->branch->address }}</small>
                                        @endif
                                    </span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <span class="text-muted"><i class="fas fa-user-tie mr-2 text-primary"></i>Nhân viên</span>
                                    <span>{{ $order->user->name ?? '—' }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <span class="text-muted"><i class="far fa-clock mr-2 text-primary"></i>Giờ vào</span>
                                    <span>
                                        {{ $order->order_time ? \Carbon\Carbon::parse($order->order_time)->format('d/m/Y H:i') : '—' }}
                                    </span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <span class="text-muted"><i class="fas fa-sticky-note mr-2 text-primary"></i>Ghi chú</span>
                                    <span>{{ $order->note ?: '—' }}</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                {{-- ============== Cột phải: Thanh toán + Tổng tiền ============== --}}
                <div class="col-lg-6 mb-4">
                    {{-- Thanh toán --}}
                    <div class="card shadow mb-4 border-0">
                        <div class="card-header py-3 bg-info text-white">
                            <h6 class="m-0 font-weight-bold">
                                <i class="fas fa-credit-card mr-2"></i>Thông tin thanh toán
                            </h6>
                        </div>
                        <div class="card-body">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <span class="text-muted"><i class="fas fa-money-bill-wave mr-2 text-info"></i>Phương thức</span>
                                    <span>
                                        @if($bill && (int) $bill->status !== 1)
                                            @php
                                                $payLabel = match($bill->pay_method) {
                                                    'cash'     => 'Tiền mặt',
                                                    'card'     => 'Thẻ',
                                                    'transfer' => 'Chuyển khoản',
                                                    'qr'       => 'QR Code',
                                                    default    => $bill->pay_method ?: '—',
                                                };
                                            @endphp
                                            <span class="badge badge-info px-3 py-1">{{ $payLabel }}</span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <span class="text-muted"><i class="far fa-calendar-check mr-2 text-info"></i>Thời gian thanh toán</span>
                                    <span>
                                        @if($bill && $bill->pay_time)
                                            {{ \Carbon\Carbon::parse($bill->pay_time)->format('d/m/Y H:i') }}
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </span>
                                </li>
                            </ul>
                        </div>
                    </div>

                    {{-- Tổng tiền --}}
                    <div class="card shadow border-0">
                        <div class="card-header py-3 bg-success text-white">
                            <h6 class="m-0 font-weight-bold">
                                <i class="fas fa-calculator mr-2"></i>Tổng tiền
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between py-2">
                                <span class="text-muted">Tạm tính</span>
                                <strong>{{ number_format($subtotal, 0, ',', '.') }} ₫</strong>
                            </div>
                            {{-- <div class="d-flex justify-content-between py-2">
                                <span class="text-muted">Thuế VAT (8%)</span>
                                <strong>{{ number_format($tax, 0, ',', '.') }} ₫</strong>
                            </div> --}}
                            <hr class="my-2">
                            <div class="d-flex justify-content-between align-items-center py-2">
                                <span class="h5 mb-0 font-weight-bold text-success">
                                    <i class="fas fa-coins mr-1"></i>TỔNG CỘNG
                                </span>
                                <span class="h4 mb-0 font-weight-bold text-success">
                                    {{ number_format($subtotal, 0, ',', '.') }} ₫
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ============== Danh sách món ============== --}}
            <div class="card shadow mb-4 border-0">
                <div class="card-header py-3 d-flex align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-utensils mr-2"></i>Danh sách món
                        <span class="badge badge-secondary ml-2">{{ $grouped->count() }}</span>
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered align-middle" width="100%" cellspacing="0">
                            <thead class="thead-light">
                                <tr>
                                    <th width="60">STT</th>
                                    <th>Tên món</th>
                                    <th width="90" class="text-center">SL</th>
                                    <th width="140" class="text-right">Đơn giá</th>
                                    <th width="160" class="text-right">Thành tiền</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($grouped as $i => $detail)
                                    <tr>
                                        <td class="text-muted">{{ $i + 1 }}</td>
                                        <td>
                                            <strong>{{ $detail->menuItem->name ?? ('Món #' . $detail->id_food) }}</strong>
                                        </td>
                                        <td class="text-center"><span class="badge badge-light border px-3">{{ (int) $detail->quantity }}</span></td>
                                        <td class="text-right">{{ number_format((float) $detail->price, 0, ',', '.') }} ₫</td>
                                        <td class="text-right font-weight-bold text-primary">
                                            {{ number_format((float) $detail->price * (int) $detail->quantity, 0, ',', '.') }} ₫
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-5">
                                            <div class="text-muted">
                                                <i class="fas fa-inbox fa-2x mb-2"></i>
                                                <p class="mb-0">Đơn hàng chưa có món nào</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                            @if($grouped->count() > 0)
                            <tfoot class="thead-light">
                                <tr>
                                    <th colspan="4" class="text-right">Tổng số lượng:</th>
                                    <th class="text-right">{{ $grouped->sum(fn($d) => (int) $d->quantity) }} món</th>
                                </tr>
                            </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
