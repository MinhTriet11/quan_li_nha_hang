@extends('layouts.admin_home')

@section('title', 'Danh sách phiếu nhập kho')

@section('content')

            <div id="content">

                @include('admin.partials.topbar')

                <div class="container-fluid">

                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Danh sách phiếu nhập kho</h1>
                        <a href="{{ route('admin.import.create') }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                            <i class="fas fa-plus fa-sm text-white-50"></i> Tạo phiếu nhập
                        </a>
                    </div>

                    {{-- Search & filter --}}
                    <div class="card shadow mb-4">
                        <div class="card-body">
                            <form method="GET" action="{{ route('admin.import.index') }}" class="mb-0">
                                <div class="row">
                                    <div class="col-md-4 mb-2">
                                        <label class="small font-weight-bold">Tìm kiếm</label>
                                        <input type="text" name="search" class="form-control" placeholder="Mã phiếu..." value="{{ request('search') }}">
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <label class="small font-weight-bold">Chi nhánh</label>
                                        <select name="branch" class="form-control">
                                            <option value="">Tất cả</option>
                                            @foreach($branches as $branch)
                                                <option value="{{ $branch->id_br }}" {{ request('branch') == $branch->id_br ? 'selected' : '' }}>{{ $branch->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <label class="small font-weight-bold">Trạng thái</label>
                                        <select name="status" class="form-control">
                                            <option value="">Tất cả</option>
                                            <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Chờ duyệt</option>
                                            <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Đã duyệt</option>
                                            <option value="2" {{ request('status') === '2' ? 'selected' : '' }}>Từ chối</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2 mb-2 d-flex align-items-end">
                                        <button type="submit" class="btn btn-primary mr-2">
                                            <i class="fas fa-search"></i> Tìm
                                        </button>
                                        <a href="{{ route('admin.import.index') }}" class="btn btn-secondary">
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
                            <h6 class="m-0 font-weight-bold text-primary">Danh sách phiếu nhập</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>STT</th>
                                            <th>Mã phiếu</th>
                                            <th>Chi nhánh</th>
                                            
                     
                                            <th class="text-right">Tổng tiền</th>
                                            <th class="text-center">Trạng thái</th>
                                            <th>Ngày tạo</th>
                                            <th class="text-center">Thao tác</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($receipts as $key => $receipt)
                                        <tr>
                                            <td>{{ $key + 1 + ($receipts->currentPage() - 1) * $receipts->perPage() }}</td>
                                            <td><strong>#NK{{ $receipt->id_im }}</strong></td>
                                            <td>{{ $receipt->branch->name ?? '—' }}</td>
                                            
                                           
                                            <td class="text-right">{{ number_format($receipt->total) }} đ</td>
                                            <td class="text-center">
                                                @if($receipt->status == 0)
                                                    <span class="badge badge-warning">Chờ duyệt</span>
                                                @elseif($receipt->status == 2)
                                                    <span class="badge badge-danger">Từ chối</span>
                                                @else
                                                    <span class="badge badge-success">Đã duyệt</span>
                                                @endif
                                            </td>
                                            <td>{{ $receipt->created_at ? $receipt->created_at->format('d/m/Y H:i') : '—' }}</td>
                                            <td class="text-center">
                                                <a href="{{ route('admin.import.show', $receipt->id_im) }}" class="btn btn-sm btn-info mr-1" title="Xem chi tiết">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                {{-- @if($receipt->status == 0)
                                                <form action="{{ route('admin.import.reject', $receipt->id_im) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-warning mr-1" title="Từ chối" onclick="return confirm('Bạn có chắc chắn muốn từ chối phiếu nhập này?')">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </form>
                                                @endif --}}
                                                @if($receipt->status == 0)
                                                <form action="{{ route('admin.import.destroy', $receipt->id_im) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" title="Xóa" onclick="return confirm('Bạn có chắc chắn muốn xóa phiếu nhập này?')">
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
                                                    <i class="fas fa-inbox fa-2x mb-2"></i>
                                                    <p>Chưa có phiếu nhập kho nào</p>
                                                </div>
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            @if($receipts->hasPages())
                            <div class="mt-3 d-flex justify-content-end align-items-center">
                                {{ $receipts->appends(request()->query())->links() }}
                            </div>
                            @endif
                        </div>
                    </div>

                </div>
            </div>

@endsection