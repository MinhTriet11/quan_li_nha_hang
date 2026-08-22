@extends('layouts.admin_home')

@section('title', 'Chi tiết bàn')

@section('content')

            <div id="content">
                @include('admin.partials.topbar')

                <div class="container-fluid">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Chi tiết bàn</h1>
                        <div>
                            <a href="{{ route('admin.table.edit', $table->id_table) }}" class="btn btn-primary">
                                <i class="fas fa-edit"></i> Sửa
                            </a>
                            <a href="{{ route('admin.table') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Quay lại
                            </a>
                        </div>
                    </div>

                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Thông tin bàn</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <tr>
                                        <th width="200">Tên bàn</th>
                                        <td>{{ $table->table_name }}</td>
                                    </tr>
                                    <tr>
                                        <th>Loại bàn</th>
                                        <td>{{ $table->TableTypes->name ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Chi nhánh</th>
                                        <td>{{ $table->branch->name ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Trạng thái</th>
                                        <td>
                                            @switch($table->status)
                                                @case(0)
                                                    <span class="badge badge-success">Đang trống</span>
                                                    @break
                                                @case(1)
                                                    <span class="badge badge-warning">Bảo trì</span>
                                                    @break
                                                @case(2)
                                                    <span class="badge badge-secondary">Đang phục vụ</span>
                                                    @break
                                                @default
                                                    <span class="badge badge-secondary">{{ $table->status }}</span>
                                            @endswitch
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

@endsection
