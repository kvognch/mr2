/**
 * Alpine.js + Swiper.js: Users Reviews section
 * Requires: Swiper (loaded before this), Alpine.js (loaded after this)
 */
// Home page swiper: title, text, author, authRole
const REVIEWS_DATA = [
  {
    title: "Подключал газ",
    text: "Долго искал организацию которая сможет выполнить весь комплекс услуг по подключению газа. Платформа помогла найти компетентных специалистов. Рекомендовал друзьям - остались довольны подбором нужной организации",
    author: "Александр П.",
    authRole: "Строю дом",
  },
  {
    title: "Ищу новых клиентов",
    text: "Интересная площадка как для профессионалов, так и для тех кто ищет надежную компанию для строительства. После регистрации на платформе работы прибавилось. Также нашли компетентных подрядчиков",
    author: "Евгений И.",
    authRole: "Директор компании",
  },
  {
    title: "Нашел подрядчика",
    text: "С помощью платформы быстро нашел квалифицированного подрядчика для выполнения работ по подключению инженерных сетей. В целом интересная, удобная и полезная платформа, но хотелось бы большей территории охвата",
    author: "Сергей Г.",
    authRole: "Строительная компания",
  },
  {
    title: "Подключение электричества",
    text: "Оформил заявку на подключение электричества через платформу. Менеджер связался в тот же день, все документы подготовили быстро. Очень удобный сервис для частных застройщиков.",
    author: "Дмитрий К.",
    authRole: "Владелец участка",
  },
  {
    title: "Ремонт сетей",
    text: "Искали подрядчика на ремонт инженерных сетей в коттеджном поселке. Платформа позволила сравнить несколько компаний и выбрать оптимальный вариант по срокам и цене.",
    author: "Ольга М.",
    authRole: "Управляющая компания",
  },
  {
    title: "Проектирование и монтаж",
    text: "Заказывали полный цикл: проект и монтаж водоснабжения и канализации. Нашли исполнителя с хорошими отзывами. Работы выполнены в срок, претензий нет.",
    author: "Андрей В.",
    authRole: "Строю дом",
  },
  {
    title: "Консультация по подключениям",
    text: "Получил подробную консультацию по порядку подключения к сетям. Понятно объяснили, какие документы нужны и в какой последовательности обращаться. Рекомендую новичкам.",
    author: "Михаил С.",
    authRole: "Частный заказчик",
  },
];

// About page swiper: title, desc, authRole, stars (1-5), author, date; "Рекомендует" shown when stars > 3
const ABOUT_REVIEWS_DATA = [
  {
    title: "Подключал газ",
    desc: "Долго искал организацию которая сможет выполнить весь комплекс услуг по подключению газа. Платформа помогла найти компетентных специалистов. Рекомендовал друзьям - остались довольны подбором нужной организации",
    author: "Александр П.",
    authRole: "Строю дом",
    date: "01.08.2025",
    stars: 5,
  },
  {
    title: "Ищу новых клиентов",
    desc: "Интересная площадка как для профессионалов, так и для тех кто ищет надежную компанию для строительства. После регистрации на платформе работы прибавилось. Также нашли компетентных подрядчиков",
    author: "Евгений И.",
    authRole: "Директор компании",
    date: "28.07.2025",
    stars: 5,
  },
  {
    title: "Нашел подрядчика",
    desc: "С помощью платформы быстро нашел квалифицированного подрядчика для выполнения работ по подключению инженерных сетей. В целом интересная, удобная и полезная платформа, но хотелось бы большей территории охвата",
    author: "Сергей Г.",
    authRole: "Строительная компания",
    date: "15.07.2025",
    stars: 3,
  },
];

document.addEventListener("alpine:init", () => {
  // Scroll store for header shadow (scrollY > 64 or mobile menu open)
  Alpine.store("scroll", { y: 0 });
  // Review modal: open/close from any scope (e.g. "Оставить отзыв" button in reviews)
  Alpine.store("reviewModalOpen", false);
  Alpine.store("reviewModalOpenCount", 0);
  // Star rating state for review modal (remount via key so rating resets on open)
  Alpine.data("reviewModalForm", () => ({ rating: 0, hoverRating: 0 }));
  const setScrollY = () =>
    (Alpine.store("scroll").y = window.scrollY ?? window.pageYOffset);
  setScrollY();
  window.addEventListener("scroll", setScrollY, { passive: true });

  // Home page: 1 / 2 / 3 slides by breakpoint
  Alpine.data("homeReviewsSwiper", () => ({
    swiper: null,
    isBeginning: true,
    isEnd: false,
    reviews: REVIEWS_DATA,
    initSwiper() {
      const el = this.$el.querySelector(".reviews-swiper");
      if (!el) return;
      this.swiper = new Swiper(el, {
        slidesPerView: 1,
        slidesPerGroup: 1,
        spaceBetween: 24,
        navigation: {
          nextEl: this.$el.querySelector(".reviews-swiper-next"),
          prevEl: this.$el.querySelector(".reviews-swiper-prev"),
        },
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
  Alpine.data("reviewsSwiper", () => ({
    swiper: null,
    isBeginning: true,
    isEnd: false,
    reviews: ABOUT_REVIEWS_DATA,
    initSwiper() {
      const el = this.$el.querySelector(".reviews-swiper");
      if (!el) return;
      this.swiper = new Swiper(el, {
        slidesPerView: 1,
        slidesPerGroup: 1,
        spaceBetween: 24,
        breakpoints: {
          768: { slidesPerView: 2, slidesPerGroup: 1 },
          1024: { slidesPerView: 1, slidesPerGroup: 1 },
        },
        navigation: {
          nextEl: this.$el.querySelector(".reviews-swiper-next"),
          prevEl: this.$el.querySelector(".reviews-swiper-prev"),
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
