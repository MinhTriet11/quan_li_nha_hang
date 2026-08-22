@extends('layouts.admin_home')

@section('title', 'Danh sách bàn')

@section('content')

            <!-- Main Content -->
            <div id="content">

                @include('admin.partials.topbar')

                <!-- Begin Page Content -->
                <div class="container-fluid">

                    <!-- Page Heading -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Danh sách bàn</h1>
                        <div>
                            <a href="{{ route('admin.tableType') }}" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm mr-2">
                                <i class="fas fa-layer-group fa-sm text-white-50"></i> Quản lý loại bàn
                            </a>
                            <a href="{{ route('admin.table.create') }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                                <i class="fas fa-plus fa-sm text-white-50"></i> Thêm bàn mới
                            </a>
                        </div>
                    </div>

                    <!-- Search and Filter Row -->
                    <div class="card shadow mb-4">
                        <div class="card-body">
                            <form method="GET" action="{{ route('admin.table') }}" class="mb-0">
                                <div class="row">
                                    <div class="col-md-3 mb-2">
                                        <label class="small font-weight-bold">Tìm kiếm</label>
                                        <input type="text" name="search" class="form-control" placeholder="Tên bàn..." value="{{ request('search') }}">
                                    </div>
                                    <div class="col-md-2 mb-2">
                                        <label class="small font-weight-bold">Chi nhánh</label>
                                        <select name="branch" class="form-control">
                                            <option value="">Tất cả</option>
                                            @foreach($branches as $branch)
                                            <option value="{{ $branch->id_br }}" {{ request('branch') == $branch->id_br ? 'selected' : '' }}>{{ $branch->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2 mb-2">
                                        <label class="small font-weight-bold">Loại bàn</label>
                                        <select name="table_type" class="form-control">
                                            <option value="">Tất cả</option>
                                            @foreach($tableTypes as $type)
                                            <option value="{{ $type->id }}" {{ request('table_type') == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2 mb-2">
                                        <label class="small font-weight-bold">Trạng thái</label>
                                        <select name="status" class="form-control">
                                            <option value="">Tất cả</option>
                                            <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Đang trống</option>
                                            <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>Bảo trì</option>
                                            <option value="2" {{ request('status') == '2' ? 'selected' : '' }}>Đang phục vụ</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3 mb-2 d-flex align-items-end">
                                        <button type="submit" class="btn btn-primary mr-2">
                                            <i class="fas fa-search"></i> Tìm kiếm
                                        </button>
                                        <a href="{{ route('admin.table') }}" class="btn btn-secondary">
                                            <i class="fas fa-redo"></i> Reset
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Table Card -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Danh sách bàn</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>STT</th>
                                            <th>Tên bàn</th>
                                            <th>Loại bàn</th>
                                            <th>Chi nhánh</th>
                                            <th>Trạng thái</th>
                                            <th class="text-center">Thao tác</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($tables as $key => $table)
                                        <tr>
                                            <td>{{ $key + 1 + ($tables->currentPage() - 1) * $tables->perPage() }}</td>
                                            <td>{{ $table->table_name }}</td>
                                            <td>{{ $table->TableTypes->name ?? 'N/A' }}</td>
                                            <td>{{ $table->branch->name ?? 'N/A' }}</td>
                                            <td>
                                                @switch($table->status)
                                                    @case(0)
                                                        <span class="badge badge-success">Đang hoạt động</span>
                                                        @break
                                                    @case(1)
                                                        <span class="badge badge-warning">Bảo trì</span>
                                                        @break
                                                    @case(2)
                                                        <span class="badge badge-secondary">Đang phục vụ</span>
                                                        @break
                                                    @default
                                                        <span class="badge badge-secondary">{{ $table->status }}</span>
                                                @endswitch
                                            </td>
                                            <td class="text-center">
                                                <a href="{{ route('admin.table.show', $table->id_table) }}" class="btn btn-sm btn-info mr-1" title="Xem chi tiết">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.table.edit', $table->id_table) }}" class="btn btn-sm btn-primary mr-1" title="Sửa">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('admin.table.destroy', $table->id_table) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" title="{{ $table->status == 0 ? 'Chỉ có thể xóa bàn bảo trì' : 'Xóa' }}" onclick="return confirm('Bạn có chắc chắn muốn xóa bàn này?')" {{ $table->status == 0 || $table->status == 2 ? 'disabled' : '' }}>
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
                                                    <p>Không có dữ liệu bàn</p>
                                                </div>
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            @if($tables->hasPages())
                            <div class="mt-3 d-flex justify-content-end align-items-center">
                                {{-- appends giu lai url --}}
                                {{ $tables->appends(request()->query())->links() }}  
                            </div>
                            @endif
                        </div>
                    </div>

                </div>
                <!-- /.container-fluid -->

            </div>
            <!-- End of Main Content -->

@endsection
