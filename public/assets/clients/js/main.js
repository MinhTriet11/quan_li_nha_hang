import { validatorRules } from "./auth.js";
import { reservation } from "./reservation.js";
//slider
const slides = Array.from(document.querySelectorAll(".slider-item"));
const dots = Array.from(document.querySelectorAll(".dot"));
const prevBtn = document.querySelector(".left-btn");
const nextBtn = document.querySelector(".right-btn");
let currentIndex = 0;

//slider-promo
const promoImages = Array.from(
    document.querySelectorAll(".slider-item img"),
);
const promoModal = document.getElementById("promoModal");
const promoModalImage = document.getElementById("promoModalImage");
const promoModalLabel = document.getElementById("promoModalLabel");
const promoModalTitle = document.getElementById("promoModalTitle");
const promoModalText = document.getElementById("promoModalText");
const promoModalClose = document.querySelector(".promo-modal-close");

const registerItem = document.querySelector(".register-item");
const forgotItem = document.querySelector(".forgot-item");
const loginItem = document.querySelector(".login-item");

const registerTab = document.getElementById("register-tab");
const loginTab = document.getElementById("login-tab");
const forgotTab = document.getElementById("forgot-tab");

const registerPane = document.getElementById("register-pane");
const loginPane = document.getElementById("login-pane");
const forgotPane = document.getElementById("forgot-pane");

//Auth
const formRegister = document.getElementById("formRegister");

validatorRules(formRegister);

const formLogin = document.getElementById("formLogin");

validatorRules(formLogin);

const formForgot = document.getElementById("formForgot");

validatorRules(formForgot);

const formReset = document.getElementById("formReset");

validatorRules(formReset);

//Profile
const proInfo = document.getElementById("pro-info");

validatorRules(proInfo);

const proChangePass = document.getElementById("pro-change-password");

validatorRules(proChangePass);

// ===== PROFILE PAGE =====
// Use Bootstrap Tab plugin for profile navigation
const profileListGroup = document.querySelector(".list-group-flush");
if (profileListGroup) {
    const triggerTabList = profileListGroup.querySelectorAll(
        '[data-bs-toggle="list"]',
    );
    triggerTabList.forEach((triggerEl) => {
        const tabTrigger = new bootstrap.Tab(triggerEl);
        triggerEl.addEventListener("click", (event) => {
            event.preventDefault();
            tabTrigger.show();
        });
    });
}

// Booking filters
const filterBtns = document.querySelectorAll(".filter-btn");
if (filterBtns.length > 0) {
    filterBtns.forEach((btn) => {
        btn.addEventListener("click", () => {
            filterBtns.forEach((b) => b.classList.remove("active"));
            btn.classList.add("active");
        });
    });
}

//Tab Content

if (registerItem) {
    registerItem.onclick = (e) => {
        e.preventDefault();
        bootstrap.Tab.getOrCreateInstance(registerTab).show();
    };
}

if (forgotItem) {
    forgotItem.onclick = (e) => {
        e.preventDefault();
        bootstrap.Tab.getOrCreateInstance(forgotTab).show();
    };
}

if (loginItem) {
    loginItem.onclick = (e) => {
        e.preventDefault();
        bootstrap.Tab.getOrCreateInstance(loginTab).show();
    };
}

//================================================ Welcome Promotion Popup ====================================================

const welcomePromoOverlay = document.getElementById("welcomePromoOverlay");
const welcomePromoClose = document.getElementById("welcomePromoClose");
const welcomePromoBook = document.getElementById("welcomePromoBook");
const welcomePromoViewMore = document.getElementById(
    "welcomePromoViewMore",
);

function populateWelcomePromo() {
    //lay thong tin slider
    const firstPromoItem = document.querySelector(
        '.slider-item[data-index="1"]',
    );

    if (firstPromoItem) {
        const img = firstPromoItem.querySelector("img");
        const promoCard = firstPromoItem.querySelector(".promo-card");

        if (img) {
            console.log(img);
            document.getElementById("welcomePromoImg").src = img.src;
        }

        if (promoCard) {
            const label = promoCard.querySelector(".promo-label");
            const title = promoCard.querySelector("h3");
            const desc = promoCard.querySelector("p:not(.promo-label)");

            if (label) {
                document.getElementById("welcomePromoLabel").textContent =
                    label.textContent;
            }
            if (title) {
                document.getElementById("welcomePromoTitle").textContent =
                    title.textContent;
            }
            if (desc) {
                document.getElementById("welcomePromoDesc").textContent =
                    desc.textContent;
            }
        }
    }
}

