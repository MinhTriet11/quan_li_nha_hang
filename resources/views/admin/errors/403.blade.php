@extends('layouts.admin_home')

@section('title', 'Không có quyền truy cập')

@section('content')
            <div id="content">
                @include('admin.partials.topbar')
                <div class="container-fluid">
                    <div class="text-center my-5">
                        <div class="error mx-auto" data-text="403">403</div>
                        <p class="lead text-gray-800 mb-3">Không có quyền truy cập</p>
                        <p class="text-gray-600 mb-4">{{ $message ?? 'Bạn không có quyền truy cập chức năng này.' }}</p>
                        <a href="{{ route('admin.dashboard') }}" class="btn btn-primary">
                            <i class="fas fa-arrow-left"></i> Về trang chính
                        </a>
                    </div>
                </div>
            </div>

            <style>
                .error {
                    font-size: 7rem;
                    font-weight: 800;
                    color: #e74a3b;
                    text-shadow: 4px 4px 0 #f6c23e;
                }
            </style>
@endsection
