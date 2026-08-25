@extends('layouts.admin_home')

@section('title', 'Sửa chi nhánh')

@section('content')

            <div id="content">
                @include('admin.partials.topbar')

                <div class="container-fluid">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Sửa thông tin chi nhánh</h1>
                        <a href="{{ route('admin.branch') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Quay lại
                        </a>
                    </div>

                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Thông tin chi nhánh</h6>
                        </div>
                        <div class="card-body">
                            <form id="formEditBranch" method="POST" action="{{ route('admin.branch.update', $branch->id_br) }}" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="name">Tên chi nhánh <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="name" name="name" value="{{ $branch->name }}" placeholder="Nhập tên chi nhánh" rules="required">
                                            <span class="form-message"></span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="status">Trạng thái <span class="text-danger">*</span></label>
                                            <select class="form-control" id="status" name="status" rules="required">
                                                <option value="1" {{ $branch->status == 1 ? 'selected' : '' }}>Đang hoạt động</option>
                                                <option value="0" {{ $branch->status == 0 ? 'selected' : '' }}>Ngưng hoạt động</option>
                                            </select>
                                            <span class="form-message"></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="address">Địa chỉ <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="address" name="address" value="{{ $branch->address }}" placeholder="Nhập địa chỉ" rules="required">
                                            <span class="form-message"></span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="location">Vị trí (Google Maps URL) <span class="text-danger">*</span></label>
                                            <input type="url" class="form-control" id="location" name="location" value="{{ $branch->location }}" placeholder="https://maps.google.com/..." rules="required">
                                            <span class="form-message"></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="image">Ảnh chi nhánh</label>
                                            @if($branch->image)
                                                <div class="mb-2">
                                                    <img src="{{ asset($branch->image) }}" alt="{{ $branch->name }}" style="width:150px;height:100px;object-fit:cover;border-radius:4px;border:1px solid #e3e6f0;">
                                                </div>
                                            @endif
                                            <input type="file" class="form-control-file" id="image" name="image" accept="image/*">
                                            <small class="form-text text-muted">Để trống nếu không muốn thay đổi. jpg, jpeg, png. Tối đa 3MB.</small>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Cập nhật
                                    </button>
                                    <a href="{{ route('admin.branch') }}" class="btn btn-secondary">Hủy</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

@endsection