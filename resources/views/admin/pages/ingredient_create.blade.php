@extends('layouts.admin_home')

@section('title', 'Nguyên Liệu')

@section('content')

            <div id="content">

                @include('admin.partials.topbar')
                

                <div class="container-fluid">

                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Thêm nguyên liệu mới</h1>
                        <a href="{{ route('admin.ingredient') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Quay lại
                        </a>
                    </div>

                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Thông tin nguyên liệu</h6>
                        </div>
                        <div class="card-body">
                            <form id="formCreateIngre">
                                @csrf
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="name">Tên nguyên liệu <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="name" name="name" placeholder="Nhập tên" rules="required">
                                            <span class="form-message"></span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="unit">Đơn vị <span class="text-danger">*</span></label>
                                            <input type="text" placeholder="Nhập đơn vị" class="form-control" id="unit" name="unit"  rules="required">
                                            <span class="form-message"></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="min">Mức cảnh báo <span class="text-danger">*</span></label>
                                            <input type="text" placeholder="Nhập mức cảnh báo" class="form-control" id="min" name="min" rules="required">
                                            <span class="form-message"></span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="status">Trạng thái<span class="text-danger">*</span></label>
                                            <select name="status" class="form-control"  id="status" rules="required">
                                                <option value="1" selected>Đang sử dụng</option>
                                                <option value="0">Ngừng sử dụng</option>
                                            </select>
                                            <span class="form-message"></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Lưu
                                    </button>
                                    <a href="{{ route('admin.ingredient') }}" class="btn btn-secondary">Hủy</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
            </div>

@endsection
