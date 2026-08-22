@extends('layouts.admin_home')

@section('title', 'Danh sách nhân viên')

@section('content')

            <div id="content">

                @include('admin.partials.topbar')

                <div class="container-fluid">

                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Danh sách nhân viên</h1>
                        <a href="{{ route('admin.user.create') }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                            <i class="fas fa-plus fa-sm text-white-50"></i> Thêm nhân viên mới
                        </a>
                    </div>

                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    <div class="card shadow mb-4">
                        <div class="card-body">
                            <form method="GET" action="{{ route('admin.user') }}" class="mb-0">
                                <div class="row">
                                    <div class="col-md-5 mb-2">
                                        <label class="small font-weight-bold">Tìm kiếm</label>
                                        <input type="text" name="search" class="form-control" placeholder="Tên, email hoặc số điện thoại..." value="{{ request('search') }}">
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <label class="small font-weight-bold">Chức vụ</label>
                                        <select name="id_role" class="form-control">
                                            <option value="">Tất cả chức vụ</option>
                                            @foreach($roles as $role)
                                                <option value="{{ $role->id_role }}" {{ (string) request('id_role') === (string) $role->id_role ? 'selected' : '' }}>
                                                    {{ $role->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2 mb-2">
                                        <label class="small font-weight-bold">Trạng thái</label>
                                        <select name="status" class="form-control">
                                            <option value="">Tất cả</option>
                                            <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>Đang hoạt động</option>
                                            <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Đã khóa</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2 mb-2 d-flex align-items-end">
                                        <button type="submit" class="btn btn-primary mr-2">
                                            <i class="fas fa-search"></i> Tìm
                                        </button>
                                        <a href="{{ route('admin.user') }}" class="btn btn-secondary">
                                            <i class="fas fa-redo"></i>
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Danh sách nhân viên</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>STT</th>
                                            <th>Tên nhân viên</th>
                                            <th>Email</th>
                                        
                                            <th>Chi nhánh</th>
                                            <th>Chức vụ</th>
                                            <th>Trạng thái</th>
                                            <th class="text-center">Thao tác</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($users as $key => $user)
                                        <tr>
                                            <td>{{ $key + 1 + ($users->currentPage() - 1) * $users->perPage() }}</td>
                                            <td>{{ $user->name }}</td>
                                            <td>{{ $user->email }}</td>
                                            php artisan --version
                                            <td>{{ $user->branch->name ?? 'Chưa phân công' }}</td>
                                            <td>
                                                @if($user->role)    
                            
                                                @switch($user->id_role)
                                                    @case(1)
                                                        <span class="badge badge-warning">Admin</span>
                                                        @break
                                                    @case(2)
                                                        <span class="badge badge-primary">Quản lý</span>
                                                        @break
                                                    @case(3)
                                                        <span class="badge badge-danger">Nhân viên phục vụ</span>
                                                        @break
                                                    @case(4)
                                                        <span class="badge badge-success">Nhân viên bếp</span>
                                                        @break
                                                    @case(5)
                                                        <span class="badge badge-secondary">Nhân viên kho</span>
                                                        @break
                                                    @default
                                                        <span class="badge badge-secondary">—</span>
                                                @endswitch
                                                
                                                @endif
                                            </td>
                                            <td>
                                                @if($user->status == 1)
                                                    <span class="badge badge-success">Đang hoạt động</span>
                                                @else
                                                    <span class="badge badge-warning">Đã khóa</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <a href="{{ route('admin.user.show', $user->id_user) }}" class="btn btn-sm btn-info mr-1" title="Xem chi tiết">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.user.edit', $user->id_user) }}" class="btn btn-sm btn-primary mr-1" title="Sửa">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                @if ($user->id_user !== auth()->id())
                                                    <form action="{{ route('admin.user.toggleStatus', $user->id_user) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm {{ $user->status == 1 ? 'btn-warning' : 'btn-success' }}" title="{{ $user->status == 1 ? 'Khóa tài khoản' : 'Mở khóa' }}" onclick="return confirm('Bạn có chắc chắn muốn {{ $user->status == 1 ? 'khóa' : 'mở khóa' }} nhân viên này?')">
                                                        @if($user->status == 1)
                                                            <i class="fas fa-lock"></i>
                                                        @else
                                                            <i class="fas fa-lock-open"></i>
                                                        @endif
                                                    </button>
                                                </form>
                                                @endif

                                                @php
                                                    $totalHistory = $user->order_count
                                                        + $user->import_receipts_count
                                                        + $user->export_receipts_count
                                                        + $user->stock_check_receipts_count
                                                        + $user->transfers_count;
                                                @endphp
                                                @if($user->id_user !== auth()->id() && $totalHistory == 0)
                                                <form action="{{ route('admin.user.destroy', $user->id_user) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" title="Xóa nhân viên" onclick="return confirm('Bạn có chắc chắn muốn xóa nhân viên này?')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                                @endif
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="8" class="text-center py-4">
                                                <div class="text-muted">
                                                    <i class="fas fa-inbox fa-2x mb-2"></i>
                                                    <p>Không có dữ liệu nhân viên</p>
                                                </div>
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            @if($users->hasPages())
                            <div class="mt-3 d-flex justify-content-end align-items-center">
                                {{ $users->appends(request()->query())->links() }}
                            </div>
                            @endif
                        </div>
                    </div>

                </div>
            </div>

@endsection
