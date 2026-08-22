@extends('layouts.admin_home')

@section('title', 'Báo cáo tổng hợp')

@section('content')
    <div id="content">
        @include('admin.partials.topbar')

        <div class="container-fluid">

            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <div>
                    <h1 class="h3 mb-0 text-gray-800 font-weight-bold">
                        Báo cáo
                    </h1>
                    <p class="mb-0 text-gray-600">
                        Tổng hợp 3 nhóm báo cáo: doanh thu, món ăn bán chạy và nguyên liệu.
                    </p>
                </div>
            </div>

            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-filter mr-1"></i> Bộ lọc chung</h6>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.report.index') }}" class="row align-items-end">
                        <div class="form-group col-md-4">
                            <label class="small font-weight-bold">Chi nhánh</label>
                            <select name="branch_id" class="form-control form-control-sm">
                                <option value="">Tất cả chi nhánh</option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id_br }}"
                                        {{ (string) $branchId === (string) $branch->id_br ? 'selected' : '' }}>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-3">
                            <label class="small font-weight-bold">Từ ngày</label>
                            <input type="date" name="from_date" value="{{ $fromDate }}" class="form-control form-control-sm">
                        </div>
                        <div class="form-group col-md-3">
                            <label class="small font-weight-bold">Đến ngày</label>
                            <input type="date" name="to_date" value="{{ $toDate }}" class="form-control form-control-sm">
                        </div>
                        <div class="form-group col-md-2">
                            <button type="submit" class="btn btn-sm btn-primary btn-block">
                                <i class="fas fa-search"></i> Áp dụng
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- PHẦN 1: DOANH THU --}}
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-dollar-sign mr-1"></i> 1. Doanh thu
                    </h6>
                    <small class="text-muted">
                        {{ \Carbon\Carbon::parse($fromDate)->format('d/m/Y') }}
                        → {{ \Carbon\Carbon::parse($toDate)->format('d/m/Y') }}
                    </small>
                </div>
                <div class="card-body">
                    {{-- KPI doanh thu --}}
                    <div class="row">
                        <div class="col-xl-4 col-md-6 mb-3">
                            <div class="card border-left-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Tổng doanh thu</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        {{ number_format($totalRevenue, 0, ',', '.') }} đ
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-4 col-md-6 mb-3">
                            <div class="card border-left-success shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Số hóa đơn</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($totalBills) }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-4 col-md-12 mb-3">
                            <div class="card border-left-info shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Trung bình / hóa đơn</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        {{ number_format($avgBill, 0, ',', '.') }} đ
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Biểu đồ doanh thu --}}
                    <div class="chart-area" style="height: 280px;">
                        <canvas id="reportsRevenueChart"></canvas>
                    </div>
                </div>
            </div>

            {{-- PHẦN 2: MÓN ĂN BÁN CHẠY --}}
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-trophy mr-1"></i> 2. Món ăn bán chạy (Top 10 theo doanh thu)
                    </h6>
                    <small class="text-muted">
                        Tổng SL: <strong>{{ number_format($totalQtyAll) }}</strong> ·
                        Tổng DT: <strong>{{ number_format($totalRevenueAll, 0, ',', '.') }} đ</strong>
                    </small>
                </div>
                <div class="card-body">
                    @if ($topBestSellers->count() > 0)
                        @php $maxRevTop = max($topBestSellers->max('total_revenue'), 1); @endphp
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                                <thead class="thead-light">
                                    <tr>
                                        <th width="60">#</th>
                                        <th>Món ăn</th>
                                        <th>Danh mục</th>
                                        <th class="text-right">Số lượng</th>
                                        <th class="text-right">Doanh thu</th>
                                        <th>Trực quan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($topBestSellers as $i => $row)
                                        <tr>
                                            <td>
                                                @if ($i === 0)
                                                    <span class="badge badge-warning font-weight-bold">🥇 #1</span>
                                                @elseif ($i === 1)
                                                    <span class="badge badge-secondary font-weight-bold">🥈 #2</span>
                                                @elseif ($i === 2)
                                                    <span class="badge badge-danger font-weight-bold">🥉 #3</span>
                                                @else
                                                    <span class="badge badge-light">#{{ $i + 1 }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    @if (!empty($row['image']))
                                                        <img src="{{ asset('/storage/menu_items/'.$row['image']) }}" alt="{{ $row['name'] }}"
                                                             class="img-thumbnail mr-2"
                                                             style="width: 38px; height: 38px; object-fit: cover;">
                                                    @else
                                                        <div class="bg-light rounded mr-2 d-flex align-items-center justify-content-center"
                                                             style="width: 38px; height: 38px;">
                                                            <i class="fas fa-utensils text-gray-400"></i>
                                                        </div>
                                                    @endif
                                                    <div>
                                                        <div class="font-weight-bold">{{ $row['name'] }}</div>
                                                        <small class="text-muted">#{{ $row['id_food'] }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>{{ $row['category'] }}</td>
                                            <td class="text-right font-weight-bold">{{ number_format($row['total_qty']) }}</td>
                                            <td class="text-right font-weight-bold text-success">
                                                {{ number_format($row['total_revenue'], 0, ',', '.') }} đ
                                            </td>
                                            <td style="min-width: 140px;">
                                                <div class="progress" style="height: 8px;">
                                                    <div class="progress-bar bg-success" role="progressbar"
                                                         style="width: {{ $row['total_revenue'] / $maxRevTop * 100 }}%"></div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-center text-muted my-4">Chưa có dữ liệu bán hàng trong khoảng đã chọn.</p>
                    @endif
                </div>
            </div>

            {{-- PHẦN 3: NGUYÊN LIỆU --}}
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-boxes mr-1"></i> 3. Nguyên liệu
                    </h6>
                </div>
                <div class="card-body">
                    {{-- KPI nguyên liệu --}}
                    <div class="row">
                        <div class="col-xl-3 col-md-6 mb-3">
                            <div class="card border-left-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Tổng tồn kho</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        {{ number_format($totalStockQty, 1) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6 mb-3">
                            <div class="card border-left-warning shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Sắp hết</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($lowStockCount) }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6 mb-3">
                            <div class="card border-left-danger shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Đã hết</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($outOfStockCount) }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6 mb-3">
                            <div class="card border-left-success shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Tổng NL nhập</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        {{ number_format($totalImportQty, 1) }}
                                    </div>
                                    <div class="text-xs text-gray-500 mt-1">
                                        Giá trị: {{ number_format($totalImportValue, 0, ',', '.') }} đ
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Bảng nguyên liệu --}}
                    @if ($ingredientReport->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover table-sm" width="100%" cellspacing="0">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Nguyên liệu</th>
                                        <th>Chi nhánh</th>
                                        <th class="text-right">Tồn kho</th>
                                        <th class="text-right">Min</th>
                                        <th class="text-right">Nhập</th>
                                        <th class="text-right">Xuất bán</th>
                                        <th class="text-right">Xuất khác</th>
                                        <th>Trạng thái</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($ingredientReport as $row)
                                        <tr>
                                            <td class="font-weight-bold">{{ $row['name'] }} <small class="text-muted">({{ $row['unit'] }})</small></td>
                                            <td>{{ $row['branch_name'] }}</td>
                                            <td class="text-right">{{ number_format($row['stock_qty'], 1) }}</td>
                                            <td class="text-right text-muted">{{ number_format($row['min_stock'], 1) }}</td>
                                            <td class="text-right text-primary">{{ number_format($row['import_qty'], 1) }}</td>
                                            <td class="text-right text-success">{{ number_format($row['export_qty'], 1) }}</td>
                                            <td class="text-right text-danger">{{ number_format($row['waste_qty'], 1) }}</td>
                                            <td>
                                                @if ($row['is_out'])
                                                    <span class="badge badge-danger">Hết hàng</span>
                                                @elseif ($row['is_low'])
                                                    <span class="badge badge-warning">Sắp hết</span>
                                                @else
                                                    <span class="badge badge-success">Bình thường</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-center text-muted my-4">Chưa có dữ liệu nguyên liệu.</p>
                    @endif
                </div>
            </div>

        </div>
    </div>
@endsection

@section('js')
    <script>
        (function () {
            const ctx = document.getElementById('reportsRevenueChart');
            if (!ctx) return;

            const labels = @json($revenueLabels);
            const values = @json($revenueValues);
            const counts = @json($revenueCounts);

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Doanh thu (đ)',
                        data: values,
                        borderColor: '#4e73df',
                        backgroundColor: 'rgba(78, 115, 223, 0.08)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.35,
                        pointRadius: 3,
                        pointBackgroundColor: '#4e73df',
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: (c) => ' Doanh thu: ' + Number(c.parsed.y || 0).toLocaleString('vi-VN') + ' đ',
                                afterLabel: (c) => ' Số hóa đơn: ' + (counts[c.dataIndex] || 0),
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: (v) => Number(v).toLocaleString('vi-VN')
                            }
                        }
                    }
                }
            });
        })();
    </script>
@endsection
