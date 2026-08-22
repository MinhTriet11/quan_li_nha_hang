@extends('layouts.admin_home')

@section('title', 'Sửa đặt bàn')

@section('content')

    <!-- Main Content -->
    <div id="content">

        @include('admin.partials.topbar')

        <!-- Begin Page Content -->
        <div class="container-fluid">

            <!-- Page Heading -->
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">Sửa đặt bàn</h1>
                <a href="{{ route('admin.book') }}" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
                    <i class="fas fa-arrow-left fa-sm text-white-50"></i> Quay lại
                </a>
            </div>

            <!-- Form Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Thông tin đặt bàn</h6>
                </div>
                <div class="card-body">
                    <form id="formEditBook" method="POST" action="{{ route('admin.book.update', $book->id_book) }}">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <!-- Thông tin khách hàng -->
                            <div class="col-md-6">
                                <h5 class="text-primary mb-3">Thông tin khách hàng</h5>

                                <div class="form-group">
                                    <label for="guest_name">Tên khách hàng <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('guest_name') is-invalid @enderror"
                                        id="guest_name" name="guest_name" value="{{ old('guest_name', $book->guest_name) }}"
                                        placeholder="Nhập tên khách hàng" rules="required">
                                    <span class="form-message"></span>
                                </div>

                                <div class="form-group">
                                    <label for="guest_phone">Số điện thoại <span class="text-danger">*</span></label>
                                    <input type="tel" class="form-control @error('guest_phone') is-invalid @enderror"
                                        id="guest_phone" name="guest_phone"
                                        value="{{ old('guest_phone', $book->guest_phone) }}"
                                        placeholder="Nhập số điện thoại" rules="required|phone">
                                    <span class="form-message"></span>
                                </div>

                                <div class="form-group">
                                    <label for="guest_email">Email<span class="text-danger">*</span></label>
                                    <input type="email" class="form-control @error('guest_email') is-invalid @enderror"
                                        id="guest_email" name="guest_email"
                                        value="{{ old('guest_email', $book->guest_email) }}" placeholder="Nhập email"
                                        rules="required|email">
                                    <span class="form-message"></span>
                                </div>
                            </div>

                            <!-- Thông tin đặt bàn -->
                            <div class="col-md-6">
                                <h5 class="text-primary mb-3">Thông tin đặt bàn</h5>

                                <div class="form-group">
                                    <label for="id_br">Chi nhánh <span class="text-danger">*</span></label>
                                    <select class="form-control @error('id_br') is-invalid @enderror" id="id_br"
                                        name="id_br" rules="required">
                                        <option value="">-- Chọn chi nhánh --</option>
                                        @foreach ($branches as $branch)
                                            <option value="{{ $branch->id_br }}"
                                                {{ old('id_br', $book->id_br) == $branch->id_br ? 'selected' : '' }}>
                                                {{ $branch->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <span class="form-message"></span>
                                </div>

                                <div class="form-group">
                                    <label for="date">Ngày đặt <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('date') is-invalid @enderror"
                                        id="date" name="date"
                                        value="{{ old('date', \Carbon\Carbon::parse($book->time)->format('Y-m-d')) }}"
                                        min="{{ date('Y-m-d') }}" rules="required">
                                    <span class="form-message"></span>
                                </div>

                                <div class="form-group">
                                    <label for="time">Giờ đặt <span class="text-danger">*</span></label>
                                    <select class="form-control @error('time') is-invalid @enderror" id="time"
                                        name="time" rules="required">
                                        <option value="">-- Chọn giờ --</option>
                                        @php
                                            $bookTime = \Carbon\Carbon::parse($book->time)->format('H:i');
                                        @endphp
                                        @for ($hour = 10; $hour <= 22; $hour++)
                                            <option value="{{ sprintf('%02d:00', $hour) }}"
                                                {{ $bookTime == sprintf('%02d:00', $hour) ? 'selected' : '' }}>
                                                {{ sprintf('%02d:00', $hour) }}
                                            </option>
                                            @if ($hour < 22)
                                                <option value="{{ sprintf('%02d:30', $hour) }}"
                                                    {{ $bookTime == sprintf('%02d:30', $hour) ? 'selected' : '' }}>
                                                    {{ sprintf('%02d:30', $hour) }}
                                                </option>
                                            @endif
                                        @endfor
                                    </select>
                                    <span class="form-message"></span>
                                </div>

                                <div class="form-group">
                                    <label for="id_table_type">Loại bàn <span class="text-danger">*</span></label>
                                    <select class="form-control @error('id_table_type') is-invalid @enderror"
                                        id="id_table_type" name="id_table_type" rules="required">
                                        <option value="">-- Chọn loại bàn --</option>
                                        @foreach ($tableTypes as $type)
                                            <option value="{{ $type->id }}"
                                                {{ old('id_table_type', $book->id_table_type) == $type->id ? 'selected' : '' }}>
                                                {{ $type->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <span class="form-message"></span>
                                </div>

                                <div class="form-group">
                                    <label for="id_table">Bàn cụ thể</label>
                                    <select class="form-control @error('id_table') is-invalid @enderror" id="id_table"
                                        name="id_table" disabled>
                                        @if ($book->table)
                                            <option value="{{ $book->table->id_table }}" selected >
                                                {{ $book->table->table_name }}
                                            </option>
                                        @endif
                                    </select>
                                    <small class="text-muted">Hệ thống sẽ tự chọn bàn trống</small>
                                    <span class="form-message"></span>
                                </div>

                                <div class="form-group">
                                    <label for="status">Trạng thái <span class="text-danger">*</span></label>
                                    <select class="form-control @error('status') is-invalid @enderror" id="status"
                                        name="status">
                                        @for ($i = 0; $i < 5; $i++)
                                            @if ($book->status == $i)
                                                <option value="{{ $i }}"
                                                {{ old('status', $book->status) == $i ? 'selected' : '' }}>
                                            </option>
                                            @endif
                                        @endfor
                                        {{-- <option value="0" {{ old('status', $book->status) == 0 ? 'selected' : '' }}>Giữ bàn</option>
                                                <option value="1" {{ old('status', $book->status) == 1 ? 'selected' : '' }}>Đã đặt</option>
                                                <option value="2" {{ old('status', $book->status) == 2 ? 'selected' : '' }}>Đã Hủy</option>
                                                <option value="3" {{ old('status', $book->status) == 3 ? 'selected' : '' }}>Hoàn thành</option>
                                                <option value="4" {{ old('status', $book->status) == 4 ? 'selected' : '' }}>Quá hạn</option> --}}
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12 text-center">
                                <button type="submit" class="btn btn-primary px-5">
                                    <i class="fas fa-save mr-2"></i>Cập nhật
                                </button>
                                <a href="{{ route('admin.book') }}" class="btn btn-secondary px-5 ml-2">
                                    Hủy
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

        </div>
        <!-- /.container-fluid -->

    </div>


@endsection
