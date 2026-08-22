@extends('layouts.admin_home')

@section('title', 'Quản lý loại bàn')

@section('content')

            <div id="content">
                @include('admin.partials.topbar')

                <div class="container-fluid">

                    <!-- Page Heading -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Quản lý loại bàn</h1>
                        <div>
                            <a href="{{ route('admin.table') }}" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm mr-2">
                                <i class="fas fa-arrow-left fa-sm text-white-50"></i> Quay lại
                            </a>
                            <a href="{{ route('admin.tableType.create') }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                                <i class="fas fa-plus fa-sm text-white-50"></i> Thêm loại bàn
                            </a>
                        </div>
                    </div>

                    <!-- Alert Messages -->
                    @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <strong>Thành công!</strong> {{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    @endif

                    @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong>Lỗi!</strong> {{ session('error') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    @endif

                    <!-- Table Card -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Danh sách loại bàn</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>STT</th>
                                            <th>Tên loại bàn</th>
                                            <th>Số bàn đang sử dụng</th>
                                            <th class="text-center">Thao tác</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($tableTypes as $key => $type)
                                        <tr>
                                            <td>{{ $key + 1 }}</td>
                                            <td>{{ $type->name }}</td>
                                            <td>
                                                <span class="badge badge-info">{{ $type->tables_count }}</span> bàn
                                            </td>
                                            <td class="text-center">
                                                <a href="{{ route('admin.tableType.edit', $type->id) }}" class="btn btn-sm btn-primary mr-1" title="Sửa">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                
                                                <form class="d-inline" action="{{ route('admin.tableType.destroy', $type->id) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" title="Xóa"
                                                    onclick="return confirm('Bạn có chắc chắn muốn xóa loại bàn này?')"
                                                    {{ $type->tables_count > 0 ? 'disabled' : '' }}>
                                                    <i class="fas fa-trash"></i>
                                                </button>
                            
                                                </form>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-4">
                                                <div class="text-muted">
                                                    <i class="fas fa-inbox fa-2x mb-2"></i>
                                                    <p>Không có dữ liệu loại bàn</p>
                                                </div>
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <!-- Pagination -->
                            @if($tableTypes->hasPages())
                            <div class="mt-3 d-flex justify-content-end align-items-center">
                                {{-- appends giu lai url --}}
                                {{ $tableTypes->appends(request()->query())->links() }}  
                            </div>
                            @endif
                        </div>
                    </div>

                </div>
                <!-- /.container-fluid -->

            </div>
            <!-- End of Main Content -->

@endsection


