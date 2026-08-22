@extends('layouts.client')

@section('title', '404 — Không tìm thấy trang')

@section('content')
<div class="container text-center my-5">
    <h1 class="display-1">404</h1>
    <p class="lead">Xin lỗi — trang bạn tìm không tồn tại hoặc đã bị di chuyển.</p>
    <a href="{{ url('/') }}" class="btn btn-primary mt-3">Về trang chủ</a>
</div>
@endsection
