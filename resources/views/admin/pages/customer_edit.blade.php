@extends('layouts.admin_home')

@section('title', 'Sửa khách hàng')

@section('content')

            <div id="content">
                @include('admin.partials.topbar')

                <div class="container-fluid">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Sửa thông tin khách hàng</h1>
                        <a href="{{ route('admin.customer') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Quay lại
                        </a>
                    </div>

                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Thông tin khách hàng</h6>
                        </div>
                        <div class="card-body">
                            <form id="formEditCustomer" method="POST" action="{{ route('admin.customer.update', $customer->id_cus) }}">
                                @csrf
                                @method('PUT')
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="name">Tên khách hàng <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="name" name="name" value="{{ $customer->name }}" rules="required">
                                            <span class="form-message"></span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="email">Email <span class="text-danger">*</span></label>
                                            <input type="email" class="form-control" id="email" name="email" value="{{ $customer->email }}" rules="required|email">
                                            <span class="form-message"></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="password">Mật khẩu mới</label>
                                            <input type="password" class="form-control" id="password" name="password" placeholder="Để trống nếu không đổi" rules="min:6">
                                            <span class="form-message"></span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="password_confirmation">Xác nhận mật khẩu mới</label>
                                            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" placeholder="Nhập lại mật khẩu mới" rules="confirm:#password">
                                            <span class="form-message"></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="phone">Số điện thoại</label>
                                            <input type="text" class="form-control" id="phone" name="phone" value="{{ $customer->phone }}" rules="phone">
                                            <span class="form-message"></span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="status">Trạng thái <span class="text-danger">*</span></label>
                                            <select class="form-control" id="status" name="status" rules="required">
                                                <option value="1" {{ $customer->status == 1 ? 'selected' : '' }}>Đang hoạt động</option>
                                                <option value="0" {{ $customer->status == 0 ? 'selected' : '' }}>Đã khóa</option>
                                            </select>
                                            <span class="form-message"></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="address">Địa chỉ</label>
                                            <input type="text" class="form-control" id="address" name="address" value="{{ $customer->address }}">
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Cập nhật
                                    </button>
                                    <a href="{{ route('admin.customer') }}" class="btn btn-secondary">Hủy</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

@endsection
