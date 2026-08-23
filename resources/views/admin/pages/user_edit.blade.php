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
                                            <label for="id_br">
                                                Chi nhánh
                                                <span class="text-danger d-none" id="id_br_required_mark">*</span>
                                            </label>
                                            <select class="form-control" id="id_br" name="id_br" rules="required">
                                                <option value="">-- Chọn chi nhánh --</option>
                                                @foreach($branches as $branch)
                                                    <option value="{{ $branch->id_br }}" {{ $user->id_br == $branch->id_br ? 'selected' : '' }}>{{ $branch->name }}</option>
                                                @endforeach
                                            </select>
                                            <span class="form-message" id="id_br_error"></span>
                                            <small class="form-text text-muted" id="id_br_hint_optional" style="display:none;">
                                                <i class="fas fa-info-circle"></i> Chức vụ <strong id="id_br_hint_optional_role">này</strong> không cần gán chi nhánh cụ thể.
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
// Đồng bộ trạng thái dropdown chi nhánh theo chức vụ:
//   - admin / warehouse : không bắt buộc -> disable + skip validate + gợi ý "không cần"
//   - manager/chef/staff/waiter/cashier : bắt buộc -> validate required + hiển thị lỗi inline giống các ô khác
//   - các role khác : tuỳ chọn (không required)
//
// Cơ chế: dùng `dataset.noValidate = '1'` để framework validatorRules skip,
// và đăng ký submit listener ở CAPTURE phase để chạy validate TRƯỚC framework.
(function () {
    const form         = document.getElementById('formEditUser');
    const roleSelect   = document.getElementById('id_role');
    const branchSelect = document.getElementById('id_br');
    const requiredMark = document.getElementById('id_br_required_mark');
    const hintOptional = document.getElementById('id_br_hint_optional');
    const hintOptionalRole = document.getElementById('id_br_hint_optional_role');
    const branchGroup  = branchSelect ? branchSelect.closest('.form-group') : null;
    const branchMsg    = branchGroup ? branchGroup.querySelector('.form-message') : null;
    if (!form || !roleSelect || !branchSelect) return;

    const NO_BRANCH_ROLES  = ['admin', 'warehouse'];
    const REQ_BRANCH_ROLES = ['manager', 'chef', 'staff', 'waiter', 'cashier'];
    let branchRequiredNow  = false;

    function clearBranchError() {
        if (branchGroup) branchGroup.classList.remove('invalid');
        if (branchMsg)   branchMsg.innerText = '';
        branchSelect.classList.remove('is-invalid');
    }

    function showBranchError(msg) {
        if (branchGroup) branchGroup.classList.add('invalid');
        if (branchMsg)   branchMsg.innerText = msg;
        branchSelect.classList.add('is-invalid');
    }

    function syncBranchState() {
        const opt = roleSelect.options[roleSelect.selectedIndex];
        const code = opt ? (opt.getAttribute('data-code') || '') : '';
        const roleName = opt ? (opt.textContent || '').trim() : '';

        // Reset UI
        branchSelect.disabled = false;
        branchSelect.classList.remove('bg-light');
        if (requiredMark) requiredMark.classList.add('d-none');
        if (hintOptional) hintOptional.style.display = 'none';
        clearBranchError();

        if (NO_BRANCH_ROLES.includes(code)) {
            branchSelect.value = '';
            branchSelect.disabled = true;
            branchSelect.classList.add('bg-light');
            branchSelect.dataset.noValidate = '1';
            branchRequiredNow = false;
            if (hintOptional) {
                hintOptional.style.display = 'block';
                if (hintOptionalRole) hintOptionalRole.textContent = roleName;
            }
        } else if (REQ_BRANCH_ROLES.includes(code)) {
            delete branchSelect.dataset.noValidate;
            branchRequiredNow = true;
            if (requiredMark) requiredMark.classList.remove('d-none');
            if (!branchSelect.value) {
                showBranchError('Vui lòng không bỏ trống');
            }
        } else {
            delete branchSelect.dataset.noValidate;
            branchRequiredNow = false;
        }
    }

    branchSelect.addEventListener('change', clearBranchError);
    roleSelect.addEventListener('change', syncBranchState);
    syncBranchState(); // chạy lần đầu để áp dụng cho old value

    // Intercept submit ở capture phase
    form.addEventListener('submit', function (e) {
        if (!branchRequiredNow) return;

        if (!branchSelect.value) {
            e.preventDefault();
            e.stopImmediatePropagation();
            showBranchError('Vui lòng không bỏ trống');
            branchSelect.focus();
        }
    }, true);
})();
</script>
@endsection
