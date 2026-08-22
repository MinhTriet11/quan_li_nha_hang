@extends('layouts.admin_home')

@section('title', 'Tạo đặt bàn mới')

@section('content')

            <!-- Main Content -->
            <div id="content">

                @include('admin.partials.topbar')

                <!-- Begin Page Content -->
                <div class="container-fluid">

                    <!-- Page Heading -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Tạo đặt bàn mới</h1>
                        <a href="{{ route('admin.book') }}" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
                            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Quay lại
                        </a>
                    </div>

                    <!-- Form Card -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Thông tin đặt bàn</h6>
                        </div>
                        <div class="card-body">
                            <form id="formCreateBook" method="POST" action="{{ route('admin.book.store') }}">
                                @csrf
                                <div class="row">
                                    <!-- Thông tin khách hàng -->
                                    <div class="col-md-6">
                                        <h5 class="text-primary mb-3">Thông tin khách hàng</h5>

                                        <div class="form-group">
                                            <label for="guest_name">Tên khách hàng <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('guest_name') is-invalid @enderror"
                                                id="guest_name" name="guest_name" rules="required" value="{{ old('guest_name') }}"
                                                placeholder="Nhập tên khách hàng">
                                            {{-- @error('guest_name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror --}}
                                            <span class="form-message"></span>
                                        </div>

                                        <div class="form-group">
                                            <label for="guest_phone">Số điện thoại <span class="text-danger">*</span></label>
                                            <input type="tel" class="form-control @error('guest_phone') is-invalid @enderror"
                                                id="guest_phone" name="guest_phone" rules="required|phone" value="{{ old('guest_phone') }}"
                                                placeholder="Nhập số điện thoại">
                                            {{-- @error('guest_phone')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror --}}
                                            <span class="form-message"></span>
                                        </div>

                                        <div class="form-group">
                                            <label for="guest_email">Email <span class="text-danger">*</span></label>
                                            <input type="email" class="form-control @error('guest_email') is-invalid @enderror"
                                                id="guest_email" name="guest_email" rules="required|email" value="{{ old('guest_email') }}"
                                                placeholder="Nhập email">
                                            {{-- @error('guest_email')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror --}}
                                            <span class="form-message"></span>
                                        </div>
                                    </div>

                                    <!-- Thông tin đặt bàn -->
                                    <div class="col-md-6">
                                        <h5 class="text-primary mb-3">Thông tin đặt bàn</h5>

                                        <div class="form-group">
                                            <label for="id_br">Chi nhánh <span class="text-danger">*</span></label>
                                            <select class="form-control @error('id_br') is-invalid @enderror"
                                                id="id_br" name="id_br" rules="required">
                                                <option value="">-- Chọn chi nhánh --</option>
                                                @foreach($branches as $branch)
                                                    <option value="{{ $branch->id_br }}" {{ old('id_br') == $branch->id_br ? 'selected' : '' }}>
                                                        {{ $branch->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            {{-- @error('id_br')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror --}}
                                            <span class="form-message"></span>
                                        </div>

                                        <div class="form-group">
                                            <label for="date">Ngày đặt <span class="text-danger">*</span></label>
                                            <input type="date" class="form-control @error('date') is-invalid @enderror"
                                                id="date" name="date" rules="required" value="{{ old('date') }}"
                                                min="{{ date('Y-m-d') }}">
                                            {{-- @error('date')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror --}}
                                            <span class="form-message"></span>
                                        </div>

                                        <div class="form-group">
                                            <label for="time">Giờ đặt <span class="text-danger">*</span></label>
                                            <select class="form-control @error('time') is-invalid @enderror"
                                                id="time" name="time" rules="required" disabled>
                                                <option value="">-- Chọn giờ --</option>
                                            </select>
                                            {{-- @error('time')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror --}}
                                            <span class="form-message"></span>
                                        </div>

                                        <div class="form-group">
                                            <label for="id_table">Chọn loại bàn <span class="text-danger">*</span></label>
                                            <select class="form-control @error('id_table') is-invalid @enderror"
                                                id="id_table_type" name="id_table_type" rules="required" disabled>
                                                <option value="">-- Chọn loại bàn --</option>
                                                
                                            </select>
                                            <small class="text-muted">Vui lòng chọn chi nhánh, ngày và giờ trước</small>
                                            {{-- @error('id_table')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror --}}
                                            <span class="form-message"></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mt-4">
                                    <div class="col-12 text-center">
                                        <button type="submit" class="btn btn-primary  px-5">
                                            <i class="fas fa-save mr-2"></i>Tạo đặt bàn
                                        </button>
                                        <a href="{{ route('admin.book') }}" class="btn btn-secondary  px-5 ml-2">
                                            Hủy
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                </div>
                <!-- /.container-fluid -->

            </div>
            <!-- End of Main Content -->

@endsection



