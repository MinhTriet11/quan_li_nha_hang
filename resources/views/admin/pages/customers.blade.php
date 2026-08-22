@extends('layouts.admin_home')

@section('title', 'Danh sách khách hàng')

@section('content')

            <div id="content">

                @include('admin.partials.topbar')

                <div class="container-fluid">

                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Danh sách khách hàng</h1>
                        <a href="{{ route('admin.customer.create') }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                            <i class="fas fa-plus fa-sm text-white-50"></i> Thêm khách hàng mới
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
                            <form method="GET" action="{{ route('admin.customer') }}" class="mb-0">
                                <div class="row">
                                    <div class="col-md-7 mb-2">
                                        <label class="small font-weight-bold">Tìm kiếm</label>
                                        <input type="text" name="search" class="form-control" placeholder="Tên, email hoặc số điện thoại..." value="{{ request('search') }}">
                                    </div>
                                    <div class="col-md-3 mb-2">
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
                                        <a href="{{ route('admin.customer') }}" class="btn btn-secondary">
                                            <i class="fas fa-redo"></i>
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Danh sách khách hàng</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>STT</th>
                                            <th>Tên khách hàng</th>
                                            <th>Email</th>
                                            <th>Số điện thoại</th>
                                            <th>Địa chỉ</th>
                                            <th>Trạng thái</th>
                                            <th class="text-center">Thao tác</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($customers as $key => $customer)
                                        <tr>
                                            <td>{{ $key + 1 + ($customers->currentPage() - 1) * $customers->perPage() }}</td>
                                            <td>{{ $customer->name }}</td>
                                            <td>{{ $customer->email }}</td>
                                            <td>{{ $customer->phone ?? 'N/A' }}</td>
                                            <td>{{ $customer->address ?? 'N/A' }}</td>
                                            <td>
                                                @if($customer->status == 1)
                                                    <span class="badge badge-success">Đang hoạt động</span>
                                                @else
                                                    <span class="badge badge-warning">Đã khóa</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <a href="{{ route('admin.customer.show', $customer->id_cus) }}" class="btn btn-sm btn-info mr-1" title="Xem chi tiết">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.customer.edit', $customer->id_cus) }}" class="btn btn-sm btn-primary mr-1" title="Sửa">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('admin.customer.toggleStatus', $customer->id_cus) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm {{ $customer->status == 1 ? 'btn-warning' : 'btn-success' }}" title="{{ $customer->status == 1 ? 'Khóa tài khoản' : 'Mở khóa' }}" onclick="return confirm('Bạn có chắc chắn muốn {{ $customer->status == 1 ? 'khóa' : 'mở khóa' }} khách hàng này?')">
                                                        @if($customer->status == 1)
                                                            <i class="fas fa-lock"></i>
                                                        @else
                                                            <i class="fas fa-lock-open"></i>
                                                        @endif
                                                    </button>
                                                </form>
                                                @if($customer->Books->count() == 0 && $customer->status == 0)
                                                <form action="{{ route('admin.customer.destroy', $customer->id_cus) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" title="Xóa khách hàng" onclick="return confirm('Bạn có chắc chắn muốn xóa khách hàng này?')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                                @endif
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-4">
                                                <div class="text-muted">
                                                    <i class="fas fa-inbox fa-2x mb-2"></i>
                                                    <p>Không có dữ liệu khách hàng</p>
                                                </div>
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            @if($customers->hasPages())
                            <div class="mt-3 d-flex justify-content-end align-items-center">
                                {{ $customers->appends(request()->query())->links() }}
                            </div>
                            @endif
                        </div>
                    </div>

                </div>
            </div>

@endsection
