<!-- header -->
<section class="hero">
    <nav class="navbar navbar-expand-lg ">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{ route('client.home') }}">
                <img src="{{ asset('assets/clients/image/logo.png') }}" alt="Logo" width="120" height="80">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar"
                aria-controls="offcanvasNavbar" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasNavbar"
                aria-labelledby="offcanvasNavbarLabel">
                <div class="offcanvas-header">
                    <h5 class="offcanvas-title" id="offcanvasNavbarLabel">Mây Nướng</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                </div>
                <div class="offcanvas-body">
                    <ul class="navbar-nav justify-content-end flex-grow-1 pe-3">
                        <li class="nav-item">
                            <a class="nav-link active" aria-current="page" href="#about">Trang chủ</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#home">Giới thiệu</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#menu">Thực đơn</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#discount">Khuyến mãi</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#contact">Liên hệ</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link nav-book" href="#">
                                <i class="fa-regular fa-calendar"></i>Đặt bàn ngay
                            </a>
                        </li>
                        @if (Auth::guard('customer')->check())
                            <li class="nav-item">
                                <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#">
                                    <i class="fa-solid fa-user"></i>
                                </a>

                                <ul class="dropdown-menu dropdown-menu-dark top-20 start-0 translate-middle-x">
                                    <li><a class="dropdown-item"
                                            href="#">{{ Auth::guard('customer')->user()->name }}</a></li>
                                    <li><a class="dropdown-item" href="/profile">Thông tin</a></li>
                                    <li><a class="dropdown-item" href="/auth-logout">Đăng xuất</a></li>
                                </ul>
                            </li>
                        @else
                            <li class="nav-item">
                                <a class="nav-link" href="/auth">
                                    <i class="fa-solid fa-user"></i>
                                </a>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </nav>
    <div class="slice-content">
        <div class="row align-content-center sub-content">
            <div class="col-lg-7">
                <p class="slice-subtitles">
                    Chào mừng đến với
                </p>
                <p class="slice-title">
                    Mây Nướng
                </p>
                <p class="slice-description">
                    Không gian ấm cúng, món nướng thơm ngon, <br>
                    cùng bạn bè và người thân tận hưởng những khoảnh khắc
                    tuyệt vời.
                </p>

                <div class="d-flex slice-btn">
                    <ul class="list-btn">
                        <li class="btn-item menu">
                            <a class="btn-menu" href="#menu">Xem thực đơn <i class="fa-solid fa-utensils"></i></a>
                        </li>
                        <li class="btn-item book">
                            <a class="btn-book" href="#">Đặt bàn ngay <i class="fa-regular fa-calendar"> </i></a>
                        </li>

                    </ul>
                </div>
            </div>
            <div class="col-lg-5 time">
                <div class="time-open">
                    <h1 class="time-item time-text"><i class="fa-regular fa-clock"></i> Giờ mở cửa</h1>
                    <p class="time-item time-morning">10:00 - 14:00</p>
                    <p class="time-item time-line"></p>
                    <p class="time-item time-night">16:30 - 22:30</p>
                    <h3 class="time-item time-subtext">Tất cả các ngày trong tuần</h3>
                </div>

                <div class="social">
                    <ul class="social-lists">
                        <li class="social-item fb">
                            <a class="social-fb" target="blank" href="https://www.facebook.com/maynuongq1"><i class="fa-brands fa-facebook-f"></i></a>
                        </li>
                        <li class="social-item tiktok">
                            <a class="social-tiktok" target="blank" href="https://www.tiktok.com/@maynuongne"><i class="fa-brands fa-tiktok"></i></a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    </div>

</section>
