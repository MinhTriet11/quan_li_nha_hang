@extends('layouts.admin_home')

@section('title', 'Sửa nhà cung cấp')

@section('content')

            <div id="content">
                @include('admin.partials.topbar')

                <div class="container-fluid">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Sửa thông tin nhà cung cấp</h1>
                        <a href="{{ route('admin.supplier') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Quay lại
                        </a>
                    </div>

                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Thông tin nhà cung cấp</h6>
                        </div>
                        <div class="card-body">
                            <form id="formEditSupplier" method="POST" action="{{ route('admin.supplier.update', $supplier->id_su) }}">
                                @csrf
                                @method('PUT')
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="name">Tên nhà cung cấp <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="name" name="name" value="{{ $supplier->name }}" placeholder="Nhập tên nhà cung cấp" rules="required">
                                            <span class="form-message"></span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="phone">Số điện thoại</label>
                                            <input type="text" class="form-control" id="phone" name="phone" value="{{ $supplier->phone }}" placeholder="Nhập số điện thoại">
                                            <span class="form-message"></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="email">Email</label>
                                            <input type="email" class="form-control" id="email" name="email" value="{{ $supplier->email }}" placeholder="example@email.com">
                                            <span class="form-message"></span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="status">Trạng thái <span class="text-danger">*</span></label>
                                            <select class="form-control" id="status" name="status" rules="required">
                                                <option value="1" {{ $supplier->status == 1 ? 'selected' : '' }}>Đang hoạt động</option>
                                                <option value="0" {{ $supplier->status == 0 ? 'selected' : '' }}>Ngưng hoạt động</option>
                                            </select>
                                            <span class="form-message"></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="address">Địa chỉ <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="address" name="address" value="{{ $supplier->address }}" placeholder="Nhập địa chỉ" rules="required">
                                            <span class="form-message"></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Cập nhật
                                    </button>
                                    <a href="{{ route('admin.supplier') }}" class="btn btn-secondary">Hủy</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

@endsection

@section('js')
<script>
document.getElementById('formEditSupplier').addEventListener('submit', function(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    formData.append('_method', 'PUT');

    fetch(form.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(async res => {
        const data = await res.json();
        if (res.ok && data.success) {
            alert(data.message);
            window.location.href = '{{ route("admin.supplier") }}';
        } else {
            alert(data.message || 'Có lỗi xảy ra');
        }
    })
    .catch(err => {
        console.error(err);
        alert('Có lỗi xảy ra');
    });
});
</script>
@endsection