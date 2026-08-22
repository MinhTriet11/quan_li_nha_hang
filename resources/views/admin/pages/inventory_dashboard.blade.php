@extends('layouts.admin_home')

@section('title', 'Tổng quan tồn kho')

@section('content')

<div id="content">

    @include('admin.partials.topbar')

    <div class="container-fluid">

        {{-- Page Heading --}}
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-warehouse text-primary"></i> Tổng quan tồn kho
            </h1>
            <div>
                <a href="{{ route('admin.inventory.stock') }}" class="btn btn-sm btn-info shadow-sm mr-2">
                    <i class="fas fa-boxes fa-sm text-white-50"></i> Tồn kho & Cảnh báo
                </a>
                <a href="{{ route('admin.import.index') }}" class="btn btn-sm btn-success shadow-sm mr-2">
                    <i class="fas fa-arrow-down fa-sm text-white-50"></i> Nhập kho
                </a>
                <a href="{{ route('admin.export.index') }}" class="btn btn-sm btn-warning shadow-sm">
                    <i class="fas fa-arrow-up fa-sm text-white-50"></i> Xuất kho
                </a>
            </div>
        </div>

        {{-- Flash messages --}}
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

        {{-- Filter --}}
        <div class="card shadow mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.inventory.dashboard') }}" class="mb-0">
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
                            <label class="small font-weight-bold">Từ ngày</label>
                            <input type="date" name="from_date" class="form-control" value="{{ $fromDate }}">
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="small font-weight-bold">Đến ngày</label>
                            <input type="date" name="to_date" class="form-control" value="{{ $toDate }}">
                        </div>
                        <div class="col-md-2 mb-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary mr-2">
                                <i class="fas fa-filter"></i> Lọc
                            </button>
                            <a href="{{ route('admin.inventory.dashboard') }}" class="btn btn-secondary">
                                <i class="fas fa-redo"></i>
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- KPI Cards --}}
        <div class="row">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Số chi nhánh</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $branchCount }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-building fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Tổng nguyên liệu</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalIngredients }}</div>
                                <div class="small text-muted mt-1">Tổng SL tồn: {{ number_format($totalStockQty, 2) }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-boxes fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <a href="{{ route('admin.inventory.stock', array_filter(['branch_id' => $selectedBranchId, 'status' => 'low'])) }}" class="col-xl-3 col-md-6 mb-4 text-decoration-none">
                <div class="card border-left-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Sắp hết hàng</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $lowStockCount }}</div>
                                <div class="small text-muted mt-1">≤ mức cảnh báo</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>

            <a href="{{ route('admin.inventory.stock', array_filter(['branch_id' => $selectedBranchId, 'status' => 'out'])) }}" class="col-xl-3 col-md-6 mb-4 text-decoration-none">
                <div class="card border-left-danger shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Hết hàng</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $outOfStockCount }}</div>
                                <div class="small text-muted mt-1">Cần nhập gấp</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-times-circle fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        {{-- Charts --}}
        <div class="row">
            <div class="col-xl-8 col-lg-7">
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">Phiếu nhập / Phiếu xuất đã duyệt theo ngày</h6>
                        <small class="text-muted">{{ \Carbon\Carbon::parse($fromDate)->format('d/m/Y') }} → {{ \Carbon\Carbon::parse($toDate)->format('d/m/Y') }}</small>
                    </div>
                    <div class="card-body">
                        <div class="chart-area">
                            <canvas id="importExportChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4 col-lg-5">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Tổng tồn theo chi nhánh</h6>
                    </div>
                    <div class="card-body">
                        @if($stockByBranch->isEmpty())
                            <div class="text-center text-muted py-4">
                                <i class="fas fa-inbox fa-2x mb-2"></i>
                                <p>Chưa có dữ liệu tồn kho</p>
                            </div>
                        @else
                            @foreach($stockByBranch as $row)
                                @php
                                    $maxQty = max($stockByBranch->max('total_qty'), 1);
                                    $width = ($row['total_qty'] / $maxQty) * 100;
                                @endphp
                                <h6 class="small font-weight-bold d-flex justify-content-between">
                                    <span>{{ $row['branch_name'] }}</span>
                                    <span class="text-muted">{{ number_format($row['total_qty'], 2) }}</span>
                                </h6>
                                <div class="progress mb-3" style="height: 12px;">
                                    <div class="progress-bar {{ $row['low_count'] > 0 ? 'bg-warning' : 'bg-success' }}" role="progressbar"
                                         style="width: {{ $width }}%"></div>
                                </div>
                                @if($row['low_count'] > 0)
                                    <div class="small text-warning mb-2">Có {{ $row['low_count'] }} nguyên liệu sắp hết</div>
                                @endif
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Recent activity --}}
        <div class="row">
            <div class="col-12">
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">Hoạt động nhập/xuất gần đây</h6>
                        <div>
                            <small class="text-muted mr-2">{{ $importTotal + $exportTotal }} phiếu đã duyệt · {{ $importPending + $exportPending }} phiếu chờ duyệt</small>
                        </div>
                    </div>
                    <div class="card-body">
                        <ul class="nav nav-tabs mb-3" id="recentTab" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="import-tab" data-toggle="tab" href="#recentImport" role="tab">
                                    <i class="fas fa-arrow-down text-success"></i> Phiếu nhập ({{ $recentImports->count() }})
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="export-tab" data-toggle="tab" href="#recentExport" role="tab">
                                    <i class="fas fa-arrow-up text-warning"></i> Phiếu xuất ({{ $recentExports->count() }})
                                </a>
                            </li>
                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane fade show active" id="recentImport" role="tabpanel">
                                @forelse($recentImports as $r)
                                    <a href="{{ route('admin.import.show', $r->id_im) }}" class="text-decoration-none">
                                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom text-dark">
                                            <div>
                                                <strong>#{{ $r->id_im }}</strong>
                                                <span class="text-muted ml-2">{{ $r->branch->name ?? '—' }}</span>
                                                <div class="small text-muted">{{ $r->created_at->format('d/m/Y H:i') }} · {{ $r->user->name ?? 'N/A' }}</div>
                                            </div>
                                            @if($r->status == 1)
                                                <span class="badge badge-success">Đã duyệt</span>
                                            @else
                                                <span class="badge badge-secondary">Chờ duyệt</span>
                                            @endif
                                        </div>
                                    </a>
                                @empty
                                    <div class="text-center text-muted py-3">Không có phiếu nhập trong khoảng thời gian này</div>
                                @endforelse
                            </div>
                            <div class="tab-pane fade" id="recentExport" role="tabpanel">
                                @forelse($recentExports as $r)
                                    <a href="{{ route('admin.export.show', $r->id_ex) }}" class="text-decoration-none">
                                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom text-dark">
                                            <div>
                                                <strong>#{{ $r->id_ex }}</strong>
                                                <span class="text-muted ml-2">{{ $r->branch->name ?? '—' }}</span>
                                                <div class="small text-muted">{{ \Carbon\Carbon::parse($r->create_at)->format('d/m/Y H:i') }} · {{ $r->reason_label }}</div>
                                            </div>
                                            @if($r->status == 1)
                                                <span class="badge badge-success">Đã duyệt</span>
                                            @elseif($r->status == 2)
                                                <span class="badge badge-danger">Từ chối</span>
                                            @else
                                                <span class="badge badge-secondary">Chờ duyệt</span>
                                            @endif
                                        </div>
                                    </a>
                                @empty
                                    <div class="text-center text-muted py-3">Không có phiếu xuất trong khoảng thời gian này</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Quick link card --}}
        <div class="card shadow mb-4 border-left-info">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="m-0 font-weight-bold text-info">
                        <i class="fas fa-boxes"></i> Tồn kho & Cảnh báo
                    </h6>
                    <p class="small text-muted mb-0">Xem chi tiết tồn kho của từng nguyên liệu theo chi nhánh và các cảnh báo dưới mức tối thiểu.</p>
                </div>
                <a href="{{ route('admin.inventory.stock', array_filter(['branch_id' => $selectedBranchId])) }}" class="btn btn-info">
                    Mở trang tồn kho <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
        </div>

    </div>
