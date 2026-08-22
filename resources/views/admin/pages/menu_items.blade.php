@extends('layouts.admin_home')

@section('title', 'Quản lý món ăn')

@section('content')

            <div id="content">
                @include('admin.partials.topbar')

                <div class="container-fluid">

                    <!-- Page Heading -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Quản lý món ăn</h1>
                        <div>
                            <a href="{{ route('admin.category') }}" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm mr-2">
                                <i class="fas fa-arrow-left fa-sm text-white-50"></i> Quay lại
                            </a>
                            <a href="{{ route('admin.menuItem.create') }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                                <i class="fas fa-plus fa-sm text-white-50"></i> Thêm món ăn
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

                    <!-- Search and Filter Row -->
                    <div class="card shadow mb-4">
                        <div class="card-body">
                            <form method="GET" action="{{ route('admin.menuItem') }}" class="mb-0">
                                <div class="row">
                                    <div class="col-md-3 mb-2">
                                        <label class="small font-weight-bold">Tìm kiếm</label>
                                        <input type="text" name="search" class="form-control" placeholder="Tên món ăn..." value="{{ request('search') }}">
                                    </div>
                                    <div class="col-md-2 mb-2">
                                        <label class="small font-weight-bold">Danh mục</label>
                                        <select name="category" class="form-control">
                                            <option value="">Tất cả</option>
                                            @foreach($categories as $category)
                                            <option value="{{ $category->id_cate }}" {{ request('category') == $category->id_cate ? 'selected' : '' }}>{{ $category->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2 mb-2">
                                        <label class="small font-weight-bold">Trạng thái</label>
                                        <select name="status" class="form-control">
                                            <option value="">Tất cả</option>
                                            <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Hoạt động</option>
                                            <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>Ngừng bán</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3 mb-2 d-flex align-items-end">
                                        <button type="submit" class="btn btn-primary mr-2">
                                            <i class="fas fa-search"></i> Tìm kiếm
                                        </button>
                                        <a href="{{ route('admin.menuItem') }}" class="btn btn-secondary">
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
                            <h6 class="m-0 font-weight-bold text-primary">Danh sách món ăn</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>STT</th>
                                            <th>Ảnh</th>
                                            <th>Tên món</th>
                                            <th>Danh mục</th>
                                            <th>Giá</th>
                                            <th>Trạng thái</th>
                                            <th class="text-center">Thao tác</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($menuItems as $key => $item)
                                        <tr>
                                            <td>{{ ($menuItems->firstItem() ?? 0) + $key }}</td>
                                            <td>
                                                @if($item->image)
                                                <img src="{{ asset('storage/menu_items/' . $item->image) }}" alt="{{ $item->name }}" style="width: 50px; height: 50px; object-fit: cover;">
                                                @else
                                                <img src="{{ asset('assets/admin/img/no-image.png') }}" alt="No image" style="width: 50px; height: 50px; object-fit: cover;">
                                                @endif
                                            </td>
                                            <td>{{ $item->name }}</td>
                                            <td>{{ $item->category->name ?? 'N/A' }}</td>
                                            <td>{{ number_format($item->price, 0, ',', '.') }} VNĐ</td>
                                            <td>
                                                @switch($item->status)
                                                    @case(0)
                                                        <span class="badge badge-success">Hoạt động</span>
                                                        @break
                                                    @case(1)
                                                        <span class="badge badge-secondary">Ngừng bán</span>
                                                        @break
                                                    @default
                                                        <span class="badge badge-secondary">{{ $item->status }}</span>
                                                @endswitch
                                            </td>
                                            <td class="text-center">
                                                <a href="{{ route('admin.menuItem.show', $item->id_food) }}" class="btn btn-sm btn-info mr-1" title="Xem chi tiết">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.menuItem.edit', $item->id_food) }}" class="btn btn-sm btn-primary mr-1" title="Sửa">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form class="d-inline" action="{{ route('admin.menuItem.destroy', $item->id_food) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" title="Xóa" onclick="return confirm('Bạn có chắc chắn muốn xóa món ăn này?')" {{ ($item->status == 0) ? 'disabled' : '' }} >
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
                                                    <p>Không có dữ liệu món ăn</p>
                                                </div>
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            @if($menuItems->hasPages())
                            <div class="mt-3 d-flex justify-content-end align-items-center">
                                {{-- appends giu lai url --}}
                                {{ $menuItems->appends(request()->query())->links() }}  
                            </div>
                            @endif
                        </div>
                    </div>

                </div>
                <!-- /.container-fluid -->

            </div>
            <!-- End of Main Content -->

@endsection
