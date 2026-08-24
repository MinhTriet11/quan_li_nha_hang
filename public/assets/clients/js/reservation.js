import { validatorRules } from "./auth.js";

export function reservation() {
    const bookButtons = Array.from(
        document.querySelectorAll(".btn-book, .nav-book"),
    );
    const overlay = document.getElementById("reservationOverlay");
    const closeBtn = document.querySelector(".reservation-close");
    const reservationForm = document.querySelector(".reservation-form");
    const branchSelect = document.getElementById("reservationBranch");
    const reservationTablesHint = document.getElementById("reservationTablesHint",);
    const reservationTablesList = document.getElementById("reservationTablesList",);
    const reservationHoldNotice = document.getElementById("reservationHoldNotice",);
    const reservationTableInput = document.getElementById("reservationTable");
    const reservationNameInput = document.getElementById("reservationName");
    const reservationPhoneInput = document.getElementById("reservationPhone");
    const reservationEmailInput = document.getElementById("reservationEmail");
    const reservationDateInput = document.getElementById("reservationDate");
    const reservationTimeInput = document.getElementById("reservationTime");

    // Lay danh sach cac khung gio da bi chiem (anh huong cua booking khac trong cung chi nhanh).
    // Moi booking chiem 2 gio quanh no, nen booking luc 18h se chan ca 16h, 17h, 19h, 20h.
    // let bookedSlots = [];

    // async function fetchBookedSlots(branchId, date) {
    //     if (!branchId || !date) {
    //         bookedSlots = [];
    //         return;
    //     }
    //     try {
    //         const res = await fetch(`/book/booked-slots?branch=${branchId}&date=${date}`);
    //         const data = await res.json();
    //         bookedSlots = data.slots || [];
    //     } catch (e) {
    //         console.error("Lỗi khi lấy dữ liệu các khung giờ đã đặt:", e);
    //         bookedSlots = [];
    //     }
    // }

    // Tao option cho mot khung gio, disable neu no trung bookedSlot hoac qua gio hien tai (ngay hom nay).
    // function buildTimeOption(value) {
    //     const isBooked = bookedSlots.includes(value);
    //     const disabledAttr = isBooked ? "disabled" : "";
    //     const label = isBooked ? `${value} (đã có khách)` : value;
    //     return `<option value="${value}" ${disabledAttr}>${label}</option>`;
    // }

    function timeOptionsHTML(minHour, maxHour) {
        let html = "";
        for (let hour = minHour; hour <= maxHour; hour++) {
            //html += buildTimeOption(`${String(hour).padStart(2, "0")}:00`);
            html += `<option value="${String(hour).padStart(2, "0")}:00">${String(hour).padStart(2, "0")}:00</option>`;
            if (hour < maxHour) {
                
                html += `<option value="${String(hour).padStart(2, "0")}:30">${String(hour).padStart(2, "0")}:30</option>`;
            }
        }
        return html;
    }

    // Cap nhat gio khi ngay (hoac chi nhanh) thay doi.
    // Sau khi load xong bookedSlots, cac khung gio bi chiem se bi disable.
    async function updateTimeSlots() {
        const selectedDate = reservationDateInput.value;
        const branchId = branchSelect.value;
        const today = new Date().toISOString().split("T")[0];
        reservationTimeInput.innerHTML = '<option value="" disabled selected>Chọn giờ</option>';

        if (!selectedDate) {
            reservationTimeInput.disabled = true;
            return;
        }

        // Lay bookedSlots truoc khi render option
        //await fetchBookedSlots(branchId, selectedDate);

        if (selectedDate === today) {
            const now = new Date();
            let currentHour = now.getHours() + 2; //dat truoc 2 tieng

            if (currentHour < 10) currentHour = 10;
            if (currentHour > 20) {
                reservationTimeInput.disabled = true;
                reservationTimeInput.innerHTML = '<option value="" selected disabled>Đã hết giờ đặt</option>';
                return;
            }

            reservationTimeInput.innerHTML += timeOptionsHTML(currentHour, 20);
        } else {
            reservationTimeInput.innerHTML += timeOptionsHTML(10, 20);
        }
        reservationTimeInput.disabled = false;
    }

    // Ham load tables - chi load khi da chon du chi nhanh + ngay + gio
    function loadAvailableTables() {
        const branchId = branchSelect.value;
        const date = reservationDateInput.value;
        const time = reservationTimeInput.value;

        // Khong load neu chua chon day du
        if (!branchId || !date || !time) {
            if (reservationTablesHint) {
                reservationTablesHint.textContent = "Vui lòng chọn đầy đủ chi nhánh, ngày và giờ để xem bàn khả dụng.";
            }
            if (reservationTablesList) {
                reservationTablesList.innerHTML = "";
            }
            clearTableHold();
            return;
        }

        reservationTablesList.innerHTML = '<div class="text-center py-3">Đang tải...</div>';
        clearTableHold();

        // Su dung API available-tables voi day du tham so
        fetch(`/book/available-tables?branch=${branchId}&date=${date}&time=${time}`)
            .then((response) => response.json())
            .then((data) => {
                reservationTablesList.innerHTML = "";

                if (data.data.length === 0) {
                    reservationTablesHint.textContent = "Không có bàn trống cho khung giờ này. Vui lòng chọn ngày/giờ khác.";
                    return;
                }

                reservationTablesHint.innerHTML = `Có <strong>${data.data.length}</strong> loại bàn trống. Vui lòng chọn một bàn.`;

                // Render tat ca cac ban
                data.data.forEach((table) => {
                    const button = document.createElement("button");
                    button.type = "button";
                    button.className = "reservation-table-card";
                    button.dataset.tableId = table.table_type_id;
                    button.innerHTML = `
                        
                        <span class="reservation-table-name">${table.table_type_name || 'Bàn thường'}</span>
                        <span class="reservation-table-meta">Số lượng: ${table.available_count}</span>
                    `;

                    button.addEventListener("click", function () {
                        if (reservationTableInput) {
                            reservationTableInput.value = table.table_type_id;
                        }

                        const allCards = document.querySelectorAll(".reservation-table-card");
                        allCards.forEach((card) => card.classList.remove("selected"));

                        this.classList.add("selected");
                    });

                    reservationTablesList.appendChild(button);
                });
            })
            .catch((error) => {
                console.error("Error fetching tables:", error);
                reservationTablesHint.textContent = "Có lỗi xảy ra khi tải danh sách bàn.";
                reservationTablesList.innerHTML = "";
            });
    }

    if (reservationDateInput && reservationTimeInput) {
        reservationDateInput.addEventListener("change", function () {
            updateTimeSlots();
            // Reset table selection when date changes
            if (reservationTablesHint) {
                reservationTablesHint.textContent = "Vui lòng chọn đầy đủ chi nhánh, ngày và giờ để xem bàn khả dụng.";
            }
            if (reservationTablesList) {
                reservationTablesList.innerHTML = "";
            }
            clearTableHold();
        });
    }

    function clearTableHold() {
        if (reservationHoldNotice) {
            reservationHoldNotice.classList.add("hidden");
        }
        if (reservationTableInput) {
            reservationTableInput.value = "";
        }
        if (reservationTablesList) {
            reservationTablesList
                .querySelectorAll(".reservation-table-card")
                .forEach((card) => card.classList.remove("selected"));
        }
    }

    function openReservation() {
        if (!overlay) return;
        overlay.classList.remove("hidden");
        document.body.style.overflow = "hidden";
        if (reservationForm) {
            reservationForm.reset();
        }
        if (reservationTimeInput) {
            reservationTimeInput.innerHTML = '<option value="" disabled selected>Chọn giờ</option>';
            reservationTimeInput.disabled = true;
        }
        if (reservationTablesHint) {
            reservationTablesHint.textContent = "Vui lòng chọn đầy đủ chi nhánh, ngày và giờ để xem bàn khả dụng.";
        }
        if (reservationTablesList) {
            reservationTablesList.innerHTML = "";
        }
        clearTableHold();
    }

    function closeReservation() {
        if (!overlay) return;
        overlay.classList.add("hidden");
        document.body.style.overflow = "";
        clearTableHold();

        // Reset time select
        if (reservationTimeInput) {
            reservationTimeInput.innerHTML =
                '<option value="" disabled selected>Chọn giờ</option>';
            reservationTimeInput.disabled = true;
        }
    }

    bookButtons.forEach((button) => {
        button.addEventListener("click", (event) => {
            event.preventDefault();
            openReservation();
        });
    });

    if (branchSelect) {
        branchSelect.addEventListener("change", (event) => {
            // Khi doi chi nhanh, refetch bookedSlots va refresh time slots
            updateTimeSlots();
            loadAvailableTables();
        });
    }

    // Trigger load tables when time changes
    if (reservationTimeInput) {
        reservationTimeInput.addEventListener("change", function () {
            loadAvailableTables();
        });
    }

    if (closeBtn) {
        closeBtn.addEventListener("click", closeReservation);
    }

    if (overlay) {
        overlay.addEventListener("click", (event) => {
            if (event.target === overlay) {
                closeReservation();
            }
        });
    }

    if (reservationForm) {
        validatorRules(reservationForm);
    }

    // Gán vào window để auth.js có thể dùng
window.clearTableHold = clearTableHold;
window.closeReservation = closeReservation;
}


