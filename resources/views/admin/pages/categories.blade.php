@extends('layouts.admin_home')

@section('title', 'Quản lý danh mục món ăn')

@section('content')

            <div id="content">
                @include('admin.partials.topbar')

                <div class="container-fluid">

                    <!-- Page Heading -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Quản lý danh mục món ăn</h1>
                        <div>
                            <a href="{{ route('admin.category.create') }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                                <i class="fas fa-plus fa-sm text-white-50"></i> Thêm danh mục
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
                            <h6 class="m-0 font-weight-bold text-primary">Danh sách danh mục món ăn</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>STT</th>
                                            <th>Ảnh</th>
                                            <th>Tên danh mục</th>
                                            <th>Số món ăn</th>
                                            <th class="text-center">Thao tác</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($categories as $key => $category)
                                        <tr>
                                            <td>{{ $key + 1 + ($categories->currentPage() - 1) * $categories->perPage() }}</td>
                                            <td>
                                                @if($category->image)
                                                <img src="{{ asset('storage/categories/' . $category->image) }}" alt="{{ $category->name }}" style="width: 50px; height: 50px; object-fit: cover;">
                                                @else
                                                <img src="{{ asset('assets/admin/img/no-image.png') }}" alt="No image" style="width: 50px; height: 50px; object-fit: cover;">
                                                @endif
                                            </td>
                                            <td>{{ $category->name }}</td>
                                            <td>
                                                <span class="badge badge-info">{{ $category->menu_items_count }}</span> món
                                            </td>
                                            <td class="text-center">
                                                <a href="{{ route('admin.category.edit', $category->id_cate) }}" class="btn btn-sm btn-primary mr-1" title="Sửa">
                                                    <i class="fas fa-edit"></i>
                                                </a>

                                                <form class="d-inline" action="{{ route('admin.category.destroy', $category->id_cate) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" title="Xóa"
                                                    onclick="return confirm('Bạn có chắc chắn muốn xóa danh mục này?')"
                                                    {{ $category->menu_items_count > 0 ? 'disabled' : '' }}>
                                                    <i class="fas fa-trash"></i>
                                                    </button>

                                                </form>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-4">
                                                <div class="text-muted">
                                                    <i class="fas fa-inbox fa-2x mb-2"></i>
                                                    <p>Không có dữ liệu danh mục món ăn</p>
                                                </div>
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            @if($categories->hasPages())
                            <div class="mt-3 d-flex justify-content-end align-items-center">
                                {{-- appends giu lai url --}}
                                {{ $categories->appends(request()->query())->links() }}  
                            </div>
                            @endif
                        </div>
                    </div>

                </div>
                <!-- /.container-fluid -->

            </div>
            <!-- End of Main Content -->

@endsection


