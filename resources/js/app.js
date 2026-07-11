import './bootstrap';

window.setBodyScrollLock = (locked) => {
  const body = document.body;
  if (!body) return;

  if (locked) {
    const scrollbarWidth = Math.max(0, window.innerWidth - document.documentElement.clientWidth);
    body.style.overflow = "hidden";
    body.style.paddingRight = scrollbarWidth > 0 ? `${scrollbarWidth}px` : "";
    return;
  }

  body.style.overflow = "";
  body.style.paddingRight = "";
};

const escapeHtml = (value) =>
  String(value ?? "")
    .replaceAll("&", "&amp;")
    .replaceAll("<", "&lt;")
    .replaceAll(">", "&gt;")
    .replaceAll('"', "&quot;")
    .replaceAll("'", "&#039;");

const normalizeVideoSrc = (src) => {
  const value = String(src ?? "").trim();
  if (!value) return "";

  try {
    const url = new URL(value, window.location.origin);

    if (url.hostname.includes("youtube.com") && url.searchParams.has("v")) {
      return `https://www.youtube.com/embed/${url.searchParams.get("v")}`;
    }

    if (url.hostname === "youtu.be") {
      return `https://www.youtube.com/embed/${url.pathname.replace("/", "")}`;
    }

    const rutubeMatch = url.href.match(/rutube\.ru\/video\/([^/?#]+)/i);
    if (rutubeMatch?.[1]) {
      return `https://rutube.ru/play/embed/${rutubeMatch[1]}`;
    }

    return url.href;
  } catch {
    return value;
  }
};

const isVideoModalHref = (href) => {
  const value = String(href ?? "").trim();
  if (!value) return false;

  try {
    const url = new URL(value, window.location.origin);

    return (
      url.hostname.includes("youtube.com") ||
      url.hostname === "youtu.be" ||
      /rutube\.ru\/video\/[^/?#]+/i.test(url.href)
    );
  } catch {
    return false;
  }
};

const isRequestModalHref = (href) => {
  const value = String(href ?? "").trim();

  return ["#request-modal", "#request", "#leave-request"].includes(value);
};

const openRequestModal = () => {
  const root = document.body?.matches("[x-data]") ? document.body : document.querySelector("[x-data]");
  const data = root && window.Alpine?.$data ? window.Alpine.$data(root) : null;

  if (data && "requestModalOpen" in data) {
    data.requestModalOpen = true;
    return;
  }

  window.dispatchEvent(new CustomEvent("open-request-modal"));
};

const buildVideoIframe = (src) =>
  `<iframe width="560" height="315" src="${escapeHtml(src)}" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>`;

const normalizeVideoEmbed = (embed) => {
  const value = String(embed ?? "").trim();
  if (!value) return "";

  const template = document.createElement("template");
  template.innerHTML = value;

  const iframe = template.content.querySelector("iframe");
  if (!iframe) return value;

  iframe.setAttribute("width", "560");
  iframe.setAttribute("height", "315");
  iframe.setAttribute("title", iframe.getAttribute("title") || "YouTube video player");
  iframe.setAttribute("frameborder", "0");
  iframe.setAttribute("allow", "accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share");
  iframe.setAttribute("referrerpolicy", "strict-origin-when-cross-origin");
  iframe.setAttribute("allowfullscreen", "");

  return iframe.outerHTML;
};

const ensureInlineVideoModal = () => {
  let modal = document.querySelector("[data-inline-video-modal]");
  if (modal) return modal;

  modal = document.createElement("div");
  modal.setAttribute("data-inline-video-modal", "");
  modal.className = "fixed inset-0 z-50 hidden items-center justify-center bg-black/50 px-4";
  modal.innerHTML = `
    <div class="relative inline-block max-h-[90vh] max-w-[calc(100vw-2rem)] overflow-y-auto space-y-6 bg-white rounded-brand-base pt-12 pb-5 xs:pb-6 md:pb-8 px-5 xs:px-6 md:px-8" data-inline-video-dialog>
      <button type="button" class="absolute top-5 right-5 xs:right-6 md:top-6 md:right-6 p-1 -mr-1 rounded-lg hover:bg-brand-gray-light smooth text-brand-dark" aria-label="Закрыть" data-inline-video-close>
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4.99951 18.998L11.9995 11.998M11.9995 11.998L18.9995 4.99805M11.9995 11.998L4.99951 4.99805M11.9995 11.998L18.9995 18.998" stroke="#193760" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" /></svg>
      </button>
      <div class="flex-center">
        <h4 class="text-center" data-inline-video-title></h4>
      </div>
      <div class="inline-block max-w-full [&_iframe]:aspect-video [&_iframe]:h-auto [&_iframe]:max-w-full" data-inline-video-content></div>
    </div>
  `;

  const close = () => {
    modal.classList.add("hidden");
    modal.classList.remove("flex");
    modal.querySelector("[data-inline-video-content]").innerHTML = "";
    window.setBodyScrollLock(false);
  };

  modal.addEventListener("click", close);
  modal.querySelector("[data-inline-video-close]")?.addEventListener("click", close);
  modal.querySelector("[data-inline-video-dialog]")?.addEventListener("click", (event) => event.stopPropagation());
  document.addEventListener("keydown", (event) => {
    if (event.key === "Escape" && !modal.classList.contains("hidden")) close();
  });

  document.body.appendChild(modal);

  return modal;
};

window.openInlineVideoModal = ({ title = "Видео", src = "", embed = "" } = {}) => {
  const modal = ensureInlineVideoModal();
  const content = modal.querySelector("[data-inline-video-content]");

  modal.querySelector("[data-inline-video-title]").textContent = title || "Видео";

  if (embed) {
    content.innerHTML = normalizeVideoEmbed(embed);
  } else {
    const normalizedSrc = normalizeVideoSrc(src);
    content.innerHTML = normalizedSrc ? buildVideoIframe(normalizedSrc) : "";
  }

  modal.classList.remove("hidden");
  modal.classList.add("flex");
  window.setBodyScrollLock(true);
};

document.addEventListener("click", (event) => {
  const trigger = event.target.closest("[data-video-modal], .info-page-content a[href]");
  if (!trigger) return;

  const href = trigger.getAttribute("href") || "";
  const isExplicitTrigger = trigger.hasAttribute("data-video-modal");

  if (isRequestModalHref(href)) {
    event.preventDefault();
    openRequestModal();
    return;
  }

  if (!isExplicitTrigger && !isVideoModalHref(href)) return;

  event.preventDefault();
  window.openInlineVideoModal({
    title: trigger.dataset.videoTitle || trigger.textContent.trim() || "Видео",
    src: trigger.dataset.videoSrc || href,
    embed: trigger.dataset.videoEmbed || "",
  });
});

document.addEventListener("alpine:init", () => {
  // Scroll store for header shadow and bottom padding collapse.
  Alpine.store("scroll", { y: 0, collapsed: false });
  // Review modal: open/close from any scope (e.g. "Оставить отзыв" button in reviews)
  Alpine.store("reviewModalOpen", false);
  Alpine.store("reviewModalOpenCount", 0);
  Alpine.data("reviewModalForm", (config = {}) => ({
    rating: Number(config.initialRating || 0),
    hoverRating: 0,
    isSubmitting: false,
    successMessage: config.initialSuccessMessage || "",
    errorMessage: config.initialErrorMessage || "",
    closeTimer: null,
    resetTimer: null,

    init() {
      if (this.successMessage) {
        this.open();
        this.scheduleClose();
        return;
      }

      if (config.shouldOpen) {
        this.open();
      }
    },

    async submit(event) {
      if (this.isSubmitting || this.successMessage) return;

      const form = event.target;
      window.clearTimeout(this.resetTimer);
      this.isSubmitting = true;
      this.errorMessage = "";

      try {
        const response = await axios.post(form.action, new FormData(form), {
          headers: { Accept: "application/json" },
        });

        this.successMessage = response.data?.message || "Отзыв направлен на модерацию. Спасибо!";
        form.reset();
        this.rating = 0;
        this.hoverRating = 0;
        this.scheduleClose();
      } catch (error) {
        this.errorMessage = this.extractErrorMessage(error);
      } finally {
        this.isSubmitting = false;
      }
    },

    open() {
      if (config.modal === "contractor") {
        this.contractorReviewModalOpen = true;
        return;
      }

      this.$store.reviewModalOpen = true;
    },

    close() {
      window.clearTimeout(this.closeTimer);

      if (config.modal === "contractor") {
        this.contractorReviewModalOpen = false;
      } else {
        this.$store.reviewModalOpen = false;
      }

      window.clearTimeout(this.resetTimer);
      this.resetTimer = window.setTimeout(() => {
        this.successMessage = "";
        this.errorMessage = "";
        this.isSubmitting = false;
        this.rating = Number(config.initialRating || 0);
        this.hoverRating = 0;
      }, 200);
    },

    scheduleClose() {
      window.clearTimeout(this.closeTimer);
      this.closeTimer = window.setTimeout(() => {
        this.close();
      }, 3000);
    },

    extractErrorMessage(error) {
      const errors = error.response?.data?.errors;

      if (errors && typeof errors === "object") {
        const firstError = Object.values(errors).flat().find(Boolean);

        if (firstError) return firstError;
      }

      return error.response?.data?.message || "Не удалось отправить отзыв. Попробуйте еще раз.";
    },
  }));
  Alpine.data("requestModalForm", (config = {}) => ({
    isSubmitting: false,
    successMessage: config.initialSuccessMessage || "",
    errorMessage: config.initialErrorMessage || "",
    closeTimer: null,
    resetTimer: null,

    init() {
      if (this.successMessage) {
        this.requestModalOpen = true;
        this.scheduleClose();
        return;
      }

      if (config.shouldOpen) {
        this.requestModalOpen = true;
      }
    },

    async submit(event) {
      if (this.isSubmitting || this.successMessage) return;

      const form = event.target;
      window.clearTimeout(this.resetTimer);
      this.isSubmitting = true;
      this.errorMessage = "";

      try {
        const response = await axios.post(form.action, new FormData(form), {
          headers: { Accept: "application/json" },
        });

        this.successMessage = response.data?.message || "Заявка отправлена.";
        form.reset();
        this.resetRecaptcha();
        this.scheduleClose();
      } catch (error) {
        this.errorMessage = this.extractErrorMessage(error);
        this.resetRecaptcha();
      } finally {
        this.isSubmitting = false;
      }
    },

    close() {
      window.clearTimeout(this.closeTimer);
      this.requestModalOpen = false;
      window.clearTimeout(this.resetTimer);
      this.resetTimer = window.setTimeout(() => {
        this.successMessage = "";
        this.errorMessage = "";
        this.isSubmitting = false;
      }, 200);
    },

    scheduleClose() {
      window.clearTimeout(this.closeTimer);
      this.closeTimer = window.setTimeout(() => {
        this.close();
      }, 3000);
    },

    resetRecaptcha() {
      if (window.grecaptcha?.reset) {
        window.grecaptcha.reset();
      }
    },

    extractErrorMessage(error) {
      const errors = error.response?.data?.errors;

      if (errors && typeof errors === "object") {
        const firstError = Object.values(errors).flat().find(Boolean);

        if (firstError) return firstError;
      }

      return error.response?.data?.message || "Не удалось отправить заявку. Попробуйте еще раз.";
    },
  }));
  const setScrollY = () => {
    const y = window.scrollY ?? window.pageYOffset;
    const scroll = Alpine.store("scroll");

    scroll.y = y;

    if (!scroll.collapsed && y > 80) {
      scroll.collapsed = true;
    } else if (scroll.collapsed && y < 4) {
      scroll.collapsed = false;
    }
  };
  setScrollY();
  window.addEventListener("scroll", setScrollY, { passive: true });

  // Home page: 1 / 2 / 3 slides by breakpoint
  Alpine.data("homeReviewsSwiper", (initialReviews = []) => ({
    swiper: null,
    isBeginning: true,
    isEnd: false,
    reviews: initialReviews,
    initSwiper() {
      const el = this.$el.querySelector(".reviews-swiper");
      if (!el) return;
      this.swiper = new Swiper(el, {
        slidesPerView: 1,
        slidesPerGroup: 1,
        slidesPerGroupSkip: 0,
        spaceBetween: 24,
        breakpoints: {
          640: { slidesPerView: 1, slidesPerGroup: 1 },
          768: { slidesPerView: 2, slidesPerGroup: 1 },
          1024: { slidesPerView: 3, slidesPerGroup: 1 },
        },
        loop: false,
        on: {
          init: (s) => {
            this.isBeginning = s.isBeginning;
            this.isEnd = s.isEnd;
          },
          slideChange: (s) => {
            this.isBeginning = s.isBeginning;
            this.isEnd = s.isEnd;
          },
        },
      });
    },
  }));

  // About page: one slide only, separate data (title, desc, authRole, recommended)
  Alpine.data("reviewsSwiper", (initialReviews = []) => ({
    swiper: null,
    isBeginning: true,
    isEnd: false,
    reviews: initialReviews,
    initSwiper() {
      const el = this.$el.querySelector(".reviews-swiper");
      if (!el) return;
      this.swiper = new Swiper(el, {
        slidesPerView: 1,
        slidesPerGroup: 1,
        slidesPerGroupSkip: 0,
        spaceBetween: 24,
        breakpoints: {
          768: { slidesPerView: 2, slidesPerGroup: 1 },
          1024: { slidesPerView: 1, slidesPerGroup: 1 },
        },
        loop: false,
        on: {
          init: (s) => {
            this.isBeginning = s.isBeginning;
            this.isEnd = s.isEnd;
          },
          slideChange: (s) => {
            this.isBeginning = s.isBeginning;
            this.isEnd = s.isEnd;
          },
        },
      });
    },
  }));
});
