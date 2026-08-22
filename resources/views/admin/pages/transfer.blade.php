@extends('layouts.admin_home')

@section('title', 'Danh sách phiếu chuyển kho')

@section('content')

            <div id="content">

                @include('admin.partials.topbar')

                <div class="container-fluid">

                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Danh sách phiếu chuyển kho</h1>
                        <a href="{{ route('admin.transfer.create') }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                            <i class="fas fa-plus fa-sm text-white-50"></i> Tạo phiếu chuyển
                        </a>
                    </div>

                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        </div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        </div>
                    @endif

                    {{-- Bộ lọc --}}
                    <div class="card shadow mb-4">
                        <div class="card-body">
                            <form method="GET" action="{{ route('admin.transfer.index') }}" class="mb-0">
                                <div class="row">
                                    <div class="col-md-3 mb-2">
                                        <label class="small font-weight-bold">Tìm kiếm</label>
                                        <input type="text" name="search" class="form-control" placeholder="Mã phiếu..." value="{{ request('search') }}">
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <label class="small font-weight-bold">Chi nhánh nguồn</label>
                                        <select name="from_branch" class="form-control">
                                            <option value="">Tất cả</option>
                                            @foreach($branches as $branch)
                                                <option value="{{ $branch->id_br }}" {{ request('from_branch') == $branch->id_br ? 'selected' : '' }}>{{ $branch->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <label class="small font-weight-bold">Chi nhánh đích</label>
                                        <select name="to_branch" class="form-control">
                                            <option value="">Tất cả</option>
                                            @foreach($branches as $branch)
                                                <option value="{{ $branch->id_br }}" {{ request('to_branch') == $branch->id_br ? 'selected' : '' }}>{{ $branch->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2 mb-2">
                                        <label class="small font-weight-bold">Trạng thái</label>
                                        <select name="status" class="form-control">
                                            <option value="">Tất cả</option>
                                            <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Chờ duyệt</option>
                                            <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Đã duyệt</option>
                                            <option value="2" {{ request('status') === '2' ? 'selected' : '' }}>Từ chối</option>
                                        </select>
                                    </div>
                                    <div class="col-md-1 mb-2 d-flex align-items-end">
                                        <button type="submit" class="btn btn-primary mr-1" title="Tìm"><i class="fas fa-search"></i></button>
                                        <a href="{{ route('admin.transfer.index') }}" class="btn btn-secondary" title="Làm mới"><i class="fas fa-redo"></i></a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    {{-- Bảng --}}
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Danh sách phiếu chuyển kho</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>STT</th>
                                            <th>Mã phiếu</th>
                                            <th>Chi nhánh nguồn</th>
                                            <th>Chi nhánh đích</th>
                                            <th>Người tạo</th>
                                            <th class="text-center">Trạng thái</th>
                                            <th>Ngày tạo</th>
                                            <th class="text-center">Thao tác</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($transfers as $key => $transfer)
                                            <tr>
                                                <td>{{ $transfers->firstItem() + $key }}</td>
                                                <td><strong>#CK{{ $transfer->id_tran }}</strong></td>
                                                <td>
                                                    <span class="badge badge-light border text-dark">
                                                        <i class="fas fa-arrow-right text-danger mr-1"></i>
                                                        {{ $transfer->fromBranch->name ?? '—' }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge badge-light border text-dark">
                                                        <i class="fas fa-arrow-right text-success mr-1"></i>
                                                        {{ $transfer->toBranch->name ?? '—' }}
                                                    </span>
                                                </td>
                                                <td>{{ $transfer->user->name ?? '—' }}</td>
                                                <td class="text-center">
                                                    @if((int)$transfer->status === 0)
                                                        <span class="badge badge-warning">Chờ duyệt</span>
                                                    @elseif((int)$transfer->status === 1)
                                                        <span class="badge badge-success">Đã duyệt</span>
                                                    @else
                                                        <span class="badge badge-danger">Từ chối</span>
                                                    @endif
                                                </td>
                                                <td>{{ $transfer->created_at ? ($transfer->created_at)->format('d/m/Y H:i') : '—' }}</td>
                                                <td class="text-center">
                                                    <a href="{{ route('admin.transfer.show', $transfer->id_tran) }}" class="btn btn-sm btn-info mr-1" title="Xem chi tiết">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    @if((int)$transfer->status === 0)
                                                        <form action="{{ route('admin.transfer.destroy', $transfer->id_tran) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-danger" title="Xóa" onclick="return confirm('Bạn có chắc muốn xóa phiếu chuyển kho này?')">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="9" class="text-center py-4">
                                                    <div class="text-muted">
                                                        <i class="fas fa-exchange-alt fa-2x mb-2"></i>
                                                        <p>Chưa có phiếu chuyển kho nào</p>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            @if($transfers->hasPages())
                            <div class="mt-3 d-flex justify-content-end align-items-center">
                                {{ $transfers->appends(request()->query())->links() }}
                            </div>
                            @endif
                        </div>
                    </div>

                </div>
            </div>

@endsection
