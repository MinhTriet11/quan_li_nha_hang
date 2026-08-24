function validatorRules(formElement , url = '', type = '') {
    
    var formRules = {};
    var _this = this;

    function getParent(element, selector) {
        while (element.parentElement) {
            if (element.parentElement.matches(selector)) {
                return element.parentElement;
            }
            element = element.parentElement;
        }
    }

    var validatorRules = {
        required: function (value) {
            return value ? undefined : "Vui lòng không bỏ trống";
        },
        email: function (value) {
            var regex = /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/;
            return regex.test(value) ? undefined : "Vui lòng nhập email hợp lệ";
        },
        min: function (min) {
            return function (value) {
                // Bỏ qua khi rỗng - cho phép trường optional
                if (!value || !value.length) return undefined;
                return value.length >= min
                    ? undefined
                    : `Vui lòng nhập mật khẩu lớn hơn ${min} ký tự`;
            };
        },
        phone: function (value) {
            // Bỏ qua khi rỗng - phone là trường optional
            if (!value || !value.trim()) return undefined;
            var regex = /^(0[3|5|7|8|9])[0-9]{8}$/;
            return regex.test(value.trim())
                ? undefined
                : "Vui lòng nhập số điện thoại hợp lệ";
        },
        confirm: function (selector) {
            return function (value) {
                // Nếu rỗng thì bỏ qua (cho phép để trống khi không đổi pass)
                if (!value) return undefined;
                // Hỗ trợ cả #id và name attribute
                var target;
                if (selector && selector.charAt(0) === '#') {
                    target = formElement.querySelector(selector);
                } else if (selector) {
                    target = formElement.querySelector('[name="' + selector + '"]');
                }
                if (!target) {
                    return "Không tìm thấy trường xác nhận";
                }
                return value === target.value
                    ? undefined
                    : "Vui lòng nhập mật khẩu chính xác";
            };
        },
        string: function(value){
            var regex = /^\D+/;
            return regex.test(value)
                ? undefined
                : "Vui lòng nhập tên hợp lệ";
        }
    };

    if (formElement) {
        var inputs = document.querySelectorAll("[name][rules]");


        for (var input of inputs) {
            var rules = input.getAttribute("rules").split("|");

            for (var rule of rules) {
                var ruleInfo;
                var isRuleHasValue = rule.includes(":");

                if (isRuleHasValue) {
                    ruleInfo = rule.split(":");
                    rule = ruleInfo[0];
                }

                var ruleFunc = validatorRules[rule];

                if (isRuleHasValue) {
                    ruleFunc = ruleFunc(ruleInfo[1]);
                }

                if (Array.isArray(formRules[input.name])) {
                    formRules[input.name].push(ruleFunc);
                } else {
                    formRules[input.name] = [ruleFunc];
                }
            }
            //lang nghe su kien de validate (blur, change,...)
            input.onblur = handleValidate;
            input.oninput = handleClearError;
        }

        function handleValidate(event) {
            // Bỏ qua khi input bị disable hoặc được đánh dấu skip (vd: dropdown chi nhánh theo role)
            if (event.target.disabled || event.target.dataset && event.target.dataset.noValidate === '1') {
                var formGroup = getParent(event.target, ".form-group");
                if (formGroup) formGroup.classList.remove("invalid");
                var fm = formGroup ? formGroup.querySelector(".form-message") : null;
                if (fm) fm.innerText = "";
                return true;
            }

            var rules = formRules[event.target.name];
            var errorMessage;

            for (var rule of rules) {
                errorMessage = rule(event.target.value);

                if (errorMessage) break;
            }

            if (errorMessage) {
                var formGroup = getParent(event.target, ".form-group");
                if (formGroup) {
                    var formMessage = formGroup.querySelector(".form-message");
                    if (formMessage) {
                        formMessage.innerText = errorMessage;
                        formGroup.classList.add("invalid");
                    }
                }
            }

            return !errorMessage;
        }

        function handleClearError() {
            var formGroup = getParent(event.target, ".form-group");

            if (formGroup.classList.contains("invalid")) {
                formGroup.classList.remove("invalid");
            }

            var formMessage = formGroup.querySelector(".form-message");
            if (formMessage) {
                formMessage.innerText = "";
            }
        }

        formElement.onsubmit = function (event) {
            
            event.preventDefault();

            var inputs = formElement.querySelectorAll("[name][rules]");
            var isValid = true;

            for (var input of inputs) {
                // Bỏ qua input disabled hoặc được đánh dấu skip
                if (input.disabled) continue;
                if (input.dataset && input.dataset.noValidate === '1') continue;
                if (!handleValidate({ target: input })) {
                    isValid = false;
                }
            }

            if (isValid) {
                var enableInputs = formElement.querySelectorAll("[name]");

                var formInputs = Array.from(enableInputs).reduce(function (
                    values,
                    input,
                ) {
                    switch (input.type) {
                        case "radio":
                        case "checkbox":
                            break;

                        default:
                            values[input.name] = input.value;
                            break;
                    }

                    return values;
                }, {});

                if(formElement.id === "formCreateBook"){
                    var btn = formElement.querySelector('.btn').disabled = true;
                }

                var btn = formElement.querySelector('.btn-login')??null;
                if(btn){
                    btn.disabled = true;
                    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                }

                if (formElement.id === "formLoginAdmin") {
                    fetch("/admin/login", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN":
                                document.querySelector(
                                    'meta[name="csrf-token"]',
                                )?.content || "",
                        },
                        body: JSON.stringify(formInputs),
                    })
                        .then((response) => response.json())
    
                        .then((data) => {
                            if (data.status === 'success') {
                                toastr.success(data.message, "Thành công");
                                window.location.href = data.redirect;
                                
                            } else if (data.status === 'error') {
                                toastr.error(data.message, "Lỗi");
                                btn.disabled = false;
                                btn.innerHTML = 'Đăng nhập';
                            }
                        })
                        .catch((error) => {
                            toastr.error(
                                "Đã xảy ra lỗi, vui lòng thử lại",
                                "Lỗi",
                            );
                            console.error("Error:", error);
                        });
                }


                if (formElement.id === "formCreateBook") {
                    fetch("/admin/books/", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN":
                                document.querySelector(
                                    'meta[name="csrf-token"]',
                                )?.content || "",
                        },
                        body: JSON.stringify(formInputs),
                    })
                        .then((response) => response.json())
    
                        .then((data) => {
                            if (data.success) {
                                toastr.success(data.message, "Thành công");
                                setTimeout(()=>{
                                    var btn = formElement.querySelector('.btn').disabled = false;
                                    window.location.reload()
                                }, 1000)
                            } else if (data.error) {
                                toastr.error(data.message, "Lỗi");
                            }
                        })
                        .catch((error) => {
                            toastr.error(
                                "Đã xảy ra lỗi, vui lòng thử lại",
                                "Lỗi",
                            );
                            console.error("Error:", error);
                        });
                }

                if (formElement.id === "formEditBook") {
                    // Lấy ID từ URL - /admin/books/{id}/edit
                    let pathParts = window.location.pathname.split("/").filter(p => p);
                    let id = pathParts[2]; // admin=0, books=1, {id}=2, edit=3

                    if (!id || id === 'edit') {
                        toastr.error("Không tìm thấy ID đặt bàn", "Lỗi");
                        return;
                    }

                    fetch(`/admin/books/${ id }`, {
                        method: "PUT",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN":
                                document.querySelector(
                                    'meta[name="csrf-token"]',
                                )?.content || "",
                        },
                        body: JSON.stringify(formInputs),
                    })
                        .then((response) => response.json())
    
                        .then((data) => {
                            if (data.success) {
                                toastr.success(data.message, "Thành công");
                                setTimeout(() => {
                                    window.location.href = data.redirect;
                                })
                            } else if (data.error) {
                                toastr.error(data.message, "Lỗi");
                            }
                        })
                        .catch((error) => {
                            toastr.error(
                                "Đã xảy ra lỗi, vui lòng thử lại",
                                "Lỗi",
                            );
                            console.error("Error:", error);
                        });
                }

                //create
                if (type == 'create') {
                    console.log(formElement)
                    // Check if form has file input
                    const hasFileInput = formElement.querySelector('input[type="file"]');

                    if (hasFileInput) {
                        // Use FormData for forms with file upload
                        const formData = new FormData(formElement);
                        fetch(url, {
                            method: "POST",
                            headers: {
                                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')?.content || "",
                            },
                            body: formData,
                        })
                        .then(async (response) => {
                            const data = await response.json();
                            if(!response.ok){
                                throw data
                            }
                            return data
                        })
                        .then((data) => {
                            if (data.success) {
                                toastr.success(data.message, "Thành công");
                                formElement.reset()

                                if(data.redirect){
                                    window.location.href =  data.redirect
                                }
                            } else if (data.error) {
                                toastr.error(data.message, "Lỗi");
                            }
                        })
                        .catch((error) => {
                            toastr.error(error.message, "Lỗi");
                        });
                    } else {
                        // Original JSON approach for forms without file
                        fetch(url, {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json",
                                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')?.content || "",
                            },
                            body: JSON.stringify(formInputs),
                        })
                        .then(async (response) => {
                            const data = await response.json();
                            if(!response.ok){
                                throw data
                            }
                            return data
                        })
                        .then((data) => {
                            if (data.success) {
                                toastr.success(data.message, "Thành công");
                                formElement.reset()

                                if(data.redirect){
                                    window.location.href = data.redirect;
                                }
                            } else if (data.error) {
                                toastr.error(data.message, "Lỗi");
                            }
                        })
                        .catch((error) => {
                            toastr.error(error.message, "Lỗi");
                        });
                    }
                }

                //update
                if (type == 'update') {
                    // Check if form has file input
                    const hasFileInput = formElement.querySelector('input[type="file"]');

                    if (hasFileInput) {
                        // Use FormData for forms with file upload
                        const formData = new FormData(formElement);
                        formData.append('_method', 'PUT');
                        fetch(url, {
                            method: "POST",
                            headers: {
                                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')?.content || "",
                            },
                            body: formData,
                        })
                        .then(async (response) => {
                            const data = await response.json();
                            if(!response.ok){
                                throw data
                            }
                            return data
                        })
                        .then((data) => {
                            if (data.success) {
                                toastr.success(data.message, "Thành công");
                            } else if (data.error) {
                                toastr.error(data.message, "Lỗi");
                            }
                        })
                        .catch((error) => {
                            toastr.error(error.message, "Lỗi");
                        });
                    } else {
                        // Original JSON approach for forms without file
                        fetch(url, {
                        method: "PUT",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN":
                                document.querySelector(
                                    'meta[name="csrf-token"]',
                                )?.content || "",
                        },
                        body: JSON.stringify(formInputs),
                    })
                        .then(async (response) => {
                            const data = await response.json();

                            if(!response.ok){
                                throw data
                            }

                            return data
                        })
    
                        .then((data) => {
                            if (data.success) {
                                toastr.success(data.message, "Thành công");
                                
                            } else if (data.error) {
                                toastr.error(data.message, "Lỗi");
                            }
                        })
                        .catch((error) => {
                            toastr.error(
                                error.message,
                                "Lỗi",
                            );
                            
                        });
                }
            }
        }
    }
}
}
export { validatorRules };