</div>

@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js@2.9.4/dist/Chart.min.js"></script>
<script>
(function () {
    var labels = @json($importChart['labels']);
    var importData = @json($importChart['values']);
    var exportData = @json($exportChart['values']);

    var ctx = document.getElementById('importExportChart');
    if (ctx && typeof Chart !== 'undefined') {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Phiếu nhập (đã duyệt)',
                        data: importData,
                        borderColor: '#1cc88a',
                        backgroundColor: 'rgba(28, 200, 138, 0.1)',
                        borderWidth: 2,
                        tension: 0.3,
                        fill: true,
                        pointRadius: 3,
                        pointBackgroundColor: '#1cc88a'
                    },
                    {
                        label: 'Phiếu xuất (đã duyệt)',
                        data: exportData,
                        borderColor: '#f6c23e',
                        backgroundColor: 'rgba(246, 194, 62, 0.1)',
                        borderWidth: 2,
                        tension: 0.3,
                        fill: true,
                        pointRadius: 3,
                        pointBackgroundColor: '#f6c23e'
                    }
                ]
            },
            options: {
                maintainAspectRatio: false,
                legend: { display: true, position: 'bottom' },
                scales: {
                    x: { grid: { display: false } },
                    y: { beginAtZero: true, ticks: { precision: 0 } }
                }
            }
        });
    }
})();
</script>
@endsection