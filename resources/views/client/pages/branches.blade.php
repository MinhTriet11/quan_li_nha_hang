@extends('layouts.client')

@section('title', 'Chi nhánh mây nướng')

@section('content')
<div class="branches-page">
    <div class="branches-shell">
        <a class="back-home-link" href="{{ url('/') }}"><i class="fa-solid fa-arrow-left"></i> Quay lại trang chủ</a>

        <section class="branches-hero">
            <p class="branches-subtitle">Địa điểm Mây Nướng</p>
            <h1>Các chi nhánh hiện có</h1>
            <p>Chọn cơ sở gần bạn nhất để đến thưởng thức món nướng thơm ngon và nhận trải nghiệm tuyệt vời nhất.</p>
        </section>

        <div class="branches-grid">
            @foreach($branches as $branch)
            <article class="branch-card">
                <img class="branch-image" src="{{ asset('assets/clients/image/' . ($branch->image ?? 'bg011.jpeg')) }}" alt="Cơ sở Mây Nướng {{ $branch->name }}" loading="lazy">
                <h2><i class="fa-solid fa-location-dot"></i> {{ $branch->name }}</h2>
                <p><i class="fa-solid fa-map-marker-alt"></i> {{ $branch->address }}</p>
                <a class="branch-map-link" href="{{ $branch->location ?? '#' }}" target="_blank" rel="noreferrer">
                    <i class="fa-solid fa-route"></i> Chỉ đường trên Google Maps
                </a>
            </article>
            @endforeach
        </div>
    </div>
</div>
    
@endsection