//hien thi popup
function showWelcomePromo() {
    //kiem tra localStorage co key chua
    const lastShown = localStorage.getItem("welcomePromoShown");
    const today = new Date().toDateString();

    if (lastShown !== today) {
        populateWelcomePromo();

        //hien thi sau thoi gian
        setTimeout(() => {
            if (welcomePromoOverlay) {
                welcomePromoOverlay.classList.remove("hidden");
            }
        }, 800);

        // luu key vao local
        localStorage.setItem("welcomePromoShown", today);
    }
}

function closeWelcomePromo() {
    if (welcomePromoOverlay) {
        welcomePromoOverlay.classList.add("hidden");
    }
}

if (welcomePromoClose) {
    welcomePromoClose.addEventListener("click", closeWelcomePromo);
}

if (welcomePromoBook) {
    welcomePromoBook.addEventListener("click", () => {
        closeWelcomePromo();
        // mo dat ban
        if (overlay) {
            overlay.classList.remove("hidden");

            //thanh cuon an
            document.body.style.overflow = "hidden";
        }
    });
}

if (welcomePromoViewMore) {
    welcomePromoViewMore.addEventListener("click", () => {
        closeWelcomePromo();
        //hien thi khuyen mai slider
        const discountSection = document.getElementById("discount");
        if (discountSection) {
            discountSection.scrollIntoView({ behavior: "smooth" });
        }
    });
}

if (welcomePromoOverlay) {
    welcomePromoOverlay.addEventListener("click", (event) => {
        if (event.target === welcomePromoOverlay) {
            closeWelcomePromo();
        }
    });
}

//Show welcome promo on page load
showWelcomePromo();

//================================================ Welcome Promotion Popup ====================================================

//================================================SLIDER=================================================================
function updateSlider(index) {
    //slider
    slides.forEach((slide, slideIndex) => {
        slide.classList.toggle("active", slideIndex === index);
    });

    //dot
    dots.forEach((dot, dotIndex) => {
        dot.classList.toggle("active", dotIndex === index);
    });
}

function showPrevSlide() {
    currentIndex = (currentIndex - 1 + slides.length) % slides.length;
    updateSlider(currentIndex);
}

function showNextSlide() {
    currentIndex = (currentIndex + 1) % slides.length;
    updateSlider(currentIndex);
}

function goToSlide(index) {
    currentIndex = index;
    updateSlider(currentIndex);
}

if (prevBtn) {
    prevBtn.addEventListener("click", showPrevSlide);
}

if (nextBtn) {
    nextBtn.addEventListener("click", showNextSlide);
}

dots.forEach((dot) => {
    dot.addEventListener("click", (event) => {
        const index = parseInt(event.currentTarget.dataset.index);
        goToSlide(index);
    });
});

//================================================SLIDER=================================================================


reservation();


/**=========================================== hiển thị promo khi click vào khuyến mãi=================================================*/
function openPromoModal(eventImage) {
    if (
        !promoModal ||
        !promoModalImage ||
        !promoModalLabel ||
        !promoModalTitle ||
        !promoModalText
    )
        return;

    const label =
        eventImage.querySelector(".promo-label")?.textContent ||
        "Khuyến mãi";
    const title =
        eventImage.querySelector(".promo-card h3")?.textContent ||
        "Khuyến mãi hấp dẫn";
    const text =
        eventImage.querySelector(".promo-card p:last-of-type")
            ?.textContent || "Xem thông tin ưu đãi ngay";

    promoModalImage.src = eventImage.querySelector("img").src;
    promoModalImage.alt = title;
    promoModalLabel.textContent = label;
    promoModalTitle.textContent = title;
    promoModalText.textContent = text;
    promoModal.classList.remove("hidden");
    document.body.style.overflow = "hidden";
}

function closePromoModal() {
    if (!promoModal) return;
    promoModal.classList.add("hidden");
    document.body.style.overflow = "";
}

promoImages.forEach((image) => {
    image.addEventListener("click", () => {
        // Lấy slide hiện tại đang hiển thị
        const activeSlide = document.querySelector(".slider-item.active");

        openPromoModal(activeSlide);
    });
});

if (promoModalClose) {
    promoModalClose.addEventListener("click", closePromoModal);
}

