
function validatorRules(formElement) {
    
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
                return value.length >= min
                    ? undefined
                    : `Vui lòng nhập mật khẩu lớn hơn ${min} ký tự`;
            };
        },
        phone: function (value) {
            var regex = /^(0[3|5|7|8|9])[0-9]{8}$/;
            return regex.test(value)
                ? undefined
                : "Vui lòng nhập số điện thoại hợp lệ";
        },
        confirm: function (selector) {
            return function (value) {
                return value === formElement.querySelector(selector).value
                    ? undefined
                    : "Vui lòng nhập mật khẩu chính xác";
            };
        },
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

                var btn = formElement.querySelector('.auth-btn')??null;
                if(btn){
                    btn.disabled = true;
                    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                }
                
                // Send AJAX request
                if (formElement.id === "formRegister") {
                    fetch("/auth-register", {
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
                            if (data.status === "success") {
                                toastr.success(data.message, "Thành công");
                                formElement.reset();
                                // Switch to login tab
                                setTimeout(() => {
                                    const loginTab =
                                        document.getElementById("login-tab");
                                    if (loginTab) {
                                        bootstrap.Tab.getOrCreateInstance(
                                            loginTab,
                                        ).show();
                                    }
                                }, 1500);
                            } else if (data.status === "error") {
                                toastr.error(data.message, "Lỗi");
                                btn.disabled = false;
                                btn.innerText = 'Đăng ký';
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

                if (formElement.id === "formLogin") {
                    fetch("/auth-login", {
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
                            if (data.status === "success") {
                                toastr.success(data.message, "Thành công");
                                setTimeout(() => {
                                    window.location.href = data.redirect;
                                }, 1000);
                            } else if (data.status === "error") {
                                toastr.error(data.message, "Lỗi");
                                btn.disabled = false;
                                btn.innerText = 'Đăng nhập';
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

                if (formElement.id === "formForgot") {
                    fetch("/forgot-password", {
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
                            if (data.status === "success") {
                                toastr.success(data.message, "Thành công");
                                setTimeout(()=>{
                                    btn.disabled = false;
                                    btn.innerText = 'Gửi liên kết';
                                }, 1000)
                            } else if (data.status === "error") {
                                toastr.error(data.message, "Lỗi");
                                btn.disabled = false;
                                btn.innerText = 'Gửi liên kết';
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

                if (formElement.id === "formReset") {
                    const token = window.location.pathname.split("/").pop();
                    fetch(`/reset-password/${token}`, {
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
                            if (data.status === "success") {
                                toastr.success(data.message, "Thành công");
                                setTimeout(() => {
                                    window.location.href = data.redirect;
                                }, 1000);
                            } else if (data.status === "error") {
                                toastr.error(data.message, "Lỗi");
                                btn.disabled = false;
                                btn.innerText = 'Xác nhận mật khẩu';
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

                //profile
                if (formElement.id === "pro-info") {
                    fetch("/profile-update", {
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
                            if (data.status === "success") {
                                toastr.success(data.message, "Thành công");
                            } else if (data.status === "error") {
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

                //pro-change-pass
                if (formElement.id === "pro-change-password") {
                    fetch("/change-password", {
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
                            if (data.status === "success") {
                                toastr.success(data.message, "Thành công");
                            } else if (data.status === "error") {
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


                //form-book
                if (formElement.id === "reservationForm") {

                    fetch("/reservation", {
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
                            if (data.status === "success") {
                                toastr.success("Cảm ơn! Yêu cầu đặt bàn đã được ghi nhận.", "Thành công")
                                formElement.reset(); 
                                clearTableHold();
                                closeReservation();
                            } else if (data.status === "error") {
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

            }
        };
    }
}

export { validatorRules };
