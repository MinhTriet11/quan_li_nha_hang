@extends('layouts.admin_home')

@section('title', 'Sửa danh mục món ăn')

@section('content')

            <div id="content">
                @include('admin.partials.topbar')

                <div class="container-fluid">

                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Sửa danh mục món ăn</h1>
                        <a href="{{ route('admin.category') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Quay lại
                        </a>
                    </div>

                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Thông tin danh mục</h6>
                        </div>
                        <div class="card-body">
                            <form id="formEditCategory" method="POST" action="{{ route('admin.category.update', $category->id_cate) }}" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="name">Tên danh mục <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="name" name="name" value="{{ $category->name }}" placeholder="Nhập tên danh mục (ví dụ: Món chính, Tráng miệng, Đồ uống...)" rules="required|string">
                                            <span class="form-message"></span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="image">Ảnh danh mục</label>
                                            <input type="file" class="form-control" id="image" name="image" value="{{ $category->image }}" accept="image/*">
                                            <span class="form-message"></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label>Xem trước ảnh</label>
                                            <div id="imagePreview" class="mt-2">
                                                @if($category->image)
                                                <img id="preview-img" src="{{ asset('storage/categories/' . $category->image) }}" alt="Preview" style="max-width: 200px;">
                                                @else
                                                <img id="preview-img" src="" alt="Preview" style="max-width: 200px; display: none;">
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Lưu
                                    </button>
                                    <a href="{{ route('admin.category') }}" class="btn btn-secondary">Hủy</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

@endsection



