@extends('layouts.admin_home')

@section('title', 'Thêm món ăn')

@section('content')

            <div id="content">
                @include('admin.partials.topbar')

                <div class="container-fluid">

                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Thêm món ăn</h1>
                        <a href="{{ route('admin.menuItem') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Quay lại
                        </a>
                    </div>

                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Thông tin món ăn</h6>
                        </div>
                        <div class="card-body">
                            <form id="formCreateMenuItem" method="POST" action="{{ route('admin.menuItem.store') }}" enctype="multipart/form-data">
                                @csrf
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="name">Tên món ăn <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="name" name="name" placeholder="Nhập tên món ăn" rules="required">
                                            <span class="form-message"></span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="id_cate">Danh mục <span class="text-danger">*</span></label>
                                            <select class="form-control" id="id_cate" name="id_cate" rules="required">
                                                <option value="">-- Chọn danh mục --</option>
                                                @foreach($categories as $category)
                                                <option value="{{ $category->id_cate }}">{{ $category->name }}</option>
                                                @endforeach
                                            </select>
                                            <span class="form-message"></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="price">Giá <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control" id="price" name="price" placeholder="Nhập giá món ăn" rules="required|min:0">
                                            <span class="form-message"></span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="status">Trạng thái <span class="text-danger">*</span></label>
                                            <select class="form-control" id="status" name="status" rules="required">
                                                <option value="0">Hoạt động</option>
                                                <option value="1">Ngừng bán</option>
                                            </select>
                                            <span class="form-message"></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="description">Mô tả</label>
                                            <textarea class="form-control" id="description" name="description" rules="required" rows="3" placeholder="Nhập mô tả món ăn"></textarea>
                                            <span class="form-message"></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="image">Ảnh món ăn</label>
                                            <input type="file" class="form-control" id="image" name="image" rules="required" accept="image/*">
                                            <span class="form-message"></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Lưu
                                    </button>
                                    <a href="{{ route('admin.menuItem') }}" class="btn btn-secondary">Hủy</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

@endsection


