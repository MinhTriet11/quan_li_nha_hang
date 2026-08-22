@extends('layouts.admin_home')

@section('title', 'Danh sách nhà cung cấp')

@section('content')

            <div id="content">

                @include('admin.partials.topbar')

                <div class="container-fluid">

                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Danh sách nhà cung cấp</h1>
                        <a href="{{ route('admin.supplier.create') }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                            <i class="fas fa-plus fa-sm text-white-50"></i> Thêm nhà cung cấp
                        </a>
                    </div>

                    {{-- Flash messages --}}
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

                    {{-- Search & filter --}}
                    <div class="card shadow mb-4">
                        <div class="card-body">
                            <form method="GET" action="{{ route('admin.supplier') }}" class="mb-0">
                                <div class="row">
                                    <div class="col-md-7 mb-2">
                                        <label class="small font-weight-bold">Tìm kiếm</label>
                                        <input type="text" name="search" class="form-control" placeholder="Tên, số điện thoại hoặc email..." value="{{ request('search') }}">
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <label class="small font-weight-bold">Trạng thái</label>
                                        <select name="status" class="form-control">
                                            <option value="">Tất cả</option>
                                            <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Đang hoạt động</option>
                                            <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>Ngưng hoạt động</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2 mb-2 d-flex align-items-end">
                                        <button type="submit" class="btn btn-primary mr-2">
                                            <i class="fas fa-search"></i> Tìm
                                        </button>
                                        <a href="{{ route('admin.supplier') }}" class="btn btn-secondary">
                                            <i class="fas fa-redo"></i>
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    {{-- Table --}}
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Danh sách nhà cung cấp</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>STT</th>
                                            <th>Tên nhà cung cấp</th>
                                            <th>Số điện thoại</th>
                                            <th>Email</th>
                                            <th>Địa chỉ</th>
                                            <th>Trạng thái</th>
                                            <th class="text-center">Thao tác</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($suppliers as $key => $supplier)
                                            @php
                                                $hasImports = $supplier->importReceipts()->exists();
                                            @endphp
                                        <tr>
                                            <td>{{ $key + 1 + ($suppliers->currentPage() - 1) * $suppliers->perPage() }}</td>
                                            <td>{{ $supplier->name }}</td>
                                            <td>{{ $supplier->phone ?? '—' }}</td>
                                            <td>{{ $supplier->email ?? '—' }}</td>
                                            <td>{{ $supplier->address }}</td>
                                            <td>
                                                @if($supplier->status == 1)
                                                    <span class="badge badge-success">Đang hoạt động</span>
                                                @else
                                                    <span class="badge badge-warning">Ngưng hoạt động</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <a href="{{ route('admin.supplier.edit', $supplier->id_su) }}" class="btn btn-sm btn-primary mr-1" title="Sửa">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('admin.supplier.destroy', $supplier->id_su) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" title="{{ $hasImports ? 'Đang có phiếu nhập kho, không thể xóa' : 'Xóa' }}" onclick="return confirm('Bạn có chắc chắn muốn xóa nhà cung cấp này?')" {{ $hasImports ? 'disabled' : '' }}>
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-4">
                                                <div class="text-muted">
                                                    <i class="fas fa-inbox fa-2x mb-2"></i>
                                                    <p>Không có dữ liệu nhà cung cấp</p>
                                                </div>
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            @if($suppliers->hasPages())
                            <div class="mt-3 d-flex justify-content-end align-items-center">
                                {{ $suppliers->appends(request()->query())->links() }}
                            </div>
                            @endif
                        </div>
                    </div>

                </div>
            </div>

@endsection