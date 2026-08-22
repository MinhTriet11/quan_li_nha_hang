<!-- book -->
    <div class="reservation-overlay hidden" id="reservationOverlay">
        
        <div class="reservation-modal">
            <div>
            <button class="reservation-close" type="button" aria-label="Đóng">×</button>
            <h2>Đặt bàn ngay</h2>
            <form class="reservation-form"  id="reservationForm">
                @csrf
                <div class="form-group">
                    <label for="reservationName">Họ tên</label>
                    <input type="text" id="reservationName" name="name" rules="required" placeholder="Nhập họ tên" value="{{ $customer->name ?? '' }}" >
                    <span class="form-message"></span>
                </div>
                <div class="form-group">
                    <label for="reservationPhone">Số điện thoại</label>
                    <input type="tel" id="reservationPhone" name="phone" rules="required|phone" placeholder="Nhập số điện thoại" value="{{ $customer->phone ?? '' }}" >
                    <span class="form-message"></span>
                </div>
                <div class="form-group">
                    <label for="reservationEmail">Email</label>
                    <input type="email" id="reservationEmail" name="email" rules="required|email" placeholder="Nhập email" value="{{ $customer->email ?? '' }}" >
                    <span class="form-message"></span>
                </div>
                <div class="form-group">
                    <label for="reservationBranch">Chọn cơ sở</label>
                    <select id="reservationBranch" name="branch" rules="required">
                        <option value="" disabled selected>Chọn cơ sở</option>
                        @foreach ( $branches as $value )
                            <option value="{{ $value->id_br }}">{{ $value->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="reservationDate">Ngày đặt</label>
                        <input type="date" id="reservationDate" name="date" rules="required" min="{{ date('Y-m-d') }}">
                    </div>
                    <div class="form-group">
                        <label for="reservationTime">Giờ đặt</label>
                        <select id="reservationTime" name="time" rules="required" disabled>
                            <option value="" disabled selected>Chọn giờ</option>
                        </select>
                    </div>
                </div>
                <div class="form-group reservation-tables-group">
                    <label>Bàn khả dụng</label>
                    <p class="reservation-tables-hint" id="reservationTablesHint">Vui lòng chọn cơ sở để xem các bàn phù hợp.</p>
                    <div class="reservation-tables-list" id="reservationTablesList"></div>
                    <div class="reservation-hold-notice hidden" id="reservationHoldNotice">
                        <span>Bạn đã chọn bàn. Hệ thống giữ bàn trong</span>
                        <span class="reservation-hold-timer" id="reservationHoldTimer"></span>
                    </div>
                </div>
                <input type="hidden" id="reservationTable" name="id_table_type" value="">
                <button class="btn-reservation-submit" type="submit">Gửi yêu cầu</button>
            </form>
        </div>
        
    </div>