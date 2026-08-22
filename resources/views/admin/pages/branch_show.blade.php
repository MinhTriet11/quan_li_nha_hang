@extends('layouts.admin_home')

@section('title', 'Chi tiết chi nhánh')

@section('content')

            <div id="content">
                @include('admin.partials.topbar')

                <div class="container-fluid">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Chi tiết chi nhánh</h1>
                        <div>
                            <a href="{{ route('admin.branch.edit', $branch->id_br) }}" class="btn btn-primary">
                                <i class="fas fa-edit"></i> Sửa
                            </a>
                            <a href="{{ route('admin.branch') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Quay lại
                            </a>
                        </div>
                    </div>

                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Thông tin chi nhánh</h6>
                        </div>
                        <div class="card-body">
                            @if($branch->image)
                                <div class="mb-3">
                                    <img src="{{ asset($branch->image) }}" alt="{{ $branch->name }}" style="max-width:300px;border-radius:6px;border:1px solid #e3e6f0;">
                                </div>
                            @endif
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <tr>
                                        <th width="200">Tên chi nhánh</th>
                                        <td>{{ $branch->name }}</td>
                                    </tr>
                                    <tr>
                                        <th>Địa chỉ</th>
                                        <td>{{ $branch->address }}</td>
                                    </tr>
                                    <tr>
                                        <th>Vị trí</th>
                                        <td>
                                            @if($branch->location)
                                                <a href="{{ $branch->location }}" target="_blank">{{ $branch->location }}</a>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Trạng thái</th>
                                        <td>
                                            @if($branch->status == 0)
                                                <span class="badge badge-success">Đang hoạt động</span>
                                            @else
                                                <span class="badge badge-warning">Ngưng hoạt động</span>
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

@endsection