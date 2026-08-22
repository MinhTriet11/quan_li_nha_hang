@extends('layouts.admin_home')

@section('title', 'Sửa bàn')

@section('content')

            <div id="content">
                @include('admin.partials.topbar')

                <div class="container-fluid">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Sửa thông tin bàn</h1>
                        <a href="{{ route('admin.table') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Quay lại
                        </a>
                    </div>

                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Thông tin bàn</h6>
                        </div>
                        <div class="card-body">
                            <form id="formEditTable" method="POST" action="{{ route('admin.table.update', $table->id_table) }}">
                                @csrf
                                @method('PUT')
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="table_name">Tên bàn <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="table_name" name="table_name" value="{{ $table->table_name }}" placeholder="Nhập tên bàn" rules="required">
                                            <span class="form-message"></span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="id_br">Chi nhánh <span class="text-danger">*</span></label>
                                            <select class="form-control" id="id_br" name="id_br" rules="required">
                                                <option value="">-- Chọn chi nhánh --</option>
                                                @foreach($branches as $branch)
                                                <option value="{{ $branch->id_br }}" {{ $table->id_br == $branch->id_br ? 'selected' : '' }}>{{ $branch->name }}</option>
                                                @endforeach
                                            </select>
                                            <span class="form-message"></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="table_type_id">Loại bàn <span class="text-danger">*</span></label>
                                            <select class="form-control" id="table_type_id" name="table_type_id" rules="required">
                                                <option value="">-- Chọn loại bàn --</option>
                                                @foreach($tableTypes as $type)
                                                <option value="{{ $type->id }}" {{ $table->table_type_id == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                                                @endforeach
                                            </select>
                                            <span class="form-message"></span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="status">Trạng thái <span class="text-danger">*</span></label>
                                            <select class="form-control" id="status" name="status" rules="required">
                                                @if ($table->status == 0 || $table->status == 1)
                                                    <option value="0" {{ $table->status == 0 ? 'selected' : '' }}>Đang trống</option>
                                                    <option value="1" {{ $table->status == 1 ? 'selected' : '' }}>Bảo trì</option>
                                                @endif
                                                <option value="2" {{ $table->status == 2 ? 'selected' : '' }}>Đang phục vụ</option>
                                            </select>
                                            <span class="form-message"></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-4">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Cập nhật
                                    </button>
                                    <a href="{{ route('admin.table') }}" class="btn btn-secondary">Hủy</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

@endsection


