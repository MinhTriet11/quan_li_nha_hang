import { validatorRules } from "./auth_admin.js";

document.addEventListener("DOMContentLoaded", function () {
    const formLoginAdmin = document.getElementById("formLoginAdmin");

    const formBook =
        document.getElementById("formCreateBook") ||
        document.getElementById("formEditBook");

    if (formLoginAdmin) {
        validatorRules(formLoginAdmin);
    }

    const dateInput = document.getElementById("date");

    const timeSelect = document.getElementById("time");
    const branchSelect = document.getElementById("id_br");
    const tableTypeSelect = document.getElementById("id_table_type");

    const tableSelect = document.getElementById("id_table") ?? null;
    const statusSelect = document.getElementById("status");

    //tao ra mang khung gio
    function generateTimeSlots() {
        const today = new Date().toISOString().split("T")[0];

        const times = [];

        if (dateInput.value === today) {
            // Neu la ngay hom nay, chi hien thi gio tu gio hien tai + 1h
            const now = new Date();
            let currentHour = now.getHours() + 2;

            if (currentHour < 10) currentHour = 10;
            if (currentHour > 20) {
                timeSelect.disabled = true;
                timeSelect.innerHTML =
                    '<option value="" selected disabled>Đã hết giờ đặt</option>';
                return;
            }

            for (let hour = currentHour; hour <= 20; hour++) {
                times.push(`${hour.toString().padStart(2, "0")}:00`);
                if (hour < 20) {
                    times.push(`${hour.toString().padStart(2, "0")}:30`);
                }
            }
        } else {
            for (let hour = 10; hour <= 20; hour++) {
                times.push(`${hour.toString().padStart(2, "0")}:00`);
                if (hour < 20) {
                    times.push(`${hour.toString().padStart(2, "0")}:30`);
                }
            }
        }
        return times;
    }

    // get unit ingredient selection
    const selectedIngre = document.getElementById("selectedIngre");
    if(selectedIngre){
        selectedIngre.onchange = function (e) {
        let idIngre = e.target.value;
        fetch("/admin/ingredient/get-unit/" + idIngre)
            .then((response) => response.json())
            .then((data) => {
                const unitIngreInput =
                    document.getElementById("unitIngreInput");
                unitIngreInput.value = data.data;
            });
    };
    }
    

    //Bắt sự kiện khi nhập định lượng
    const recipeQuantity = document.querySelectorAll(".recipeQuantity");

    if (recipeQuantity) {
        recipeQuantity.forEach(re => {
            let foodId = re.dataset.id;

        re.onchange = function (e) {
            let quantityNew = e.target.value;

            fetch(`/admin/recipe/item/${foodId}`, {
                method: "PUT",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN":
                        document.querySelector('meta[name="csrf-token"]')
                            ?.content || "",
                },
                body: JSON.stringify({ quantity: quantityNew }),
            })
                .then(async (response) => {
                    const data = await response.json();

                    if (!response.ok) {
                        throw data;
                    }

                    return data;
                })
                .then((data) => {
                    if (data.success) {
                        toastr.success(data.message, "Thành công");
                    } else {
                        toastr.error(data.message || "Có lỗi xảy ra", "Lỗi");
                    }
                })
                .catch((error) => {
                    toastr.error(error.message || "Có lỗi xảy ra", "Lỗi");
                });
        };


        })
    }

    // render ra status theo dk [ 1, 0 => 2, 3 ; 2, 3, 4 ko cho doi ]
    function generateStatus() {
        const arrStatus = [
            "Chờ xác nhận",
            "Đã đặt",
            "Đã hoàn thành",
            "Đã hủy",
            "Quá hạn",
        ];
        const currentStatus = parseInt(statusSelect.value);

        // Xóa tất cả options hiện tại

        arrStatus.forEach((value, index) => {
            if (currentStatus === index) {
                statusSelect[0].innerText = arrStatus[index];
            }
        });

        // Nếu đang chờ xác nhận (0) hoặc đã đặt (1) → hiển thị Đã hủy (2), Đã hoàn thành (3)
        if (currentStatus === 0 || currentStatus === 1) {
            // Thêm option Đã hủy (2) và Đã hoàn thành (3)
            [2, 3].forEach((index) => {
                const opt = document.createElement("option");
                opt.value = index;
                opt.textContent = arrStatus[index];
                statusSelect.appendChild(opt);
            });
        } else {
            // Các trạng thái còn lại (2, 3, 4) - hiển thị trạng thái hiện tại và disable
            const opt = document.createElement("option");
            opt.value = currentStatus;
            opt.textContent = arrStatus[currentStatus];
            opt.selected = true;
            statusSelect.appendChild(opt);
            statusSelect.disabled = true;
        }
    }
    if (formBook) {
        if (formBook.id === "formEditBook") {
            generateStatus();
        }
    }

    // bat su kien khi ngay khi ngay duoc chon
    if (dateInput) {
        dateInput.addEventListener("change", function () {
            const timeSlots = generateTimeSlots();
            if (this.value) {
                timeSelect.disabled = false;
                timeSelect.innerHTML =
                    '<option value="">-- Chọn giờ --</option>';
                timeSlots.forEach((time) => {
                    const option = document.createElement("option");
                    option.value = time;
                    option.textContent = time;
                    timeSelect.appendChild(option);
                });
                loadTables();
            } else {
                timeSelect.disabled = true;
                timeSelect.innerHTML =
                    '<option value="">-- Chọn giờ --</option>';
                tableSelect.disabled = true;
                tableSelect.innerHTML =
                    '<option value="">-- Chọn loại bàn --</option>';
            }
        });
    }

    // tai ban kha dung
    function loadTables() {
        const branchId = branchSelect.value;
        const date = dateInput?.value;
        const time = timeSelect?.value;


        if (branchId && date && time) {
            
            if (formBook.id === "formCreateBook") {
                tableTypeSelect.disabled = false;
            }

            tableTypeSelect.innerHTML = '<option value="">Đang tải...</option>';

            fetch(
                `/admin/books/available-tables?branch=${branchId}&date=${date}&time=${time}`,
            )
                .then((response) => response.json())
                .then((data) => {
                    tableTypeSelect.innerHTML =
                        '<option value="">-- Chọn loại bàn --</option>';
                    if (data.data && data.data.length > 0) {
                        data.data.forEach((table) => {
                            const option = document.createElement("option");
                            option.value = table.table_type_id;
                            option.textContent = `${table.table_type_name} (${table.available_count} bàn trống)`;
                            tableTypeSelect.appendChild(option);
                        });
                    } else {
                        tableTypeSelect.innerHTML =
                            '<option value="">Không có bàn trống</option>';
                    }
                })
                .catch((error) => {
                    console.error("Error:", error);
                    tableTypeSelect.innerHTML =
                        '<option value="">Lỗi tải dữ liệu</option>';
                });
        } else {
            if (formBook.id === "formCreateBook") {
                tableTypeSelect.disabled = true;
            }
            tableTypeSelect.innerHTML =
                '<option value="">-- Chọn loại bàn --</option>';
        }
    }


    // Trigger on branch change
    if (formBook) {
        if (branchSelect) {
            branchSelect.addEventListener("change", loadTables);
        }
    }

    // Trigger on time change
    if (timeSelect) {
        timeSelect.addEventListener("change", loadTables);
    }

    // Handle form submission with fetch
    if (formBook) {
        // Bắt sự kiện thay đổi ->
        if (formBook.id === "formEditBook") {
            dateInput.addEventListener("change", loadTables);
            timeSelect.addEventListener("change", loadTables);

        }
        validatorRules(formBook);
    }

    //lay id cho update
    function getIdUpdate() {
        let pathParts = window.location.pathname.split("/").filter((p) => p);
        let id = pathParts[2]; // admin=0, books=1, {id}=2, edit=3

        return id;
    }

    //xu ly TableType
    const formTableTypeCreate = document.getElementById("formCreateTableType");
    const formTableTypeUpdate = document.getElementById("formEditTableType");

    if (formTableTypeCreate) {
        const formTable = formTableTypeCreate;
        validatorRules(formTable, "/admin/table-types", "create");
    }

    if (formTableTypeUpdate) {
        const formTable = formTableTypeUpdate;
        validatorRules(
            formTable,
            "/admin/table-types/" + getIdUpdate(),
            "update",
        );
    }

    //xu ly table
    const formTableCreate = document.getElementById("formCreateTable");

    if (formTableCreate) {
        const formTable = formTableCreate;
        validatorRules(formTable, "/admin/tables", "create");
    }
    const formTableEdit = document.getElementById("formEditTable");

    if (formTableEdit) {
        const formTable = formTableEdit;
        validatorRules(formTable, "/admin/tables/" + getIdUpdate(), "update");
    }

    //xử lý category
    const formCategoryCreate = document.getElementById("formCreateCategory");
    const formCategoryEdit = document.getElementById("formEditCategory");

    if (formCategoryCreate) {
        validatorRules(formCategoryCreate, "/admin/categories", "create");
    }

    if (formCategoryEdit) {
        validatorRules(
            formCategoryEdit,
            `/admin/categories/` + getIdUpdate(),
            "update",
        );
    }

    // Xử lý form menu item
    const formMenuItemCreate = document.getElementById("formCreateMenuItem");
    const formMenuItemEdit = document.getElementById("formEditMenuItem");

    if (formMenuItemCreate) {
        validatorRules(formMenuItemCreate, "/admin/menu-items", "create");
    }

    if (formMenuItemEdit) {
        validatorRules(
            formMenuItemEdit,
            `/admin/menu-items/` + getIdUpdate(),
            "update",
        );
    }

    // Xu ly form nguyen lieu
    const formCreateIngre = document.getElementById("formCreateIngre");
    const formEditIngre = document.getElementById("formIditIngre");

    if (formCreateIngre) {
        validatorRules(formCreateIngre, "/admin/ingredient", "create");
    }

    if (formEditIngre) {
        validatorRules(
            formEditIngre,
            "/admin/ingredient/" + getIdUpdate(),
            "update",
        );
    }

    //xu ly form them cong thuc
    const addRecipeItem = document.getElementById("addRecipeItem");

    if (addRecipeItem) {
        validatorRules(addRecipeItem, "/admin/recipe", "create");
    }

    // Xu ly form nhan vien
    const formUserCreate = document.getElementById("formCreateUser");
    const formUserEdit = document.getElementById("formEditUser");

    if (formUserCreate) {
        validatorRules(formUserCreate, "/admin/users", "create");
    }

    if (formUserEdit) {
        validatorRules(formUserEdit, "/admin/users/" + getIdUpdate(), "update");
    }

    // Xu ly form khach hang
    const formCustomerCreate = document.getElementById("formCreateCustomer");
    const formCustomerEdit = document.getElementById("formEditCustomer");

    if (formCustomerCreate) {
        validatorRules(formCustomerCreate, "/admin/customers", "create");
    }

    if (formCustomerEdit) {
        validatorRules(formCustomerEdit, "/admin/customers/" + getIdUpdate(), "update");
    }

    // Xu ly form chi nhanh
    const formBranchCreate = document.getElementById("formCreateBranch");
    const formBranchEdit = document.getElementById("formEditBranch");

    if (formBranchCreate) {
        validatorRules(formBranchCreate, "/admin/branches", "create");
    }

    if (formBranchEdit) {
        validatorRules(formBranchEdit, "/admin/branches/" + getIdUpdate(), "update");
    }


}); // End DOMContentLoaded
