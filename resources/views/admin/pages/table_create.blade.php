@extends('layouts.admin_home')

@section('title', 'Thêm bàn mới')

@section('content')

            <div id="content">
                @include('admin.partials.topbar')

                <div class="container-fluid">

                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Thêm bàn mới</h1>
                        <a href="{{ route('admin.table') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Quay lại
                        </a>
                    </div>

                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Thông tin bàn</h6>
                        </div>
                        <div class="card-body">
                            <form id="formCreateTable" method="POST" action="{{ route('admin.table.store') }}">
                                @csrf
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="table_name">Tên bàn <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="table_name" name="table_name" placeholder="Nhập tên bàn" rules="required">
                                            <span class="form-message"></span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="id_br">Chi nhánh <span class="text-danger">*</span></label>
                                            <select class="form-control" id="id_br" name="id_br" rules="required">
                                                <option value="">-- Chọn chi nhánh --</option>
                                                @foreach($branches as $branch)
                                                <option value="{{ $branch->id_br }}">{{ $branch->name }}</option>
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
                                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                                                @endforeach
                                            </select>
                                            <span class="form-message"></span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="status">Trạng thái <span class="text-danger">*</span></label>
                                            <select class="form-control" id="status" name="status" rules="required">
                                                <option value="0">Đang trống</option>
                                                <option value="1">Bảo trì</option>
                                                <option value="2">Đang phục vụ</option>

                                            </select>
                                            <span class="form-message"></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Lưu
                                    </button>
                                    <a href="{{ route('admin.table') }}" class="btn btn-secondary">Hủy</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

@endsection

{{-- <script>
$(document).ready(function() {
    $('#formCreateTable').on('submit', function(e) {
        e.preventDefault();

        const form = $(this);
        const formData = {
            table_name: $('#table_name').val().trim(),
            id_br: $('#id_br').val(),
            table_type_id: $('#table_type_id').val(),
            status: $('#status').val(),
            _token: '{{ csrf_token() }}'
        };

        if (!formData.table_name || !formData.id_br || !formData.table_type_id || !formData.status) {
            alert('Vui lòng điền đầy đủ thông tin');
            return;
        }

        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    alert(response.message);
                    window.location.href = '{{ route("admin.table") }}';
                } else {
                    alert(response.message);
                }
            },
            error: function(xhr) {
                let errorMessage = 'Có lỗi xảy ra';
                if (xhr.responseJSON) {
                    if (xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    } else if (xhr.responseJSON.errors) {
                        const errors = xhr.responseJSON.errors;
                        for (const key in errors) {
                            errorMessage = errors[key][0];
                            break;
                        }
                    }
                }
                alert(errorMessage);
            }
        });
    });
});
</script> --}}
