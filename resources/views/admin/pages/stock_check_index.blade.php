@extends('layouts.admin_home')

@section('title', 'Kiểm kê tồn kho')

@section('content')

<div id="content">
    @include('admin.partials.topbar')

    <div class="container-fluid">

        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">
                Phiếu kiểm kê
            </h1>
            <div>
                <a href="{{ route('admin.inventory.stock') }}" class="btn btn-sm btn-secondary shadow-sm mr-2">
                    <i class="fas fa-boxes fa-sm text-white-50"></i> Tồn kho
                </a>
                <a href="{{ route('admin.stock_check.create') }}" class="btn btn-sm btn-warning shadow-sm">
                    <i class="fas fa-plus fa-sm text-white-50"></i> Tạo phiếu kiểm kê
                </a>
            </div>
        </div>

        {{-- Filter --}}
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-filter"></i> Bộ lọc</h6>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('admin.stock_check.index') }}" class="mb-0">
                    <div class="row">
                        <div class="col-md-4 mb-2">
                            <label class="small font-weight-bold">Chi nhánh</label>
                            <select name="branch_id" class="form-control">
                                <option value="">Tất cả chi nhánh</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id_br }}" {{ (string)request('branch_id') === (string)$branch->id_br ? 'selected' : '' }}>
                                        {{ $branch->name }}
                                    </option>
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
                        <div class="col-md-3 mb-2">
                            <label class="small font-weight-bold">Mã phiếu</label>
                            <input type="text" name="search" class="form-control" placeholder="#ID" value="{{ request('search') }}">
                        </div>
                        <div class="col-md-2 mb-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary mr-2"><i class="fas fa-search"></i> Lọc</button>
                            <a href="{{ route('admin.stock_check.index') }}" class="btn btn-secondary"><i class="fas fa-redo"></i></a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- List --}}
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Danh sách phiếu kiểm kê</h6>
                <span class="badge badge-info">{{ $receipts->total() }} phiếu</span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                        <thead class="thead-light">
                            <tr>
                                <th width="50">STT</th>
                                <th>Mã phiếu</th>
                                <th>Chi nhánh</th>
                                <th>Người tạo</th>
                                <th class="text-center" width="120">Trạng thái</th>
                                <th>Ngày tạo</th>
                                <th class="text-center" width="220">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($receipts as $key => $r)
                                <tr>
                                    <td>{{ ($receipts->firstItem() ?? 0) + $key }}</td>
                                    <td><strong>#KK{{ $r->id_stock }}</strong></td>
                                    <td>{{ $r->branch->name ?? '—' }}</td>
                                    <td>{{ $r->user->name ?? '—' }}</td>
                                    <td class="text-center">
                                        <span class="badge badge-{{ $r->status_badge }}">{{ $r->status_label }}</span>
                                    </td>
                                    <td>{{ $r->created_at?->format('d/m/Y H:i') }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('admin.stock_check.show', $r->id_stock) }}" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if($r->status == 0)
                                            <form action="{{ route('admin.stock_check.destroy', $r->id_stock) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" title="Xóa" onclick="return confirm('Bạn có chắc muốn xóa phiếu kiểm kê này?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="fas fa-inbox fa-2x mb-2"></i>
                                            <p>Chưa có phiếu kiểm kê nào</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($receipts->isNotEmpty())
                    <div class="d-flex justify-content-end mt-2 pagination-sm">
                        {{ $receipts->links() }}
                    </div>
                @endif
            </div>
        </div>

    </div>
</div>

@endsection

