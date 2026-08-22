
@extends('layouts.admin')

@section('title', 'Đăng nhập quản trị')

<style>
    
</style>

@section('content')
<div class="login-bg">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-10 col-lg-12 col-md-9">
                <div class="login-card">
                    <div class="row no-gutters">
                        <!-- Left Side - Image & Brand -->
                        <div class="col-lg-5 login-image-section d-none d-lg-flex flex-column justify-content-center align-items-center">
                            <div class="brand-text">
                                <div class="logo">
                                    <i class="fas fa-fire"></i>
                                </div>
                                <h2>Mây Nướng</h2>
                                <p>Quản lý nhà hàng BBQ</p>
                            </div>
                        </div>

                        <!-- Right Side - Login Form -->
                        <div class="col-lg-7">
                            <div class="p-5">
                                <div class="text-center mb-4 d-lg-none">
                                    <h1 class="h3 text-gray-800 font-weight-bold">
                                        <i class="fas fa-fire text-primary"></i> Mây Nướng
                                    </h1>
                                    <p class="text-muted">Đăng nhập quản trị</p>
                                </div>

                                <div class="text-center mb-4 d-none d-lg-block">
                                    <h4 class="text-gray-800 font-weight-bold">Xin chào!</h4>
                                    <p class="text-muted">Đăng nhập để quản lý nhà hàng</p>
                                </div>

                                <form class="user" id="formLoginAdmin">
                                    @csrf
                                    <!-- Email -->
                                    <div class="form-group mb-4">
                                        <div class="input-wrapper">
                                            <span class="input-icon">
                                                <i class="fas fa-envelope"></i>
                                            </span>
                                            <input type="email" name="email" rules="required|email" class="form-control form-control-custom"
                                                id="exampleInputEmail" placeholder="Email quản trị">
                                        </div>
                                        <span class="form-message"></span>
                                    </div>

                                    <!-- Password -->
                                    <div class="form-group mb-4">
                                        <div class="input-wrapper">
                                            <span class="input-icon">
                                                <i class="fas fa-lock"></i>
                                            </span>
                                            <input type="password" name="password" rules="required|min:6" class="form-control form-control-custom"
                                                id="exampleInputPassword" placeholder="Mật khẩu">
                                        </div>
                                        <span class="form-message"></span>
                                    </div>

                                    <!-- Remember Me -->
                                    {{-- <div class="form-group remember-me mb-4">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="customCheck">
                                            <label class="custom-control-label text-muted" for="customCheck">
                                                Ghi nhớ đăng nhập
                                            </label>
                                        </div>
                                    </div> --}}

                                    <!-- Login Button -->
                                    <button type="submit" class="btn btn-primary btn-login btn-block text-white">
                                        Đăng nhập
                                    </button>

                                    <!-- Divider -->
                                    {{-- <div class="divider-text my-4">
                                        <span>hoặc</span>
                                    </div> --}}

                                    {{-- <!-- Social Login -->
                                    <div class="row">
                                        <div class="col-6">
                                            <a href="#" class="btn btn-social btn-block bg-white text-muted">
                                                <i class="fab fa-google text-danger"></i> Google
                                            </a>
                                        </div>
                                        <div class="col-6">
                                            <a href="#" class="btn btn-social btn-block bg-white text-muted">
                                                <i class="fab fa-facebook text-primary"></i> Facebook
                                            </a>
                                        </div>
                                    </div> --}}
                                </form>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection