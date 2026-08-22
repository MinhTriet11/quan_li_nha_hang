@extends('layouts.admin_home')

@section('title', 'Chi tiết khách hàng')

@section('content')

            <div id="content">
                @include('admin.partials.topbar')

                <div class="container-fluid">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Chi tiết khách hàng</h1>
                        <div>
                            <a href="{{ route('admin.customer.edit', $customer->id_cus) }}" class="btn btn-primary">
                                <i class="fas fa-edit"></i> Sửa
                            </a>
                            <a href="{{ route('admin.customer') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Quay lại
                            </a>
                        </div>
                    </div>

                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Thông tin khách hàng</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <tr>
                                        <th width="200">Tên khách hàng</th>
                                        <td>{{ $customer->name }}</td>
                                    </tr>
                                    <tr>
                                        <th>Email</th>
                                        <td>{{ $customer->email }}</td>
                                    </tr>
                                    <tr>
                                        <th>Số điện thoại</th>
                                        <td>{{ $customer->phone ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Địa chỉ</th>
                                        <td>{{ $customer->address ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Trạng thái</th>
                                        <td>
                                            @if($customer->status == 1)
                                                <span class="badge badge-success">Đang hoạt động</span>
                                            @else
                                                <span class="badge badge-warning">Đã khóa</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Hoạt động gần nhất</th>
                                        <td>{{ $customer->last_activity ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Số lần đặt bàn</th>
                                        <td>{{ $customer->Books->count() ?? 0 }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    {{-- Lịch sử đặt bàn --}}
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Lịch sử đặt bàn</h6>
                        </div>
                        <div class="card-body">
                            @if($customer->Books->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-bordered" width="100%" cellspacing="0">
                                        <thead>
                                            <tr>
                                                <th>Mã</th>
                                                <th>Chi nhánh</th>
                                                <th>Bàn</th>
                                                <th>Loại bàn</th>
                                                <th>Thời gian</th>
                                                <th>Trạng thái</th>
                                                <th class="text-center">Thao tác</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($customer->Books()->with(['branch', 'table', 'tableType'])->latest('time')->get() as $book)
                                            <tr>
                                                <td>#{{ $book->id_book }}</td>
                                                <td>{{ $book->branch->name ?? 'N/A' }}</td>
                                                <td>{{ $book->table->table_name ?? 'Chưa gán' }}</td>
                                                <td>{{ $book->tableType->name ?? 'N/A' }}</td>
                                                <td>{{ \Carbon\Carbon::parse($book->time)->format('d/m/Y H:i') }}</td>
                                                <td>
                                                    @switch($book->status)
                                                        @case(0)<span class="badge badge-warning">Giữ bàn</span>@break
                                                        @case(1)<span class="badge badge-primary">Đã đặt</span>@break
                                                        @case(2)<span class="badge badge-success">Hoàn thành</span>@break
                                                        @case(3)<span class="badge badge-danger">Đã hủy</span>@break
                                                        @case(4)<span class="badge badge-secondary">Hết hạn</span>@break
                                                        @default<span class="badge badge-secondary">{{ $book->status }}</span>
                                                    @endswitch
                                                </td>
                                                <td class="text-center">
                                                    <a href="{{ route('admin.book.show', $book->id_book) }}" class="btn btn-sm btn-info" title="Xem chi tiết">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-center text-muted mb-0">Khách hàng chưa có lịch sử đặt bàn.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

@endsection
