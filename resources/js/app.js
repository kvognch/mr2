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

document.addEventListener("alpine:init", () => {
  // Scroll store for header shadow and bottom padding collapse.
  Alpine.store("scroll", { y: 0, collapsed: false });
  // Review modal: open/close from any scope (e.g. "Оставить отзыв" button in reviews)
  Alpine.store("reviewModalOpen", false);
  Alpine.store("reviewModalOpenCount", 0);
  // Star rating state for review modal (remount via key so rating resets on open)
  Alpine.data("reviewModalForm", () => ({ rating: 0, hoverRating: 0 }));
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
