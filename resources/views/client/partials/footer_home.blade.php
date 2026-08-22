<!-- footer -->
    <footer class="footer-section">
        <div class="content-section w-full">
            <div class="row footer-inner">
                <div class="col-lg-3 footer-item footer-logo">
                    <h3>Mây Nướng</h3>
                    <img src="{{asset('assets/clients/image/logo.png')}}" alt="Mây Nướng Logo" class="footer-logo-img">
                </div>
                <div class="col-lg-3 footer-item footer-reservation">
                    <h3>Đặt bàn nhanh</h3>
                    <!-- <p>Gọi ngay hoặc để lại thông tin, nhân viên sẽ liên hệ xác nhận.</p> -->
                    <div class="footer-reserve-card">
                        <p><strong>Hotline:</strong> <a href="tel:0900123456">0900 123 456</a></p>
                        <button class="btn-footer-reservation nav-book" id="openReservation">Đặt bàn</button>
                    </div>
                </div>
                <div class="col-lg-3 footer-item footer-nav">
                    <div class="row">
                        <div class="col-lg-6">
                            <ul>
                                <li><a href="#about">Trang chủ</a></li>
                                <li><a href="#home">Giới thiệu</a></li>
                                <li><a href="{{ route('client.branches') }}">Chi nhánh</a></li>
                                <li><a href="#menu">Thực đơn</a></li>
                            </ul>
                        </div>
                        <div class="col-lg-6">
                            <ul>
                                <li><a href="#discount">Khuyến mãi</a></li>
                                <li><a href="tel:0900123456">Tuyển dụng</a></li>
                                <li><a href="#contact">Liên hệ</a></li>
                                <li><a href="#reservation">Đặt bàn</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 footer-item footer-branches">
                    <h3>Địa chỉ cơ sở</h3>
                    <p><strong>Hà Nội:</strong> Số 1, Phố A, Quận B</p>
                    <p><strong>Hồ Chí Minh:</strong> Số 2, Phố C, Quận D</p>
                    <p><strong>Đà Nẵng:</strong> Số 3, Phố E, Quận F</p>
                </div>
            </div>
        </div>
    </footer>