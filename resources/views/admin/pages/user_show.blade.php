@extends('layouts.admin_home')

@section('title', 'Chi tiết nhân viên')

@section('content')

            <div id="content">

                @include('admin.partials.topbar')

                <div class="container-fluid">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Chi tiết nhân viên</h1>
                        <div>
                            <a href="{{ route('admin.user.edit', $user->id_user) }}" class="btn btn-primary">
                                <i class="fas fa-edit"></i> Sửa
                            </a>
                            <a href="{{ route('admin.user') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Quay lại
                            </a>
                        </div>
                    </div>

                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Thông tin nhân viên</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <tr>
                                        <th width="200">Tên nhân viên</th>
                                        <td>{{ $user->name }}</td>
                                    </tr>
                                    <tr>
                                        <th>Email</th>
                                        <td>{{ $user->email }}</td>
                                    </tr>
                                    <tr>
                                        <th>Số điện thoại</th>
                                        <td>{{ $user->phone ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Địa chỉ</th>
                                        <td>{{ $user->address ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Chi nhánh</th>
                                        <td>{{ $user->branch->name ?? 'Chưa phân công' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Chức vụ</th>
                                        <td>
                                             @if($user->id_role)    
                            
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
                                    </tr>
                                    <tr>
                                        <th>Trạng thái</th>
                                        <td>
                                            @if($user->status == 1)
                                                <span class="badge badge-success">Đang hoạt động</span>
                                            @else
                                                <span class="badge badge-warning">Đã khóa</span>
                                            @endif
                                        </td>
                                    </tr>
                                    {{-- <tr>
                                        <th>Số đơn đã xử lý</th>
                                        <td>{{ $user->order->count() ?? 0 }}</td>
                                    </tr> --}}
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

@endsection