@extends('layouts.admin_home')

@section('title', 'Danh sách phiếu xuất kho')

@section('content')

            <div id="content">

                @include('admin.partials.topbar')

                <div class="container-fluid">

                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Danh sách phiếu xuất kho</h1>
                        <a href="{{ route('admin.export.create') }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                            <i class="fas fa-plus fa-sm text-white-50"></i> Tạo phiếu xuất
                        </a>
                    </div>

                    {{-- Bộ lọc --}}
                    <div class="card shadow mb-4">
                        <div class="card-body">
                            <form method="GET" action="{{ route('admin.export.index') }}" class="mb-0">
                                <div class="row">
                                    <div class="col-md-3 mb-2">
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
                                    <div class="col-md-2 mb-2">
                                        <label class="small font-weight-bold">Lý do</label>
                                        <select name="reason" class="form-control">
                                            <option value="">Tất cả</option>
                                            <option value="sale" {{ request('reason') == 'sale' ? 'selected' : '' }}>Bán hàng (auto)</option>
                                            <option value="waste" {{ request('reason') == 'waste' ? 'selected' : '' }}>Nguyên liệu hư</option>
                                            <option value="broken" {{ request('reason') == 'broken' ? 'selected' : '' }}>Đổ/bể</option>
                                            <option value="adjustment" {{ request('reason') == 'adjustment' ? 'selected' : '' }}>Điều chỉnh</option>
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
                                    <div class="col-md-2 mb-2 d-flex align-items-end">
                                        <button type="submit" class="btn btn-primary mr-2"><i class="fas fa-search"></i> Tìm</button>
                                        <a href="{{ route('admin.export.index') }}" class="btn btn-secondary"><i class="fas fa-redo"></i></a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    {{-- Bảng --}}
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Danh sách phiếu xuất</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>STT</th>
                                            <th>Mã phiếu</th>
                                            <th>Chi nhánh</th>
                                            <th>Người tạo</th>
                                            <th>Lý do</th>
                                            <th class="text-center">Trạng thái</th>
                                            <th>Ngày tạo</th>
                                            <th class="text-center">Thao tác</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($receipts as $key => $receipt)
                                            <tr>
                                                <td>{{ $key + 1 + ($receipts->currentPage() - 1) * $receipts->perPage() }}</td>
                                                <td><strong>#XK{{ $receipt->id_ex }}</strong></td>
                                                <td>{{ $receipt->branch->name ?? '—' }}</td>
                                                <td>{{ $receipt->user->name ?? '—' }}</td>
                                                <td>
                                                    @php
                                                        $reasonClass = match($receipt->reason) {
                                                            'sale' => 'badge-info',
                                                            'waste' => 'badge-warning',
                                                            'broken' => 'badge-danger',
                                                            'adjustment' => 'badge-secondary',
                                                            default => 'badge-light',
                                                        };
                                                    @endphp
                                                    <span class="badge {{ $reasonClass }}">{{ $receipt->reason_label }}</span>
                                                </td>
                                                <td class="text-center">
                                                    @if((int)$receipt->status === 0)
                                                        <span class="badge badge-warning">Chờ duyệt</span>
                                                    @elseif((int)$receipt->status === 1)
                                                        <span class="badge badge-success">Đã duyệt</span>
                                                    @else
                                                        <span class="badge badge-danger">Từ chối</span>
                                                    @endif
                                                </td>
                                                <td>{{ $receipt->created_at ? ($receipt->created_at)->format('d/m/Y H:i') : '—' }}</td>
                                                <td class="text-center">
                                                    <a href="{{ route('admin.export.show', $receipt->id_ex) }}" class="btn btn-sm btn-info mr-1" title="Xem chi tiết">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    @if((int)$receipt->status === 0 && $receipt->reason !== 'sale')
                                                        <form action="{{ route('admin.export.destroy', $receipt->id_ex) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-danger" title="Xóa" onclick="return confirm('Bạn có chắc muốn xóa phiếu xuất này?')">
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
                                                        <p>Chưa có phiếu xuất kho nào</p>
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
