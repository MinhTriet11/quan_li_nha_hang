@extends('layouts.admin_home')

@section('title', 'Danh sách đặt bàn')

@section('content')

            <!-- Main Content -->
            <div id="content">

                @include('admin.partials.topbar')

                <!-- Begin Page Content -->
                <div class="container-fluid">

                    <!-- Page Heading -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Danh sách đặt bàn</h1>
                        <a href="{{ route('admin.book.create') }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                            <i class="fas fa-plus fa-sm text-white-50"></i> Tạo đặt bàn mới
                        </a>
                    </div>

                    <!-- Search and Filter Row -->
                    <div class="card shadow mb-4">
                        <div class="card-body">
                            <form method="GET" action="{{ route('admin.book') }}" class="mb-0">
                                <div class="row">
                                    <div class="col-md-3 mb-2">
                                        <label class="small font-weight-bold">Tìm kiếm</label>
                                        <input type="text" name="search" class="form-control" placeholder="Tên khách hàng, SĐT..." value="{{ request('search') }}">
                                    </div>
                                    <div class="col-md-2 mb-2">
                                        <label class="small font-weight-bold">Ngày đặt</label>
                                        <input type="date" name="date" class="form-control" value="{{ request('date') }}">
                                    </div>
                                    <div class="col-md-2 mb-2">
                                        <label class="small font-weight-bold">Trạng thái</label>
                                        <select name="status" class="form-control">
                                            <option value="">Tất cả</option>
                                            {{-- <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Holding</option> --}}
                                            <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>Đã Đặt</option>
                                            <option value="3" {{ request('status') == '2' ? 'selected' : '' }}>Đã Hủy</option>
                                            <option value="2" {{ request('status') == '3' ? 'selected' : '' }}>Hoàn thành</option>
                                            {{-- <option value="4" {{ request('status') == '4' ? 'selected' : '' }}>Expired</option> --}}
                                        </select>
                                    </div>
                                    @if($showBranchSelector ?? true)
                                    <div class="col-md-2 mb-2">
                                        <label class="small font-weight-bold">Chi nhánh</label>
                                        <select name="branch" class="form-control">
                                            <option value="">Tất cả</option>
                                            @foreach($branches as $branch)
                                            <option value="{{ $branch->id_br }}" {{ request('branch') == $branch->id_br ? 'selected' : '' }}>{{ $branch->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @else
                                    <input type="hidden" name="branch" value="{{ $branches->first()->id_br ?? '' }}">
                                    @endif
                                    <div class="col-md-3 mb-2 d-flex align-items-end">
                                        <button type="submit" class="btn btn-primary mr-2">
                                            <i class="fas fa-search"></i> Tìm kiếm
                                        </button>
                                        <a href="{{ route('admin.book') }}" class="btn btn-secondary">
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
                            <h6 class="m-0 font-weight-bold text-primary">Danh sách đặt bàn</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>STT</th>
                                            
                                            <th>Khách hàng</th>
                                            <th>Số điện thoại</th>
                                            <th>Ngày đặt</th>
                                            <th>Giờ</th>
                                            
                                            <th>Bàn</th>
                                            <th>Chi nhánh</th>
                                            <th>Trạng thái</th>
                                            <th>Thao tác</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        
                                        @forelse($books as $key => $book)
                                        <tr>
                                            <td>{{ $key + 1 + ($books->currentPage() - 1) * $books->perPage() }}</td>
                                            
                                            <td>{{ $book->guest_name  }}</td>
                                            <td>{{ $book->guest_phone  }}</td>
                                            <td>{{ \Carbon\Carbon::parse($book->time)->format('d/m/Y') }}</td>
                                            <td>{{ \Carbon\Carbon::parse($book->time)->format('H:i') }}</td>
                                            
                                            <td>
                                                @if($book->table)
                                                <span class="badge badge-info">{{ $book->table->table_name }}</span>
                                                @else
                                                <span class="badge badge-secondary">Chưa gán</span>
                                                @endif
                                            </td>
                                            <td>{{ $book->branch->name ?? 'N/A' }}</td>
                                            <td>
                                                @switch($book->status)
                                                    @case(0)
                                                        <span class="badge badge-warning">Giữ bàn</span>
                                                        @break
                                                    @case(1)
                                                        <span class="badge badge-primary">Đã đặt</span>
                                                        @break
                                                    @case(3)
                                                        <span class="badge badge-danger">Đã hủy</span>
                                                        @break
                                                    @case(2)
                                                        <span class="badge badge-success">Hoàn thành</span>
                                                        @break
                                                    @case(4)
                                                        <span class="badge badge-secondary">Hết hạn</span>
                                                        @break
                                                    @default
                                                        <span class="badge badge-secondary">{{ $book->status }}</span>
                                                @endswitch
                                            </td>
                                            <td>
                                                <a href="{{ route('admin.book.show', $book->id_book) }}" class="btn btn-sm btn-info mr-1" title="Xem chi tiết">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @if ($book->status == 1 || $book->status == 0)
                                                    <a href="{{ route('admin.book.edit', $book->id_book) }}" class="btn btn-sm btn-primary mr-1" title="Sửa">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('admin.book.destroy', $book->id_book) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" title="Xóa" onclick="return confirm('Bạn có chắc chắn muốn xóa đặt bàn này?')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                                @endif
                                                
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="11" class="text-center py-4">
                                                <div class="text-muted">
                                                    <i class="fas fa-inbox fa-2x mb-2"></i>
                                                    <p>Không có dữ liệu đặt bàn</p>
                                                </div>
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            @if($books->hasPages())
                            <div class="mt-3 d-flex justify-content-end align-items-center">
                                {{-- appends giu lai url --}}
                                {{ $books->appends(request()->query())->links() }}  
                            </div>
                            @endif
                        </div>
                    </div>

                </div>
                <!-- /.container-fluid -->

            </div>
            <!-- End of Main Content -->

@endsection
