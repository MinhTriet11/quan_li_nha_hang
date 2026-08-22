@extends('layouts.admin_home')

@section('title', 'Sửa nhân viên')

@section('content')

            <div id="content">
                @include('admin.partials.topbar')

                <div class="container-fluid">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Sửa thông tin nhân viên</h1>
                        <a href="{{ route('admin.user') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Quay lại
                        </a>
                    </div>

                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Thông tin nhân viên</h6>
                        </div>
                        <div class="card-body">
                            <form id="formEditUser" method="POST" action="{{ route('admin.user.update', $user->id_user) }}">
                                @csrf
                                @method('PUT')
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="name">Tên nhân viên <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="name" name="name" value="{{ $user->name }}" rules="required">
                                            <span class="form-message"></span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="email">Email <span class="text-danger">*</span></label>
                                            <input type="email" class="form-control" id="email" name="email" value="{{ $user->email }}" rules="required|email">
                                            <span class="form-message"></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="password">Mật khẩu mới</label>
                                            <input type="password" class="form-control" id="password" name="password" placeholder="Để trống nếu không đổi">
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
                                            <input type="text" class="form-control" id="phone" name="phone" value="{{ $user->phone }}" rules="phone">
                                            <span class="form-message"></span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="id_br">Chi nhánh</label>
                                            <select class="form-control" id="id_br" name="id_br">
                                                <option value="">-- Chọn chi nhánh --</option>
                                                @foreach($branches as $branch)
                                                    <option value="{{ $branch->id_br }}" {{ $user->id_br == $branch->id_br ? 'selected' : '' }}>{{ $branch->name }}</option>
                                                @endforeach
                                            </select>
                                            <small class="form-text text-muted" id="id_br_hint" style="display:none;">
                                                <i class="fas fa-info-circle"></i> Chức vụ <strong id="id_br_hint_role">này</strong> không cần gán chi nhánh cụ thể.
                                            </small>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="id_role">Chức vụ <span class="text-danger">*</span></label>
                                            <select class="form-control" id="id_role" name="id_role" rules="required">
                                                <option value="">-- Chọn chức vụ --</option>
                                                @foreach($roles as $role)
                                                    @php
                                                        $codeMap = [1 => 'admin', 2 => 'manager', 3 => 'staff', 4 => 'chef', 5 => 'warehouse'];
                                                        $roleCode = $codeMap[$role->id_role] ?? '';
                                                    @endphp
                                                    <option value="{{ $role->id_role }}" data-code="{{ $roleCode }}" {{ $user->id_role == $role->id_role ? 'selected' : '' }}>{{ $role->name }}</option>
                                                @endforeach
                                            </select>
                                            <span class="form-message"></span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="address">Địa chỉ</label>
                                            <input type="text" class="form-control" id="address" name="address" value="{{ $user->address }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="status">Trạng thái <span class="text-danger">*</span></label>
                                            <select class="form-control" id="status" name="status" rules="required">
                                                <option value="1" {{ $user->status == 1 ? 'selected' : '' }}>Đang hoạt động</option>
                                                <option value="0" {{ $user->status == 0 ? 'selected' : '' }}>Đã khóa</option>
                                            </select>
                                            <span class="form-message"></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Cập nhật
                                    </button>
                                    <a href="{{ route('admin.user') }}" class="btn btn-secondary">Hủy</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

@endsection

@section('js')
<script>
// Disable dropdown chi nhánh khi chọn chức vụ admin hoặc quản lý kho (warehouse)
(function () {
    const roleSelect = document.getElementById('id_role');
    const branchSelect = document.getElementById('id_br');
    const branchHint = document.getElementById('id_br_hint');
    const branchHintRole = document.getElementById('id_br_hint_role');
    if (!roleSelect || !branchSelect) return;

    // Các role_code KHÔNG cần gán chi nhánh
    const NO_BRANCH_ROLES = ['admin', 'warehouse'];

    function syncBranchState() {
        const opt = roleSelect.options[roleSelect.selectedIndex];
        const code = opt ? (opt.getAttribute('data-code') || '') : '';
        const roleName = opt ? (opt.textContent || '').trim() : '';

        if (NO_BRANCH_ROLES.includes(code)) {
            branchSelect.value = '';
            branchSelect.disabled = true;
            branchSelect.classList.add('bg-light');
            if (branchHint) {
                branchHint.style.display = 'block';
                if (branchHintRole) branchHintRole.textContent = roleName;
            }
        } else {
            branchSelect.disabled = false;
            branchSelect.classList.remove('bg-light');
            if (branchHint) branchHint.style.display = 'none';
        }
    }

    roleSelect.addEventListener('change', syncBranchState);
    syncBranchState(); // chạy lần đầu để áp dụng cho old value
})();
</script>
@endsection
