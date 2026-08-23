@extends('layouts.admin_home')

@section('title', 'Chi tiết đặt bàn')

@section('content')

            <!-- Main Content -->
            <div id="content">

                @include('admin.partials.topbar')

                <!-- Begin Page Content -->
                <div class="container-fluid">

                    <!-- Page Heading -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Chi tiết đặt bàn</h1>
                        <a href="{{ route('admin.book') }}" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
                            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Quay lại
                        </a>
                    </div>

                    <!-- Detail Card -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Thông tin đặt bàn #{{ $book->id_book }}</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h5 class="text-primary mb-3">Thông tin khách hàng</h5>
                                    <p><strong>Tên khách hàng:</strong> {{ $book->guest_name ?? ($book->customer->name ?? 'N/A') }}</p>
                                    <p><strong>Số điện thoại:</strong> {{ $book->guest_phone ?? ($book->customer->phone ?? 'N/A') }}</p>
                                    <p><strong>Email:</strong> {{ $book->guest_email ?? ($book->customer->email ?? 'N/A') }}</p>
                                </div>
                                <div class="col-md-6">
                                    <h5 class="text-primary mb-3">Thông tin đặt bàn</h5>
                                    <p><strong>Chi nhánh:</strong> {{ $book->branch->name ?? 'N/A' }}</p>
                                    <p><strong>Loại bàn:</strong> {{ $book->tableType->name ?? 'N/A' }}</p>
                                    <p><strong>Ngày đặt:</strong> {{ \Carbon\Carbon::parse($book->time)->format('d/m/Y') }}</p>
                                    <p><strong>Giờ đặt:</strong> {{ \Carbon\Carbon::parse($book->time)->format('H:i') }}</p>
                                    <p><strong>Bàn:</strong> {{ $book->table->table_name ?? 'Chưa gán' }}</p>
                                    <p>
                                        <strong>Trạng thái:</strong>
                                        @switch($book->status)
                                            @case(0)
                                                <span class="badge badge-warning">Giữ bàn</span>
                                                @break
                                            @case(1)
                                                <span class="badge badge-primary">Đã đặt</span>
                                                @break
                                            @case(2)
                                                <span class="badge badge-success">Hoàn thành</span>
                                                @break
                                            @case(3)
                                                <span class="badge badge-danger">Đã hủy</span>
                                                @break
                                            @case(4)
                                                <span class="badge badge-secondary">Hết hạn</span>
                                                @break
                                            @default
                                                <span class="badge badge-secondary">{{ $book->status }}</span>
                                        @endswitch
                                    </p>
                                </div>
                            </div>

                            {{-- <div class="mt-4">
                                @if($book->status == 0 || $book->status == 1)
                                    <a href="{{ route('admin.book.edit', $book->id_book) }}" class="btn btn-primary">
                                        <i class="fas fa-edit mr-1"></i> Sửa
                                    </a>

                                    <form action="{{ route('admin.book.confirm', $book->id_book) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-success"
                                            onclick="return confirm('Xác nhận đặt bàn này?')">
                                            <i class="fas fa-check mr-1"></i> Xác nhận
                                        </button>
                                    </form>

                                    <form action="{{ route('admin.book.cancel', $book->id_book) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-danger"
                                            onclick="return confirm('Hủy đặt bàn này?')">
                                            <i class="fas fa-times mr-1"></i> Hủy
                                        </button>
                                    </form>
                                @endif
                                <a href="{{ route('admin.book') }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left mr-1"></i> Quay lại
                                </a>
                            </div> --}}
                        </div>
                    </div>

                </div>
                <!-- /.container-fluid -->

            </div>
            <!-- End of Main Content -->

@endsection
