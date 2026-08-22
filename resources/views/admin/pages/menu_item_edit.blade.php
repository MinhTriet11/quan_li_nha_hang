@extends('layouts.admin_home')

@section('title', 'Sửa món ăn')

@section('content')

            <div id="content">
                @include('admin.partials.topbar')

                <div class="container-fluid">

                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Sửa món ăn</h1>
                        <a href="{{ route('admin.menuItem') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Quay lại
                        </a>
                    </div>

                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Thông tin món ăn</h6>
                        </div>
                        <div class="card-body">
                            <form id="formEditMenuItem" method="POST" action="{{ route('admin.menuItem.update', $menuItem->id_food) }}" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="name">Tên món ăn <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="name" name="name" value="{{ $menuItem->name }}" placeholder="Nhập tên món ăn" rules="required">
                                            <span class="form-message"></span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="id_cate">Danh mục <span class="text-danger">*</span></label>
                                            <select class="form-control" id="id_cate" name="id_cate" rules="required">
                                                <option value="">-- Chọn danh mục --</option>
                                                @foreach($categories as $category)
                                                <option value="{{ $category->id_cate }}" {{ $menuItem->id_cate == $category->id_cate ? 'selected' : '' }}>{{ $category->name }}</option>
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
                                            <input type="number" class="form-control" id="price" name="price" value="{{ $menuItem->price }}" placeholder="Nhập giá món ăn" rules="required|min:0">
                                            <span class="form-message"></span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="status">Trạng thái <span class="text-danger">*</span></label>
                                            <select class="form-control" id="status" name="status" rules="required">
                                                <option value="0" {{ $menuItem->status == 0 ? 'selected' : '' }}>Hoạt động</option>
                                                <option value="1" {{ $menuItem->status == 1 ? 'selected' : '' }}>Ngừng bán</option>
                                            </select>
                                            <span class="form-message"></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="description">Mô tả</label>
                                            <textarea class="form-control" id="description" name="description" rows="3" placeholder="Nhập mô tả món ăn">{{ $menuItem->description }}</textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="image">Ảnh món ăn</label>
                                            <input type="file" class="form-control" id="image" name="image" accept="image/*">
                                            <span class="form-message"></span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Xem trước ảnh</label>
                                            <div id="imagePreview" class="mt-2">
                                                @if($menuItem->image)
                                                <img id="preview-img" src="{{ asset('storage/menu_items/' . $menuItem->image) }}" alt="Preview" style="max-width: 200px;">
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
                                    <a href="{{ route('admin.menuItem') }}" class="btn btn-secondary">Hủy</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

@endsection

@section('scripts')
<script>
    // Preview image before upload
    document.getElementById('image').addEventListener('change', function(e) {
        const file = e.target.files[0];
        const previewImg = document.getElementById('preview-img');

        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                previewImg.style.display = 'block';
            }
            reader.readAsDataURL(file);
        } else {
            previewImg.src = '';
            previewImg.style.display = 'none';
        }
    });
</script>
@endsection
