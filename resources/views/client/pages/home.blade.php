@extends('layouts.client_home')

@section('title', 'Trang chủ')
    
@section('content')

    <!-- about -->
    <div id="about" class="about-section">
        <div class="content-section">
            <div class="row">
                <div class="col-6">
                    <p class="text-section">Hương vị đặc trưng</p>
                    <h1 class="title-section">Món nướng được yêu thích</h1>
                    <div class="row list-favorite">
                        @foreach($grilledItems as $item)
                        <div class="col-3 favorite-item">
                            <div class="detail-item">
                                <img class="img-item" src="{{asset('storage/menu_items/'. $item->image )}}" alt="{{ $item->name }}">
                                <div class="content">
                                    <h4 class="title-item">{{ $item->name }}</h4>
                                    <p class="sub-item">{{ $item->description ?? 'Món nướng thơm ngon' }}</p>
                                    <p class="price-item">{{ number_format($item->price, 0, ',', '.') }}đ</p>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <div class="more-food d-flex justify-content-center mt-4">
                        <a class="btn-more px-4 py-2" href="#menu">Xem thêm món <i
                                class="fa-solid fa-circle-plus icon"></i></a>
                    </div>
                </div>
                <div class="col-6 banner">
                    <div class="banner-section">
                        <div class="row">
                            <div class="col-7 content-section">
                                <p class="text-section">Vì sao chọn Mây Nướng?</p>
                                <div class="row reason-list">
                                    <div class="col-2">
                                        <img class="img-section" src="{{ asset('assets/clients/image/icon01.png')}}" alt="">
                                    </div>
                                    <div class="col-10 detail-reason">
                                        <h1 class="text-reason">Nguyên liệu chất lượng</h1>
                                        <p class="subtext-reason">Tươi ngon mỗi ngày được chọn lọc kỹ lưỡng</p>
                                    </div>
                                </div>
                                <div class="row reason-list">
                                    <div class="col-2">
                                        <img class="img-section" src="{{ asset('assets/clients/image/icon02.png')}}" alt="">
                                    </div>
                                    <div class="col-10 detail-reason">
                                        <h1 class="text-reason">Không gian ấm cúng</h1>
                                        <p class="subtext-reason">Phục vụ tận tâm</p>
                                    </div>
                                </div>
                                <div class="row reason-list">
                                    <div class="col-2">
                                        <img class="img-section" src="{{ asset('assets/clients/image/icon03.png')}}" alt="">
                                    </div>
                                    <div class="col-10 detail-reason">
                                        <h1 class="text-reason">Phục vụ tận tâm</h1>
                                        <p class="subtext-reason">Đội ngũ nhân viên chuyên nghiệp</p>
                                    </div>
                                </div>
                                <div class="row reason-list">
                                    <div class="col-2">
                                        <img class="img-section" src="{{ asset('assets/clients/image/icon04.png')}}" alt="">
                                    </div>
                                    <div class="col-10 detail-reason">
                                        <h1 class="text-reason">Giá cả hợp lý</h1>
                                        <p class="subtext-reason">Chất lượng xứng đáng với giá tiền</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-5">
                                <img src="{{ asset('assets/clients/image/bg03.jpeg')}}" alt="Ảnh nhà hàng" class="banner-img">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- home -->
    <div id="home" class="home-section">
        <div class="text-section w-full text-center pt-2 pb-0">
            <i class="fa-solid fa-cloud mx-1 icon"></i>
            <p class="mb-0">Góc Nhà Mây</p>
        </div>
        <div class="subtext-section w-full text-center">
            <p><b>Mây Nướng</b> là nhà hàng chuyên các món nướng than hoa <br> với nguyên liệu tươi ngon, hương vị đậm
                đà và không gian ấm cúng. Chúng tôi mang đến những bữa ăn trọn vẹn, nơi gia đình và bạn bè cùng nhau
                chia sẻ những khoảnh khắc đáng nhớ.</p>
        </div>
        <div class="list-img w-full d-flex">
            @foreach ( $branchesImage as $value )
                <img class="img-item mx-2" src="{{ asset('assets/clients/image/'. $value->image)}}" alt="">
            @endforeach
        </div>
        <div class="more-branch w-full text-center">
            <a class="btn-more px-4 py-2" href="{{ route('client.branches') }}">Xem chi nhánh <i
                    class="fa-solid fa-arrow-right icon"></i></a>
        </div>
    </div>

    <!-- menu -->
    <div id="menu" class="menu-section">
        <div class="line w-full">

        </div>
        <div class="text-content w-full text-center">
            <p class="text-section">Mời bạn xem món</p>
        </div>

        <div class="menu-detail">
            <div class="row w-100">
                <div class="col-3">
                    <div class="content-detail w-full text-center">
                        <p class="text-detail">Mây Nướng</p>
                        <p class="subtext-detail">Hương vị nướng đặc trưng</p>
                        <div class="line-detail"></div>
                    </div>
                    <div class="list-category w-full">
                        <div class="list-detail m-4">
                            <ul class="mb-0 list-item">
                                @foreach ($categories as $key=>$value)
                                    <li class="item-detail category-item {{ $loop->first ? 'active' : '' }}" data-id="{{ $value->id_cate }}">
                                    <img class="img-item" src="{{ asset('storage/categories/'. $value->image ) }}" alt="{{ $value->name }}">
                                    <div class="name-cate mx-1">
                                        <p class="text-cate mb-0">{{ $value->name }}</p>
                                        <span class="num-cate">{{ $value->menuItems->count()}} món</span>
                                    </div>
                                    <i class="fa-solid fa-angle-right icon-cate"></i>
                                    </li>
                                    @if (!$loop->last)
                                        <hr class="m-0">
                                    @endif
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-9">
                    <div class="list-food">
                        <div class="row list-row" id="food-list">
                            @php $firstCategory = $categories->first(); @endphp
                            @foreach($firstCategory->menuItems as $item)
                            <div class="col-3 food-item" data-category-id="{{ $item->id_cate }}">
                                <div class="detail-item">
                                    <img class="img-item" src="{{asset('storage/menu_items/'. $item->image )}}" alt="{{ $item->name }}">
                                    <div class="content">
                                        <h4 class="title-item">{{ $item->name }}</h4>
                                        <p class="price-item mb-0">{{ number_format($item->price, 0, ',', '.') }}đ</p>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>

                        <!-- pagination -->
                        <div id="pagination-container"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- discount -->
    <div id="discount" class="discount-section mt-3">
        <div class="content-section">
            <div class="discount-header text-center">
                <h1 class="title-section">Khuyến mãi hấp dẫn</h1>
                <p class="discount-description">Khám phá những ưu đãi mới nhất dành cho bạn, giảm giá đến 30% và quà
                    tặng hấp dẫn.</p>
            </div>

            <div class="discount-slider">
                <button class="slider-btn left-btn" type="button" aria-label="Xem khuyến mãi trước">
                    <i class="fa-solid fa-chevron-left"></i>
                </button>
                <div class="slider-wrapper">
                    <div class="slider-track">
                        <div class="slider-item active" data-index="0">
                            <img src="{{asset('assets/clients/image/km01.jpeg')}}" alt="Khuyến mãi 1">
                            <div class="promo-card">
                                <p class="promo-label">Giảm 20%</p>
                                <h3>Set nướng gia đình</h3>
                                <p>Áp dụng từ thứ 2 đến thứ 6, tặng nước ngọt miễn phí.</p>
                            </div>
                        </div>
                        <div class="slider-item" data-index="1">
                            <img src="{{asset('assets/clients/image/km02.png')}}" alt="Khuyến mãi 2">
                            <div class="promo-card">
                                <p class="promo-label">Giảm 30%</p>
                                <h3>Món nướng thượng hạng</h3>
                                <p>Giảm giá chỉ trong tuần này, đặt trước để nhận ưu đãi.</p>
                            </div>
                        </div>
                        <div class="slider-item" data-index="2">
                            <img src="{{asset('assets/clients/image/khong-gian-3.png')}}" alt="Khuyến mãi 3">
                            <div class="promo-card">
                                <p class="promo-label">Tặng quà</p>
                                <h3>Khách hàng mới</h3>
                                <p>Đặt bàn lần đầu, nhận ngay món tráng miệng miễn phí.</p>
                            </div>
                        </div>
                    </div>
                    <div class="slider-dots">
                        <button class="dot active" data-index="0" aria-label="Slide 1"></button>
                        <button class="dot" data-index="1" aria-label="Slide 2"></button>
                        <button class="dot" data-index="2" aria-label="Slide 3"></button>
                    </div>
                </div>
                <button class="slider-btn right-btn" type="button" aria-label="Xem khuyến mãi tiếp theo">
                    <i class="fa-solid fa-chevron-right"></i>
                </button>
            </div>
        </div>
    </div>


    <!-- contact -->
    <div id="contact" class="contact-section">
        <div class="content-section w-full">
            <div class="row align-items-center content-detail">
                <div class="col-lg-6">
                    <div class="contact-card">
                        <h3>Hotline</h3>
                        <p><a href="tel:0900123456">0900 123 456</a></p>
                    </div>
                    <div class="contact-card">
                        <h3>Facebook</h3>
                        <p><a href="https://www.facebook.com/maynuong" target="_blank"
                                rel="noreferrer">facebook.com/maynuong</a></p>
                    </div>
                    <div class="contact-card">
                        <h3>TikTok</h3>
                        <p><a href="https://www.tiktok.com/@maynuong" target="_blank" rel="noreferrer">@maynuong</a></p>
                    </div>
                </div>

                <div class="col-lg-6 pl-2">
                    <h1 class="title-section">Liên hệ cho Mây nhé!</h1>
                </div>
            </div>
        </div>
    </div>

@endsection

