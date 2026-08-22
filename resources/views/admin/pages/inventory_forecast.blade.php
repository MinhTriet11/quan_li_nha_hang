@extends('layouts.admin_home')

@section('title', 'Dự báo nhập kho')

@section('content')
    <div id="content">
        @include('admin.partials.topbar')

        <div class="container-fluid">

            {{-- ============== Page heading ============== --}}
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <div>
                    <h1 class="h3 mb-1 text-gray-800 font-weight-bold">
                        Dự báo nhập kho
                    </h1>
                    <small class="text-muted">
                        Dựa trên trung bình {{ $historyDays }} ngày gần nhất · Dự báo cho {{ $forecastDays }} ngày tới
                        @if($selectedBranch)
                            · Chi nhánh: <strong>{{ $selectedBranch->name }}</strong>
                        @endif
                    </small>
                </div>
            </div>

            {{-- ============== Bộ lọc ============== --}}
            <div class="card shadow mb-4 border-0">
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.inventory.forecast') }}" class="mb-0">
                        <div class="row">
                            <div class="col-md-4 mb-2">
                                <label class="small font-weight-bold">Chi nhánh</label>
                                <select name="branch_id" class="form-control">
                                    @foreach($branches as $b)
                                        <option value="{{ $b->id_br }}" {{ (int) $selectedBranchId === (int) $b->id_br ? 'selected' : '' }}>
                                            {{ $b->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 mb-2">
                                <label class="small font-weight-bold">Số ngày lịch sử (tính TB)</label>
                                <select name="history_days" class="form-control">
                                    @foreach([7, 14, 21, 30, 60, 90] as $d)
                                        <option value="{{ $d }}" {{ (int) $historyDays === $d ? 'selected' : '' }}>{{ $d }} ngày</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 mb-2">
                                <label class="small font-weight-bold">Số ngày dự báo</label>
                                <select name="forecast_days" class="form-control">
                                    @foreach([3, 7, 14, 21, 30] as $d)
                                        <option value="{{ $d }}" {{ (int) $forecastDays === $d ? 'selected' : '' }}>{{ $d }} ngày tới</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 mb-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary btn-block">
                                    <i class="fas fa-calculator"></i> Tính lại
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            @php
                $kpiTotalSuggest = collect($suggestions)->sum('suggest_qty');
                $kpiShortage    = collect($suggestions)->where('shortage', '>', 0)->count();
                $kpiOk          = collect($suggestions)->where('shortage', '=', 0)->count();
                $kpiOut         = collect($suggestions)->where('is_out', true)->count();
            @endphp

            {{-- ============== KPI ============== --}}
            <div class="row mb-4">
                <div class="col-md-3 mb-3">
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Món dự kiến bán</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ collect($forecasts)->sum('forecast_qty') }} <small class="text-muted">phần</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card border-left-warning shadow h-100 py-2">
                        <div class="card-body">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Nguyên liệu thiếu hụt</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $kpiShortage }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card border-left-danger shadow h-100 py-2">
                        <div class="card-body">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Đã hết hàng</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $kpiOut }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card border-left-success shadow h-100 py-2">
                        <div class="card-body">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Đủ dùng (không cần nhập)</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $kpiOk }}</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ============== 1. Dự báo nhu cầu món ăn ============== --}}
            <div class="card shadow mb-4 border-0">
                <div class="card-header py-3 bg-primary text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-utensils mr-2"></i>1. Dự báo nhu cầu món ăn
                        <span class="badge badge-light ml-2">{{ collect($forecasts)->count() }} món</span>
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle" width="100%" cellspacing="0">
                            <thead class="thead-light">
                                <tr>
                                    <th width="60">STT</th>
                                    <th>Tên món</th>
                                    <th class="text-center">SL bán {{ $historyDays }} ngày qua</th>
                                    <th class="text-center">TB / ngày</th>
                                    <th class="text-center">Dự kiến {{ $forecastDays }} ngày tới</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($forecasts as $idFood => $f)
                                    @php
                                        $menu = \App\Models\MenuItem::find($idFood);
                                    @endphp
                                    <tr>
                                        <td class="text-muted">{{ $loop->iteration }}</td>
                                        <td><strong>{{ $menu->name ?? 'Món #' . $idFood }}</strong></td>
                                        <td class="text-center">{{ $f['history_qty'] }}</td>
                                        <td class="text-center text-muted">{{ $f['avg_per_day'] }}</td>
                                        <td class="text-center">
                                            <span class="badge badge-primary px-3 py-2" style="font-size: 13px;">
                                                {{ $f['forecast_qty'] }} phần
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">
                                            Chưa có dữ liệu bán hàng trong khoảng đã chọn.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- ============== 2. Yêu cầu nguyên liệu ============== --}}
            <div class="card shadow mb-4 border-0">
                <div class="card-header py-3 bg-info text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-carrot mr-2"></i>2. Yêu cầu nguyên liệu (tính từ công thức)
                        <span class="badge badge-light ml-2">{{ count($ingredientRequired) }} nguyên liệu</span>
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle" width="100%" cellspacing="0">
                            <thead class="thead-light">
                                <tr>
                                    <th width="60">STT</th>
                                    <th>Nguyên liệu</th>
                                    <th class="text-right">Lượng cần dùng</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($ingredientRequired as $idIngre => $qty)
                                    @php $ingre = \App\Models\Ingredient::find($idIngre); @endphp
                                    <tr>
                                        <td class="text-muted">{{ $loop->iteration }}</td>
                                        <td><strong>{{ $ingre->name ?? 'NL #' . $idIngre }}</strong></td>
                                        <td class="text-right font-weight-bold text-info">
                                            {{ number_format($qty, 2) }} {{ $ingre->unit ?? '' }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center py-4 text-muted">
                                            Chưa có công thức hoặc chưa có món nào được dự báo.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- ============== 3. Đề xuất nhập kho ============== --}}
            <div class="card shadow mb-4 border-0">
                <div class="card-header py-3 bg-success text-white d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-truck-loading mr-2"></i>3. Đề xuất nhập kho
                        <span class="badge badge-light ml-2">{{ count($suggestions) }} nguyên liệu cần xem</span>
                    </h6>
                </div>
                <div class="card-body">
                    {{-- <p class="text-muted small mb-3">
                        Mức an toàn dùng = <code>min_stock</code> của từng nguyên liệu.
                        Đề xuất nhập = (yêu cầu + an toàn) − tồn kho hiện tại.
                        Sắp xếp theo mức thiếu hụt giảm dần — món nào thiếu nhiều nhất lên đầu.
                    </p> --}}
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle" width="100%" cellspacing="0">
                            <thead class="thead-light">
                                <tr>
                                    <th width="60">STT</th>
                                    <th>Nguyên liệu</th>
                                    <th class="text-right">Yêu cầu</th>
                                    <th class="text-right">Tồn kho</th>
                                    <th class="text-right">An toàn (min)</th>
                                    <th class="text-right">Mức đích</th>
                                    <th class="text-right">Thiếu hụt</th>
                                    <th class="text-right">Đề xuất nhập</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($suggestions as $s)
                                    <tr class="{{ $s['is_out'] ? 'table-danger' : ($s['shortage'] > 0 ? 'table-warning' : '') }}">
                                        <td class="text-muted">{{ $loop->iteration }}</td>
                                        <td>
                                            <strong>{{ $s['name'] }}</strong>
                                            @if($s['is_out'])
                                                <span class="badge badge-danger ml-1">Hết hàng</span>
                                            @elseif($s['is_low'])
                                                <span class="badge badge-warning ml-1">Sắp hết</span>
                                            @endif
                                        </td>
                                        <td class="text-right">
                                            {{ number_format($s['required'], 2) }} <small>{{ $s['unit'] }}</small>
                                        </td>
                                        <td class="text-right">
                                            {{ number_format($s['current_qty'], 2) }} <small>{{ $s['unit'] }}</small>
                                        </td>
                                        <td class="text-right text-muted">
                                            {{ number_format($s['safety_stock'], 2) }} <small>{{ $s['unit'] }}</small>
                                        </td>
                                        <td class="text-right">
                                            {{ number_format($s['target_stock'], 2) }} <small>{{ $s['unit'] }}</small>
                                        </td>
                                        <td class="text-right">
                                            @if($s['shortage'] > 0)
                                                <span class="text-danger font-weight-bold">
                                                    −{{ number_format($s['shortage'], 2) }} <small>{{ $s['unit'] }}</small>
                                                </span>
                                            @else
                                                <span class="text-success">
                                                    <i class="fas fa-check"></i> Đủ
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-right">
                                            @if($s['suggest_qty'] > 0)
                                                <strong class="text-success">
                                                    {{ number_format($s['suggest_qty'], 2) }} <small>{{ $s['unit'] }}</small>
                                                </strong>
                                            @else
                                                <span class="text-muted">0</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-5 text-muted">
                                            <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                                            <p class="mb-0">Tồn kho hiện tại đủ đáp ứng nhu cầu dự báo.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                            @if(count($suggestions) > 0)
                            <tfoot class="thead-light">
                                <tr>
                                    <th colspan="7" class="text-right">Tổng đề xuất nhập:</th>
                                    <th class="text-right text-success font-weight-bold">
                                        {{ number_format($kpiTotalSuggest, 2) }}
                                    </th>
                                </tr>
                            </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