if (promoModal) {
    promoModal.addEventListener("click", (event) => {
        if (event.target === promoModal) {
            closePromoModal();
        }
    });
}

document.addEventListener("keydown", (event) => {
    if (
        event.key === "Escape" &&
        !promoModal?.classList.contains("hidden")
    ) {
        closePromoModal();
    }
});

/**======================================================================================================================= */

// Chatbot functionality
const chatbotBtn = document.getElementById("chatbotBtn");
const chatbotClose = document.getElementById("chatbotClose");
const chatbotBox = document.getElementById("chatbotBox");
const chatbotInput = document.getElementById("chatbotInput");
const chatbotSend = document.getElementById("chatbotSend");
const chatbotMessages = document.getElementById("chatbotMessages");

if (chatbotBtn) {
    chatbotBtn.addEventListener("click", () => {
        chatbotBox.classList.toggle("hidden");
    });
}

if (chatbotClose) {
    chatbotClose.addEventListener("click", () => {
        chatbotBox.classList.add("hidden");
    });
}

function addMessage(text, isUser) {
    const messageDiv = document.createElement("div");
    messageDiv.className = isUser
        ? "message user-message"
        : "message bot-message";

    const p = document.createElement("p");
    p.textContent = text;

    messageDiv.appendChild(p);
    chatbotMessages.appendChild(messageDiv);
    chatbotMessages.scrollTop = chatbotMessages.scrollHeight;
}

function getAutoResponse(userMessage) {
    const lowerMessage = userMessage.toLowerCase();

    if (
        lowerMessage.includes("xin chào") ||
        lowerMessage.includes("hello") ||
        lowerMessage.includes("hi")
    ) {
        return "Xin chào! Chúng tôi rất vui được hỗ trợ bạn. Bạn có câu hỏi gì không?";
    } else if (
        lowerMessage.includes("giờ mở cửa") ||
        lowerMessage.includes("mấy giờ")
    ) {
        return "Giờ mở cửa của chúng tôi: 10:00 - 14:00 và 16:30 - 22:30. Tất cả các ngày trong tuần.";
    } else if (
        lowerMessage.includes("đặt bàn") ||
        lowerMessage.includes("booking")
    ) {
        return "Bạn có thể đặt bàn trực tiếp tại nhà hàng hoặc gọi hotline 0900 123 456. Chúng tôi sẵn sàng phục vụ!";
    } else if (
        lowerMessage.includes("thực đơn") ||
        lowerMessage.includes("menu")
    ) {
        return "Chúng tôi chuyên các món nướng than hoa ngon lạ. Bạn có thể xem thực đơn đầy đủ trên website hoặc hỏi nhân viên tại nhà hàng.";
    } else if (
        lowerMessage.includes("địa chỉ") ||
        lowerMessage.includes("ở đâu")
    ) {
        return "Chúng tôi có 3 chi nhánh: Hà Nội, Hồ Chí Minh, và Đà Nẵng. Bạn muốn biết chi tiết về chi nhánh nào?";
    } else if (
        lowerMessage.includes("khuyến mãi") ||
        lowerMessage.includes("giảm giá")
    ) {
        return "Hiện tại chúng tôi có các khuyến mãi hấp dẫn như giảm 20-30% cho một số món nướng. Bạn có muốn biết thêm?";
    } else if (
        lowerMessage.includes("cảm ơn") ||
        lowerMessage.includes("thanks")
    ) {
        return "Không có gì! Chúng tôi sẽ phục vụ bạn tốt nhất. 😊";
    } else if (
        lowerMessage.includes("tạm biệt") ||
        lowerMessage.includes("bye")
    ) {
        return "Tạm biệt! Mong sớm được gặp bạn tại Mây Nướng!";
    } else {
        return "Cảm ơn câu hỏi của bạn! Để được hỗ trợ tốt hơn, vui lòng gọi hotline 0900 123 456 hoặc liên hệ trực tiếp. Chúng tôi luôn sẵn sàng!";
    }
}

if (chatbotSend) {
    chatbotSend.addEventListener("click", () => {
        const message = chatbotInput.value.trim();
        if (message) {
            addMessage(message, true);
            chatbotInput.value = "";

            // Simulate bot response delay
            setTimeout(() => {
                const response = getAutoResponse(message);
                addMessage(response, false);
            }, 500);
        }
    });
}

if (chatbotInput) {
    chatbotInput.addEventListener("keypress", (e) => {
        if (e.key === "Enter") {
            chatbotSend.click();
        }
    });
}







