@extends('layouts.admin_home')

@section('title', 'Dashboard')

@section('content')
            <!-- Main Content -->
            <div id="content">

                @include('admin.partials.topbar')

                <!-- Begin Page Content -->
                <div class="container-fluid">

                    <!-- Page Heading -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
                    </div>

                    <!-- Content Row: 4 ô KPI -->
                    <div class="row">

                        <!-- Doanh thu -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                Doanh thu</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                {{ number_format((float) $totalRevenue, 0, ',', '.') . ' đ' }}
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tổng vốn nhập -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-success shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                                Tổng vốn nhập</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                {{ number_format((float) $totalImportValue, 0, ',', '.') . ' đ' }}
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-truck fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Số phiếu chưa duyệt -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-warning shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                                Phiếu chưa duyệt</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                {{ $pendingReceiptsCount }}
                                            </div>
                                            <div class="text-xs text-gray-500 mt-1">
                                                Nhập: {{ $pendingImportCount }} ·
                                                Xuất: {{ $pendingExportCount }} ·
                                                Chuyển: {{ $pendingTransferCount }}
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Nguyên liệu cảnh báo -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-danger shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                                Nguyên liệu cảnh báo</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                {{ $lowStockCount }}
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Content Row: 2 biểu đồ -->
                    <div class="row">

                        <!-- Area Chart: doanh thu 7 ngày -->
                        <div class="col-xl-8 col-lg-7">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Doanh thu 7 ngày gần nhất</h6>
                                </div>
                                <div class="card-body">
                                    <div class="chart-area">
                                        <canvas id="weeklyRevenueChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pie Chart: doanh thu từng chi nhánh -->
                        <div class="col-xl-4 col-lg-5">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Doanh thu theo chi nhánh</h6>
                                </div>
                                <div class="card-body">
                                    <div class="chart-pie pt-4 pb-2">
                                        <canvas id="branchRevenueChart"></canvas>
                                    </div>
                                    <div class="mt-4 text-center small" id="branchRevenueLegend"
                                        data-labels='@json($branchLabels)'></div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <!-- /.container-fluid -->

            </div>
            <!-- End of Main Content -->

@endsection



@push('js')
<script>
    // ===== Biểu đồ doanh thu 7 ngày =====
    (function () {
        var ctx = document.getElementById('weeklyRevenueChart');
        if (!ctx) return;

        var labels = @json($weeklyLabels);
        var values = @json($weeklyValues);

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Doanh thu',
                    lineTension: 0.3,
                    backgroundColor: 'rgba(78, 115, 223, 0.05)',
                    borderColor: 'rgba(78, 115, 223, 1)',
                    pointRadius: 3,
                    pointBackgroundColor: 'rgba(78, 115, 223, 1)',
                    pointBorderColor: 'rgba(78, 115, 223, 1)',
                    pointHoverRadius: 5,
                    pointHoverBackgroundColor: 'rgba(78, 115, 223, 1)',
                    pointHoverBorderColor: 'rgba(78, 115, 223, 1)',
                    pointHitRadius: 10,
                    pointBorderWidth: 2,
                    data: values,
                }],
            },
            options: {
                maintainAspectRatio: false,
                layout: { padding: { left: 10, right: 25, top: 25, bottom: 0 } },
                scales: {
                    x: { grid: { display: false, drawBorder: false } },
                    y: {
                        ticks: {
                            beginAtZero: true,
                            callback: function (v) {
                                return new Intl.NumberFormat('vi-VN').format(v) + ' đ';
                            },
                        },
                        grid: { color: 'rgb(234, 236, 244)', drawBorder: false, borderDash: [2], zeroLineBorderDash: [2] },
                    },
                },
                tooltips: {
                    backgroundColor: 'rgb(255,255,255)',
                    bodyFontColor: '#858796',
                    titleMarginBottom: 10,
                    titleFontColor: '#6e707e',
                    titleFontSize: 14,
                    borderColor: '#dddfeb',
                    borderWidth: 1,
                    xPadding: 15,
                    yPadding: 15,
                    displayColors: false,
                    intersect: false,
                    mode: 'index',
                    caretPadding: 10,
                    callbacks: {
                        label: function (tooltipItem) {
                            return ' Doanh thu: ' + new Intl.NumberFormat('vi-VN').format(tooltipItem.yLabel) + ' đ';
                        },
                    },
                },
            },
        });
    })();

    // ===== Biểu đồ tròn: doanh thu từng chi nhánh =====
    (function () {
        var ctx = document.getElementById('branchRevenueChart');
        if (!ctx) return;

        var labels = @json($branchLabels);
        var values = @json($branchValues);
        var palette = ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#858796', '#5a5c69', '#fd7e14'];
        var colors = labels.map(function (_, i) { return palette[i % palette.length]; });

        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: values,
                    backgroundColor: colors,
                    hoverBackgroundColor: colors.map(function (c) { return c + 'cc'; }),
                    hoverBorderColor: 'rgba(234, 236, 244, 1)',
                }],
            },
            options: {
                maintainAspectRatio: false,
                tooltips: {
                    backgroundColor: 'rgb(255,255,255)',
                    bodyFontColor: '#858796',
                    borderColor: '#dddfeb',
                    borderWidth: 1,
                    xPadding: 15,
                    yPadding: 15,
                    displayColors: false,
                    caretPadding: 10,
                    callbacks: {
                        label: function (tooltipItem, data) {
                            var label = data.labels[tooltipItem.index] || '';
                            var val = data.datasets[0].data[tooltipItem.index] || 0;
                            return label + ': ' + new Intl.NumberFormat('vi-VN').format(val) + ' đ';
                        },
                    },
                },
                legend: { display: false },
                cutoutPercentage: 80,
            },
        });

        // Render legend từ data-labels để đồng bộ màu với chart.
        var legendEl = document.getElementById('branchRevenueLegend');
        if (legendEl) {
            try {
                var legendLabels = JSON.parse(legendEl.getAttribute('data-labels') || '[]');
            } catch (e) {
                var legendLabels = [];
            }
            legendEl.innerHTML = legendLabels.map(function (name, i) {
                return '<span class="mr-2"><i class="fas fa-circle" style="color: ' +
                    colors[i] + '"></i> ' + name + '</span>';
            }).join('');
        }
    })();
</script>
@endpush
