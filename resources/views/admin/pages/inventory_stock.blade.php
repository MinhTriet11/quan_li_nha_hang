@extends('layouts.admin_home')

@section('title', 'Tồn kho & Cảnh báo')

@section('content')

<div id="content">

    @include('admin.partials.topbar')

    <div class="container-fluid">

        {{-- Page Heading --}}
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-boxes text-info"></i> Tồn kho
            </h1>
            <div>
                <a href="{{ route('admin.stock_check.create') }}" class="btn btn-sm btn-warning shadow-sm mr-2">
                    <i class="fas fa-clipboard-check fa-sm text-white-50"></i> Kiểm kê
                </a>
                <a href="{{ route('admin.import.index') }}" class="btn btn-sm btn-success shadow-sm">
                    <i class="fas fa-plus fa-sm text-white-50"></i> Tạo phiếu chuyển kho
                </a>
            </div>
        </div>

        {{-- Summary cards --}}
        <div class="row mb-4">
            <div class="col-md-4 mb-2">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Tổng nguyên liệu</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalItems }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-cubes fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-2">
                <div class="card border-left-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Sắp hết</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $lowStockCount }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-2">
                <div class="card border-left-danger shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Hết hàng</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $outOfStockCount }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-times-circle fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filter --}}
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-filter"></i> Bộ lọc</h6>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('admin.inventory.stock') }}" class="mb-0">
                    <div class="row">
                        <div class="col-md-4 mb-2">
                            <label class="small font-weight-bold">Chi nhánh</label>
                            <select name="branch_id" class="form-control">
                                <option value="">Tất cả chi nhánh</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id_br }}" {{ (string)$selectedBranchId === (string)$branch->id_br ? 'selected' : '' }}>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="small font-weight-bold">Trạng thái</label>
                            <select name="status" class="form-control">
                                <option value="" {{ ($status ?? '') === '' ? 'selected' : '' }}>Tất cả</option>
                                <option value="out" {{ ($status ?? '') === 'out' ? 'selected' : '' }}>Hết hàng</option>
                                <option value="low" {{ ($status ?? '') === 'low' ? 'selected' : '' }}>Sắp hết</option>
                                <option value="ok" {{ ($status ?? '') === 'ok' ? 'selected' : '' }}>Ổn định</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="small font-weight-bold">Tìm kiếm</label>
                            <input type="text" name="search" class="form-control" placeholder="Tên nguyên liệu..." value="{{ $search }}">
                        </div>
                        <div class="col-md-2 mb-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary mr-2"><i class="fas fa-search"></i> Lọc</button>
                            <a href="{{ route('admin.inventory.stock') }}" class="btn btn-secondary"><i class="fas fa-redo"></i></a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Stock table --}}
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Danh sách tồn kho nguyên liệu</h6>
                <span class="badge badge-info">{{ $totalItems }} dòng</span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                        <thead class="thead-light">
                            <tr>
                                <th width="50">STT</th>
                                <th>Nguyên liệu</th>
                                <th>Chi nhánh</th>
                                <th class="text-right">Số lượng tồn</th>
                                <th class="text-right">Mức cảnh báo</th>
                                {{-- <th width="220">So với mức cảnh báo</th> --}}
                                <th class="text-center" width="120">Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($stocks as $key => $s)
                                @php
                                    $isOut = (bool) $s->is_out;
                                    $isLow = (bool) $s->is_low;
                                    $statusClass = $isOut ? 'danger' : ($isLow ? 'warning' : 'success');
                                    $statusText  = $isOut ? 'Hết hàng' : ($isLow ? 'Sắp hết' : 'Ổn định');
                                @endphp
                                <tr class="{{ $isOut ? 'table-danger' : ($isLow ? 'table-warning' : '') }}">
                                    <td>{{ ($stocks->firstItem() ?? 0) + $key }}</td>
                                    <td><strong>{{ $s->ingredient->name ?? '—' }}</strong></td>
                                    <td>{{ $s->branch->name ?? '—' }}</td>
                                    <td class="text-right">
                                        <span class="font-weight-bold {{ $statusClass === 'success' ? '' : 'text-' . $statusClass }}">
                                            {{ number_format((float) $s->quantity, 2) + 0 }} {{ $s->ingredient->unit ?? '' }}
                                        </span>
                                    </td>
                                    <td class="text-right">{{ number_format((float) ($s->ingredient->min_stock ?? 0), 2) }} {{ $s->ingredient->unit ?? '' }}</td>

                                    <td class="text-center">
                                        <span class="pt-1 badge badge-{{ $statusClass }}">{{ $statusText }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="fas fa-inbox fa-2x mb-2"></i>
                                            <p>Không có dữ liệu tồn kho phù hợp với bộ lọc</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if($stocks->isNotEmpty())
                    <div class="d-flex flex-column flex-md-row justify-content-end align-items-center mt-2 pagination-sm ">
                        <div>
                            {{ $stocks->links() }} 
                        </div>
                    </div>
                @endif
            </div>
        </div>

    </div>
</div>

@endsection