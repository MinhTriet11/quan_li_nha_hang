@extends('layouts.client')

@section('title', 'Tài khoản của tôi')

@section('content')
<div class="py-4">
    <div class="container">
        <!-- Back Button -->
        <a href="{{ route('client.home') }}" class="btn btn-outline-secondary mb-4">
            <i class="fa-solid fa-arrow-left me-2"></i>Quay lại trang chủ
        </a>

        <div class="row">
            <!-- Sidebar -->
            <div class="col-lg-3 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <div class="avatar-circle-lg mx-auto mb-3">
                            <i class="fa-solid fa-user text-white fs-1"></i>
                        </div>
                        <h5 class="mb-1">{{ $customer->name }}</h5>
                        <p class="text-muted small mb-3">{{ $customer->email }}</p>
                        {{-- <span class="badge bg-warning text-dark fs-6">
                            <i class="fa-solid fa-star me-1"></i>1,250 điểm
                        </span> --}}
                    </div>
                </div>

                <div class="card border-0 shadow-sm mt-3">
                    <div class="list-group list-group-flush">
                        <a href="#info" class="list-group-item list-group-item-action active" data-bs-toggle="list">
                            <i class="fa-solid fa-user me-2"></i>Thông tin cá nhân
                        </a>
                        <a href="#password" class="list-group-item list-group-item-action" data-bs-toggle="list">
                            <i class="fa-solid fa-lock me-2"></i>Đổi mật khẩu
                        </a>
                        <a href="#bookings" class="list-group-item list-group-item-action" data-bs-toggle="list">
                            <i class="fa-solid fa-calendar-check me-2"></i>Lịch sử đặt bàn
                        </a>
                        <a href="#invoices" class="list-group-item list-group-item-action" data-bs-toggle="list">
                            <i class="fa-solid fa-receipt me-2"></i>Hóa đơn
                        </a>
                        {{-- <a href="#points" class="list-group-item list-group-item-action" data-bs-toggle="list">
                            <i class="fa-solid fa-coins me-2"></i>Tích điểm
                        </a> --}}
                        <a href="{{ route('logout') }}" class="list-group-item list-group-item-action text-danger">
                            <i class="fa-solid fa-sign-out-alt me-2"></i>Đăng xuất
                        </a>
                    </div>
                </div>
            </div>

            <!-- Content -->
            <div class="col-lg-9">
                <div class="tab-content">
                    <!-- Thông tin cá nhân -->
                    <div class="tab-pane fade show active" id="info">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-0 pb-0">
                                <h4 class="mb-1">Thông tin cá nhân</h4>
                                <p class="text-muted mb-0">Cập nhật thông tin tài khoản của bạn</p>
                            </div>
                            <div class="card-body">
                                <form action="" method="POST" id="pro-info">
                                    @csrf
                                    <div class="row">
                                        <div class="col-md-6 mb-3 form-group">
                                            <label class="form-label">Họ và tên</label>
                                            <input type="text" class="form-control" name="fullname" rules='required'  value="{{ $customer->name }}">
                                            <span class="form-message"></span>
                                        </div>
                                        <div class="col-md-6 mb-3 form-group">
                                            <label class="form-label">Số điện thoại</label>
                                            <input type="tel" class="form-control" name="phone" rules='required|phone' value="{{ $customer->phone }}">
                                            <span class="form-message"></span>
                                        </div>
                                    </div>
                                    <div class="mb-3 form-group">
                                        <label class="form-label">Email</label>
                                        <input type="email" class="form-control" name="email" value="{{ $customer->email }}" rules="required|email" readonly>
                                        <span class="form-message"></span>
                                        <div class="form-text">Email không thể thay đổi</div>
                                    </div>
                                    <div class="mb-3 form-group">
                                        <label class="form-label">Địa chỉ</label>
                                        <textarea class="form-control" name="address" rules='required' rows="3">{{ $customer->address }}</textarea>
                                        <span class="form-message"></span>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Đổi mật khẩu -->
                    <div class="tab-pane fade" id="password">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-0 pb-0">
                                <h4 class="mb-1">Đổi mật khẩu</h4>
                                <p class="text-muted mb-0">Cập nhật mật khẩu để bảo mật tài khoản</p>
                            </div>
                            <div class="card-body">
                                <form action="" method="POST" id="pro-change-password">
                                    <div class="mb-3 form-group">
                                        <label class="form-label">Mật khẩu hiện tại</label>
                                        <input type="password" class="form-control" name="old_password" rules="required|min:6" placeholder="Nhập mật khẩu hiện tại">
                                        <span class="form-message"></span>
                                    </div>
                                    <div class="mb-3 form-group">
                                        <label class="form-label">Mật khẩu mới</label>
                                        <input type="password" class="form-control" name="password" rules="required|min:6" placeholder="Nhập mật khẩu mới">
                                        <span class="form-message"></span>
                                    </div>
                                    <div class="mb-3 form-group">
                                        <label class="form-label">Xác nhận mật khẩu mới</label>
                                        <input type="password" class="form-control" name="password_confirmation" rules="required|confirm:input[name='password']" placeholder="Nhập lại mật khẩu mới">
                                        <span class="form-message"></span>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Đổi mật khẩu</button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Lịch sử đặt bàn -->
                    <div class="tab-pane fade" id="bookings">
                        <div class="card border-0">
                            <div class="card-header bg-white border-0 pb-0">
                                <h4 class="mb-1">Lịch sử đặt bàn</h4>
                                <p class="text-muted mb-0">Xem lịch sử đặt bàn của bạn</p>
                            </div>
                            <div class="card-body">
                                @php
                                    // Phân loại trạng thái booking theo status trong DB
                                    // 0: chờ xác nhận, 1: đã xác nhận, 2: hoàn thành, 3: đã hủy
                                    $now = \Illuminate\Support\Carbon::now();
                                @endphp

                                @forelse ($bookings as $booking)
                                    @php
                                        $isPast   = \Illuminate\Support\Carbon::parse($booking->time)->lt($now);
                                        $badgeClass = match((int) $booking->status) {
                                            0       => 'bg-warning text-dark',
                                            1       => $isPast ? 'bg-success' : 'bg-info text-dark',
                                            2       => 'bg-success',
                                            3       => 'bg-danger',
                                            default => 'bg-secondary',
                                        };
                                        $statusLabel = match((int) $booking->status) {
                                            0       => 'Chờ xác nhận',
                                            1       => $isPast ? 'Đã hoàn thành' : 'Sắp tới',
                                            2       => 'Đã hoàn thành',
                                            3       => 'Đã hủy',
                                            default => 'Khác',
                                        };
                                        $alertClass = match((int) $booking->status) {
                                            3       => 'alert-danger',
                                            2       => 'alert-success',
                                            default => 'alert-info',
                                        };
                                        $cancelDeadline = \Illuminate\Support\Carbon::parse($booking->time)->subHour(); //subhour -1 tiếng
                                        $canCancel = in_array((int) $booking->status, [0, 1]) && $now->lt($cancelDeadline); //now lessthan cancelDeadline
                                    @endphp
                                    <div class="alert {{ $alertClass }} mb-3 booking-item " data-booking-id="{{ $booking->id_book }}">
                                        <div class="d-flex justify-content-between align-items-center flex-wrap">
                                            <div>
                                                <strong>
                                                    <i class="fa-solid fa-store me-2"></i>
                                                    {{ $booking->branch->name ?? 'Chi nhánh' }}
                                                </strong>
                                                <div class="small text-muted mt-1">
                                                    <i class="fa-solid fa-calendar me-1"></i>
                                                    {{ \Illuminate\Support\Carbon::parse($booking->time)->format('d/m/Y - H:i') }}
                                                    <span class="mx-2">|</span>
                                                    <i class="fa-solid fa-chair me-1"></i>
                                                    {{ $booking->table->table_name ?? ($booking->tableType->name ?? 'Bàn') }}
                                                    @if ($booking->tableType)
                                                        <span class="mx-2">|</span>
                                                        <i class="fa-solid fa-tag me-1"></i>{{ $booking->tableType->name }}
                                                    @endif
                                                    <span class="mx-2">|</span>
                                                    <i class="fa-solid fa-user me-1"></i>{{ $booking->guest_name }}
                                                </div>
                                            </div>
                                            <div class="text-end">
                                                <span class="badge {{ $badgeClass }} status-badge">{{ $statusLabel }}</span>
                                                <div class="mt-2">
                                                    @if ($canCancel)
                                                        <button class="btn btn-sm btn-danger btn-cancel-booking p-2"
                                                            data-url="{{ route('profile.booking.cancel', $booking->id_book) }}"
                                                            data-name="{{ $booking->branch->name ?? 'bàn này' }}">
                                                            <i class="fa-solid fa-xmark me-1"></i>Hủy
                                                        </button>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="card border-0 shadow-sm">
                                        <div class="card-body text-center py-5 text-muted">
                                            <i class="fa-solid fa-calendar-xmark fs-1 mb-2"></i>
                                        <p class="mb-0">Bạn chưa có lịch sử đặt bàn nào.</p>
                                        </div>
                                    </div>
                                @endforelse
                            </div>

                            {{-- Phân trang cho lịch sử đặt bàn, giữ nguyên hash #bookings --}}
                            @if ($bookings->hasPages())
                                <div class="d-flex justify-content-center mt-3">
                                    {{ $bookings->fragment('bookings')->links() }}
                                </div>
                            @endif

                        </div>
                    </div>

                    <!-- Hóa đơn -->
                    <div class="tab-pane fade" id="invoices">
                        @forelse ($bills as $bill)
                            @php
                                // Gom các order detail theo món + trạng thái, cộng dồn số lượng
                                $grouped = $bill->order->orderDetails
                                    ->groupBy(fn ($d) => $d->id_food . '_' . $d->status)
                                    ->map(function ($row) {
                                        $first = $row->first();
                                        $first->quantity = $row->sum('quantity');
                                        return $first;
                                    })
                                    ->values();
                                // $subtotal = $grouped->sum(fn ($d) => ((int) $d->quantity) * ((float) $d->price));
                                
                                // $total    = $subtotal + $tax;
                                $payTime  = $bill->pay_time
                                    ? \Illuminate\Support\Carbon::parse($bill->pay_time)->format('d/m/Y H:i')
                                    : '—';
                                $payLabel = match((int) $bill->status) {
                                    2       => 'Đã thanh toán',
                                    1       => 'Chờ thanh toán',
                                    3       => 'Đã hủy',
                                    default => 'Khác',
                                };
                                $payBadge = match((int) $bill->status) {
                                    2       => 'bg-success',
                                    1       => 'bg-warning text-dark',
                                    default => 'bg-secondary',
                                };
                                
                            @endphp
                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap">
                                    <div>
                                        <h5 class="mb-1">#HD-{{ str_pad($bill->id_bill, 6, '0', STR_PAD_LEFT) }}</h5>
                                        <p class="text-muted mb-0 small">
                                            <i class="fa-solid fa-calendar me-1"></i>{{ $payTime }}
                                            <span class="mx-2">|</span>
                                            <i class="fa-solid fa-store text-warning me-1"></i>
                                            {{ $bill->order->branch->name ?? 'Chi nhánh' }}
                                            @if ($bill->order->table)
                                                <span class="mx-2">|</span>
                                                <i class="fa-solid fa-chair me-1"></i>{{ $bill->order->table->table_name }}
                                            @endif
                                        </p>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge {{ $payBadge }} mb-2 d-block">{{ $payLabel }}</span>
                                        <small class="text-muted d-block">{{ $bill->pay_method === 'cash' ? 'Tiền mặt' : ($bill->pay_method === 'transfer' ? 'Chuyển khoản' : '-' ) }}</small>
                                    </div>
                                </div>
                                <div class="card-body pt-0">
                                    <table class="table table-sm mb-2">
                                        <thead class="table-light border-0">
                                            <tr>
                                                <th class="border-0">STT</th>
                                                <th class="border-0">Tên món</th>
                                                <th class="border-0 text-end">SL</th>
                                                <th class="border-0 text-end">Đơn giá</th>
                                                <th class="border-0 text-end">Thành tiền</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($grouped as $i => $detail)
                                                <tr>
                                                    <td>{{ $i + 1 }}</td>
                                                    <td>{{ $detail->menuItem->name ?? 'Món #' . $detail->id_food }}</td>
                                                    <td class="text-end">{{ $detail->quantity }}</td>
                                                    <td class="text-end">{{ number_format($detail->price, 0, ',', '.') }}đ</td>
                                                    <td class="text-end">{{ number_format(((int) $detail->quantity) * ((float) $detail->price), 0, ',', '.') }}đ</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5" class="text-center text-muted">Chưa có món nào.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                    <div class="border-top pt-3 mt-2">
                                        <div class="d-flex justify-content-between align-items-center flex-wrap">
                                            {{-- <div class="small text-muted">
                                                Tạm tính: <strong>{{ number_format($subtotal, 0, ',', '.') }}đ</strong>
                                                <span class="mx-2">|</span>
                                                Thuế VAT (8%): <strong>{{ number_format($tax, 0, ',', '.') }}đ</strong>
                                            </div> --}}
                                            <div>
                                                <span class="text-muted me-3">Tổng cộng:</span>
                                                <span class="fs-5 fw-bold text-warning">{{ number_format($bill->total, 0, ',', '.') }}đ</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="card border-0 shadow-sm">
                                <div class="card-body text-center py-5 text-muted">
                                    <i class="fa-solid fa-receipt fs-1 mb-2"></i>
                                    <p class="mb-0">Bạn chưa có hóa đơn nào.</p>
                                </div>
                            </div>
                        @endforelse
                    </div>

                    <!-- Tích điểm -->
                    <div class="tab-pane fade" id="points">
                        <!-- Points Summary -->
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <div class="card border-0 bg-warning text-dark">
                                    <div class="card-body text-center">
                                        <i class="fa-solid fa-coins fs-1 mb-2"></i>
                                        <h2 class="mb-0">1,250</h2>
                                        <p class="mb-0">Điểm hiện có</p>
                                        <small class="opacity-75">~ 125.000đ giá trị</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card border-0 shadow-sm">
                                    <div class="card-body text-center">
                                        <i class="fa-solid fa-arrow-up fs-2 text-danger mb-2"></i>
                                        <h3 class="mb-0">350</h3>
                                        <p class="text-muted mb-0">Điểm đã sử dụng</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card border-0 shadow-sm">
                                    <div class="card-body text-center">
                                        <i class="fa-solid fa-arrow-down fs-2 text-success mb-2"></i>
                                        <h3 class="mb-0">1,600</h3>
                                        <p class="text-muted mb-0">Điểm tích lũy</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- History -->
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-white">
                                <h5 class="mb-0">Lịch sử tích điểm</h5>
                            </div>
                            <div class="card-body p-0">
                                <div class="list-group list-group-flush">
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class="fa-solid fa-plus text-success me-2"></i>
                                            <span>Tích điểm từ hóa đơn #HD-20260715001</span>
                                            <div class="small text-muted">15/07/2026</div>
                                        </div>
                                        <span class="badge bg-success fs-6">+328</span>
                                    </div>
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class="fa-solid fa-minus text-danger me-2"></i>
                                            <span>Đổi voucher giảm 20%</span>
                                            <div class="small text-muted">12/07/2026</div>
                                        </div>
                                        <span class="badge bg-danger fs-6">-200</span>
                                    </div>
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class="fa-solid fa-plus text-success me-2"></i>
                                            <span>Tích điểm từ hóa đơn #HD-20260710002</span>
                                            <div class="small text-muted">10/07/2026</div>
                                        </div>
                                        <span class="badge bg-success fs-6">+134</span>
                                    </div>
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class="fa-solid fa-plus text-success me-2"></i>
                                            <span>Tích điểm từ đặt bàn</span>
                                            <div class="small text-muted">05/07/2026</div>
                                        </div>
                                        <span class="badge bg-success fs-6">+50</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Rewards -->
                        {{-- <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white">
                                <h5 class="mb-0">Đổi quà tặng</h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <div class="card h-100 border">
                                            <div class="card-body text-center">
                                                <div class="avatar-circle mx-auto mb-2">
                                                    <i class="fa-solid fa-ticket text-white"></i>
                                                </div>
                                                <h6>Voucher giảm 10%</h6>
                                                <p class="text-warning small mb-2">500 điểm</p>
                                                <button class="btn btn-sm btn-warning">Đổi</button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="card h-100 border">
                                            <div class="card-body text-center">
                                                <div class="avatar-circle mx-auto mb-2">
                                                    <i class="fa-solid fa-ticket text-white"></i>
                                                </div>
                                                <h6>Voucher giảm 20%</h6>
                                                <p class="text-warning small mb-2">800 điểm</p>
                                                <button class="btn btn-sm btn-warning">Đổi</button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="card h-100 border">
                                            <div class="card-body text-center">
                                                <div class="avatar-circle mx-auto mb-2 bg-secondary">
                                                    <i class="fa-solid fa-utensils text-white"></i>
                                                </div>
                                                <h6>Miễn phí 1 món</h6>
                                                <p class="text-muted small mb-2">1,200 điểm</p>
                                                <button class="btn btn-sm btn-outline-secondary" disabled>Hết điểm</button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="card h-100 border">
                                            <div class="card-body text-center">
                                                <div class="avatar-circle mx-auto mb-2 bg-secondary">
                                                    <i class="fa-solid fa-gift text-white"></i>
                                                </div>
                                                <h6>Quà tặng đặc biệt</h6>
                                                <p class="text-muted small mb-2">2,000 điểm</p>
                                                <button class="btn btn-sm btn-outline-secondary" disabled>Hết điểm</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div> --}}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Avatar Circle */
.avatar-circle {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, #f5a623, #e08c0f);
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Background & Container */
.py-4 {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    min-height: 100vh;
}

/* Card Enhancements */
.card {
    border-radius: 16px;
    overflow: hidden;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.card:hover {
    transform: translateY(-2px);
}

.card-header {
    border-bottom: 2px solid #f8f9fa;
    padding: 1.25rem 1.5rem;
}

.card-body {
    padding: 1.5rem;
}

/* Sidebar Avatar */
.avatar-circle-lg {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    background: linear-gradient(135deg, #f5a623, #e08c0f, #d4770c);
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 8px 25px rgba(245, 166, 35, 0.4);
    animation: pulse 2s ease-in-out infinite;
}

@keyframes pulse {
    0%, 100% { box-shadow: 0 8px 25px rgba(245, 166, 35, 0.4); }
    50% { box-shadow: 0 8px 35px rgba(245, 166, 35, 0.6); }
}

/* List Group Items */
.list-group-item {
    border: none;
    padding: 1rem 1.25rem;
    transition: all 0.2s ease;
    border-radius: 0;
    font-weight: 500;
}

.list-group-item:first-child {
    border-radius: 0;
}

.list-group-item:hover {
    background: linear-gradient(90deg, rgba(245, 166, 35, 0.1), transparent);
    padding-left: 1.5rem;
}

.list-group-item.active {
    background: linear-gradient(90deg, #f5a623, #e08c0f);
    border: none;
    color: #fff;
}

.list-group-item.active i {
    color: #fff;
}

/* Form Controls */
.form-control {
    border-radius: 10px;
    padding: 0.75rem 1rem;
    border: 2px solid #e9ecef;
    transition: all 0.3s ease;
}

.form-control:focus {
    border-color: #f5a623;
    box-shadow: 0 0 0 4px rgba(245, 166, 35, 0.15);
}

.form-control[readonly] {
    background: #f8f9fa;
    border-color: #e9ecef;
}

.form-label {
    font-weight: 600;
    color: #495057;
    margin-bottom: 0.5rem;
}

/* Buttons */
.btn {
    border-radius: 10px;
    padding: 0.6rem 1.25rem;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
}

.btn-primary {
    background: linear-gradient(135deg, #f5a623, #e08c0f);
    border: none;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #e08c0f, #d4770c);
}

.btn-warning {
    background: linear-gradient(135deg, #f5a623, #e08c0f);
    border: none;
    color: #fff;
}

.btn-outline-secondary:hover {
    background: #6c757d;
    color: #fff;
}

/* Badge */
.badge {
    padding: 0.5rem 0.85rem;
    font-weight: 600;
    border-radius: 8px;
}

/* Table */
.table th {
    font-weight: 600;
    color: #495057;
    text-transform: uppercase;
    font-size: 0.8rem;
    letter-spacing: 0.5px;
    border-bottom: 2px solid #f8f9fa;
}

.table td {
    vertical-align: middle;
    padding: 1rem 0.75rem;
}

.table-sm td,
.table-sm th {
    padding: 0.5rem 0.75rem;
}

.table tbody tr:hover {
    background: rgba(245, 166, 35, 0.05);
}

.table thead th {
    background: linear-gradient(135deg, #f8f9fa, #e9ecef);
}

.table-hover tbody tr:hover {
    background: rgba(245, 166, 35, 0.05);
}

/* Alerts (Booking Items) */
.alert {
    border: none;
    border-radius: 12px;
    padding: 1rem 1.25rem;
    border-left: 4px solid;
}

.alert-info {
    background: linear-gradient(135deg, #e3f2fd, #bbdefb);
    border-left-color: #0d6efd;
}

.alert-success {
    background: linear-gradient(135deg, #d4edda, #c3e6cb);
    border-left-color: #198754;
}

/* Points Card */
.card.bg-warning {
    background: linear-gradient(135deg, #f5a623, #e08c0f) !important;
    box-shadow: 0 8px 25px rgba(245, 166, 35, 0.4);
}

.card.bg-warning:hover {
    transform: scale(1.02);
}

/* Reward Cards */
.card.h-100 {
    transition: all 0.3s ease;
}

.card.h-100:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
}

.card.h-100 .avatar-circle {
    transition: transform 0.3s ease;
}

.card.h-100:hover .avatar-circle {
    transform: scale(1.1);
}

/* Animations */
.tab-pane.fade {
    animation: fadeInUp 0.4s ease;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Hover Effects */
.list-group-item-action:active {
    background: linear-gradient(90deg, #f5a623, #e08c0f);
}

/* Points Badge Animation */
.badge.bg-warning {
    animation: glow 2s ease-in-out infinite;
}

@keyframes glow {
    0%, 100% { box-shadow: 0 0 5px rgba(245, 166, 35, 0.5); }
    50% { box-shadow: 0 0 20px rgba(245, 166, 35, 0.8); }
}

/* Table Responsive */
.table-responsive {
    border-radius: 12px;
    overflow: hidden;
}

/* Custom Scrollbar */
::-webkit-scrollbar {
    width: 8px;
}

::-webkit-scrollbar-track {
    background: #f1f1f1;
}

::-webkit-scrollbar-thumb {
    background: linear-gradient(#f5a623, #e08c0f);
    border-radius: 4px;
}

/* Responsive */
@media (max-width: 768px) {
    .card-body {
        padding: 1rem;
    }

    .list-group-item {
        padding: 0.75rem 1rem;
    }

    .alert {
        padding: 0.75rem 1rem;
    }
}
</style>

<script>
    // Xử lý hủy đặt bàn từ trang profile
    document.addEventListener('DOMContentLoaded', function () {
        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // Đồng bộ tab đang active với hash trên URL (ví dụ: ?page=2#bookings)
        // để sau khi phân trang, trang không bị nhảy về tab mặc định #info.
        const hash = window.location.hash;
        if (hash) {
            const triggerEl = document.querySelector('.list-group-item[href="' + hash + '"]');
            if (triggerEl && typeof bootstrap !== 'undefined') {
                const tab = new bootstrap.Tab(triggerEl);
                tab.show();
            }
        }

        document.querySelectorAll('.btn-cancel-booking').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                const url  = this.dataset.url;
                const name = this.dataset.name || 'bàn này';

                if (!confirm('Bạn có chắc muốn hủy đặt bàn tại ' + name + '?')) {
                    return;
                }

                fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Content-Type': 'application/json'
                    },
                })
                .then(res => res.json().then(data => ({ status: res.status, data })))
                .then(({ status, data }) => {
                    if (data.status === 'success') {
                        if (typeof toastr !== 'undefined') {
                            toastr.success(data.message || 'Đã hủy đặt bàn');
                        } else {
                            alert(data.message || 'Đã hủy đặt bàn');
                        }
                        // Reload trang để render lại trạng thái mới
                        setTimeout(() => window.location.reload(), 600);
                    } else {
                        if (typeof toastr !== 'undefined') {
                            toastr.error(data.message || 'Hủy đặt bàn thất bại');
                        } else {
                            alert(data.message || 'Hủy đặt bàn thất bại');
                        }
                    }
                })
                .catch(err => {
                    console.error(err);
                    if (typeof toastr !== 'undefined') {
                        toastr.error('Có lỗi xảy ra, vui lòng thử lại');
                    } else {
                        alert('Có lỗi xảy ra, vui lòng thử lại');
                    }
                });
            });
        });
    });
</script>
@endsection