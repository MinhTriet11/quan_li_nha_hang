@extends('layouts.client')

@section('title', 'Đăng nhập / Đăng ký')

@section('content')
<div class="auth-page">
    <div class="auth-card">
        <div class="auth-intro">
            <h1>Chào mừng bạn đến Mây Nướng</h1>
            <p>Đăng nhập để đặt bàn nhanh hơn, lưu lịch sử món ăn và nhận ưu đãi đặc biệt mỗi ngày.</p>
            <div class="auth-benefits">
                <div class="auth-benefit"><i class="fa-solid fa-calendar-check"></i> Đặt bàn cực nhanh</div>
                <div class="auth-benefit"><i class="fa-solid fa-bell"></i> Nhận khuyến mãi mới</div>
                <div class="auth-benefit"><i class="fa-solid fa-gift"></i> Tích lũy điểm thưởng</div>
            </div>
        </div>

        <div class="auth-form-panel">
            <div class="auth-topbar">
                <h3 class="mb-0">Tài khoản</h3>
                <a href="/">Về trang chủ</a>
            </div>

            
            <ul class="nav nav-tabs" id="authTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="login-tab" data-bs-toggle="tab" data-bs-target="#login-pane" type="button" role="tab">Đăng nhập</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="register-tab" data-bs-toggle="tab" data-bs-target="#register-pane" type="button" role="tab">Đăng ký</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button hidden class="nav-link" id="forgot-tab" data-bs-toggle="tab" data-bs-target="#forgot-pane" type="button" role="tab"></button>
                </li>
                <li class="nav-item" role="presentation">
                    <button hidden class="nav-link" id="reset-tab" data-bs-toggle="tab" data-bs-target="#reset-pane" type="button" role="tab"></button>
                </li>
            </ul>

            <div class="tab-content" id="authTabContent">
                
                <div class="tab-pane fade show active auth-form" id="login-pane" role="tabpanel">
                    <form action="" method="POST" id="formLogin">
                    @csrf
                    <div class="mb-3 form-group">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" rules="required|email" placeholder="you@example.com">
                        <span class="form-message"></span>
                    </div>
                    <div class="mb-3 form-group">
                        <label class="form-label">Mật khẩu</label>
                        <input type="password" class="form-control" name="password" rules="required|min:6" placeholder="••••••••">
                        <span class="form-message"></span>
                    </div>
                    <button class="auth-btn">Đăng nhập</button>
                    <p class="auth-help">
                        <a href="#" data-bs-toggle="tab" class="forgot-item" data-bs-target="#forgot-pane">Quên mật khẩu?</a>
                    </p>
                    <p class="auth-help">Chưa có tài khoản? <a href="" class="register-item" data-bs-toggle="tab" data-bs-target="#register-pane" role="tab">Đăng ký ngay</a></p>
                    </form>
                </div>
                

                
                <div class="tab-pane fade auth-form " id="register-pane" role="tabpanel">
                    <form action="" method="POST" id="formRegister">
                    @csrf
                    <div class="mb-3 form-group">
                        <label class="form-label">Họ và tên</label>
                        <input type="text" class="form-control" name="fullname" rules='required' placeholder="Nguyễn Văn A">
                        <span class="form-message"></span>
                    </div>
                    <div class="mb-3 form-group">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" rules="required|email" placeholder="you@example.com">
                        <span class="form-message"></span>
                    </div>
                    <div class="mb-3 form-group">
                        <label class="form-label">Mật khẩu</label>
                        <input type="password" class="form-control" name="password" rules="required|min:6" placeholder="••••••••">
                        <span class="form-message"></span>
                    </div>
                    <div class="mb-3 form-group">
                        <label class="form-label">Xác nhận mật khẩu</label>
                        <input type="password" class="form-control" name="password_confirmation" rules="required|confirm:input[name='password']" placeholder="••••••••">
                        <span class="form-message"></span>
                    </div>
                    <button class="auth-btn">Tạo tài khoản</button>
                    <p class="auth-help">Đã có tài khoản? <a href="#" class="login-item" data-bs-toggle="tab" data-bs-target="#login-pane">Đăng nhập</a></p>
                    </form>
                </div>
                

                <div class="tab-pane fade auth-form" id="forgot-pane" role="tabpanel">
                    <form action="" method="POST" id="formForgot">
                    @csrf
                    <div class="forgot-box">
                        <p class="forgot-title">Khôi phục mật khẩu</p>
                        <p class="forgot-text">Nhập email của bạn, chúng tôi sẽ gửi hướng dẫn đổi mật khẩu tới hộp thư.</p>
                    </div>
                    <div class="mb-3 form-group">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control"  name="email" rules="required|email" placeholder="you@example.com">
                        <span class="form-message"></span>
                    </div>
                    <button  class="auth-btn">Gửi liên kết</button>  
                    </form>
                </div>

                <div class="tab-pane fade auth-form" id="reset-pane" role="tabpanel">
                    <form action="" method="POST" id="formReset">
                    @csrf
                    <div class="forgot-box">
                        <p class="forgot-title">Đặt lại mật khẩu</p>
                    </div>
                    <div class="mb-3 form-group">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" value="{{ request()->input('email') }}"  name="email" rules="required|email" placeholder="you@example.com">
                        <span class="form-message"></span>
                    </div>

                    <div class="mb-3 form-group">
                        <label class="form-label">Mật khẩu mới</label>
                        <input type="password" class="form-control" name="password" rules="required|min:6" placeholder="••••••••">
                        <span class="form-message"></span>
                    </div>

                    <div class="mb-3 form-group">
                        <label class="form-label">Xác nhận mật khẩu mới</label>
                        <input type="password" class="form-control" name="password_confirmation" rules="required|min:6" placeholder="••••••••">
                        <span class="form-message"></span>
                    </div>

                    <input type="hidden" name="token" value="{{ request()->route('token') }}">

                    <button  class="auth-btn">Xác nhận mật khẩu</button>  
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@if(request()->route('token'))
<script>
document.addEventListener('DOMContentLoaded', function () {
    bootstrap.Tab.getOrCreateInstance(
        document.getElementById('reset-tab')
    ).show();
});
</script>
@endif