    <main class="bg-brand-gray-light-2 pb-20 lg:pb-30">
      <!-- About Us -->
      <section
        id="about-us"
        class="pt-8 lg:pt-10 pb-8 lg:pb-15"
        x-data="{
          cardTooltip: { show: false, text: '', x: 0, y: 0, above: true, wide: false },
          showCardTooltip(ev, text, above, wide) {
            const r = ev.currentTarget.getBoundingClientRect();
            const gap = 6;
            this.cardTooltip = { show: true, text, wide: !!wide, above: !!above, x: r.left + r.width / 2, y: above ? r.top - gap : r.bottom + gap };
            this.$nextTick(() => {
              requestAnimationFrame(() => {
                const el = this.$refs.cardTooltipBox;
                if (!el) return;
                const box = el.getBoundingClientRect();
                const pad = 16;
                const W = window.innerWidth;
                const H = window.innerHeight;
                let x = this.cardTooltip.x;
                let y = this.cardTooltip.y;
                if (box.left < pad) x += pad - box.left;
                if (box.right > W - pad) x -= box.right - (W - pad);
                if (box.top < pad) y += pad - box.top;
                if (box.bottom > H - pad) y -= box.bottom - (H - pad);
                this.cardTooltip.x = x;
                this.cardTooltip.y = y;
              });
            });
          },
          hideCardTooltip() { this.cardTooltip.show = false; }
        }"
      >
        <div class="container-base">
          <div class="space-y-8 lg:space-y-10">
            <div
              class="grid grid-cols-2 sm:flex-base gap-3 xs:gap-5 md:gap-7 lg:gap-10"
            >
              <a
                href="{{ route('search.index') }}"
                class="text-xs 2xs:text-base lg:text-lg button_4 flex-center gap-2.5"
              >
                <svg
                  width="24"
                  height="24"
                  viewBox="0 0 24 24"
                  fill="none"
                  xmlns="http://www.w3.org/2000/svg"
                >
                  <path
                    d="M19 12H5"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                  />
                  <path
                    d="M12 19L5 12L12 5"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                  />
                </svg>

                <span>К Поиску</span>
              </a>
              <button
                type="button"
                class="text-xs 2xs:text-base lg:text-lg button_4 flex-center gap-2.5"
                @click="requestModalOpen = true"
              >
                Помощь в подборе
              </button>
            </div>
            <div
              class="flex-between flex-col sm:flex-row items-start sm:items-center gap-8"
            >
              <h1>{{ $contractor->short_name }}</h1>

              <div class="flex-base gap-5 sm:gap-10">
                @foreach ($segmentBadges as $segmentBadge)
                  <span
                    class="cursor-default text_1 bg-white px-4.25 py-3.75 rounded-brand-base"
                    @mouseenter="showCardTooltip($event, '{{ $segmentBadge['tooltip'] }}', false, false)"
                    @mouseleave="hideCardTooltip()"
                  >
                    {{ $segmentBadge['code'] }}
                  </span>
                @endforeach
              </div>
            </div>
          </div>

          <!-- Table -->
          <div class="space-y-5 lg:space-y-10 mt-8 lg:mt-15">
            <h4>Выполняемые работы по видам ресурсов</h4>

            <div
              class="bg-white rounded-brand-base xl:rounded-brand-3xl overflow-x-auto"
            >
              <table class="min-w-5xl table table-fixed w-full border-collapse">
                <thead>
                  <tr>
                    <th
                      class="border border-brand-gray-light-2 py-1.5 xs:py-3 xl:py-5"
                    ></th>
                    @foreach ($resourceColumns as $resourceColumn)
                      <th
                        class="border border-brand-gray-light-2 py-1.5 xs:py-3 xl:py-5"
                      >
                        <span
                          class="flex w-full items-center justify-center cursor-default"
                          @mouseenter="showCardTooltip($event, '{{ $resourceColumn['label'] }}', true, false)"
                          @mouseleave="hideCardTooltip()"
                        >
                          <img
                            src="{{ $resourceColumn['icon'] }}"
                            alt="{{ $resourceColumn['label'] }}"
                            class="size-5 xs:size-7 xl:size-10 shrink-0 mx-auto"
                          />
                        </span>
                      </th>
                    @endforeach
                    <th
                      class="border border-brand-gray-light-2 py-1.5 xs:py-3 xl:py-5"
                    >
                      <span
                        class="inline-block cursor-default xs:text-base xl:text-lg 3xl:text-2xl text_1 text-brand-gray-dark"
                        @mouseenter="showCardTooltip($event, 'Наличие свидетельства о допуске к работам СРО', true, false)"
                        @mouseleave="hideCardTooltip()"
                      >
                        Наличие СРО
                      </span>
                    </th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <th
                      class="border border-brand-gray-light-2 py-1.5 xs:py-3 xl:py-5"
                    >
                      <span
                        class="inline-block cursor-default xs:text-base xl:text-lg 3xl:text-2xl text_1 text-brand-gray-dark"
                        @mouseenter="showCardTooltip($event, 'Строительно-монтажные работы', false, false)"
                        @mouseleave="hideCardTooltip()"
                      >
                        СМР
                      </span>
                    </th>
                    @foreach ($resourceColumns as $resourceColumn)
                      @php
                        $hasWork = in_array($resourceColumn['abbreviation'], $smrResourceAbbreviations, true);
                      @endphp
                      <td
                        class="border border-brand-gray-light-2 py-1.5 xs:py-3 xl:py-5"
                      >
                        <span
                          class="flex w-full items-center justify-center cursor-default"
                          @mouseenter="showCardTooltip($event, '{{ $hasWork ? 'Выполняется' : 'Не выполняется' }}', true, false)"
                          @mouseleave="hideCardTooltip()"
                        >
                          <img
                            src="{{ asset($hasWork ? 'assets/svgs/tick.svg' : 'assets/svgs/minus.svg') }}"
                            alt="{{ $hasWork ? 'Выполняется' : 'Не выполняется' }}"
                            class="size-5 xs:size-7 xl:size-10 shrink-0 mx-auto"
                          />
                        </span>
                      </td>
                    @endforeach
                    @php
                      $smrHasSro = (bool) $contractor->smr_has_sro;
                    @endphp
                    <td
                      class="border border-brand-gray-light-2 py-1.5 xs:py-3 xl:py-5"
                    >
                      <span
                        class="flex w-full items-center justify-center cursor-default"
                        @mouseenter="showCardTooltip($event, '{{ $smrHasSro ? 'Есть СРО' : 'Нет СРО' }}', true, false)"
                        @mouseleave="hideCardTooltip()"
                      >
                        <img
                          src="{{ asset($smrHasSro ? 'assets/svgs/tick.svg' : 'assets/svgs/minus.svg') }}"
                          alt="{{ $smrHasSro ? 'Есть СРО' : 'Нет СРО' }}"
                          class="size-5 xs:size-7 xl:size-10 shrink-0 mx-auto"
                        />
                      </span>
                    </td>
                  </tr>
                  <tr>
                    <th
                      class="border border-brand-gray-light-2 py-1.5 xs:py-3 xl:py-5"
                    >
                      <span
                        class="inline-block cursor-default xs:text-base xl:text-lg 3xl:text-2xl text_1 text-brand-gray-dark"
                        @mouseenter="showCardTooltip($event, 'Проектно-изыскательские работы', false, true)"
                        @mouseleave="hideCardTooltip()"
                      >
                        ПИР / ПСД
                      </span>
                    </th>
                    @foreach ($resourceColumns as $resourceColumn)
                      @php
                        $hasWork = in_array($resourceColumn['abbreviation'], $pirResourceAbbreviations, true);
                      @endphp
                      <td
                        class="border border-brand-gray-light-2 py-1.5 xs:py-3 xl:py-5"
                      >
                        <span
                          class="flex w-full items-center justify-center cursor-default"
                          @mouseenter="showCardTooltip($event, '{{ $hasWork ? 'Выполняется' : 'Не выполняется' }}', true, false)"
                          @mouseleave="hideCardTooltip()"
                        >
                          <img
                            src="{{ asset($hasWork ? 'assets/svgs/tick.svg' : 'assets/svgs/minus.svg') }}"
                            alt="{{ $hasWork ? 'Выполняется' : 'Не выполняется' }}"
                            class="size-5 xs:size-7 xl:size-10 shrink-0 mx-auto"
                          />
                        </span>
                      </td>
                    @endforeach
                    @php
                      $pirHasSro = (bool) $contractor->pir_has_sro;
                    @endphp
                    <td
                      class="border border-brand-gray-light-2 py-1.5 xs:py-3 xl:py-5"
                    >
                      <span
                        class="flex w-full items-center justify-center cursor-default"
                        @mouseenter="showCardTooltip($event, '{{ $pirHasSro ? 'Есть СРО' : 'Нет СРО' }}', true, false)"
                        @mouseleave="hideCardTooltip()"
                      >
                        <img
                          src="{{ asset($pirHasSro ? 'assets/svgs/tick.svg' : 'assets/svgs/minus.svg') }}"
                          alt="{{ $pirHasSro ? 'Есть СРО' : 'Нет СРО' }}"
                          class="size-5 xs:size-7 xl:size-10 shrink-0 mx-auto"
                        />
                      </span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Fixed tooltip (no overflow/scroll issues) -->
        <div
          x-ref="cardTooltipBox"
          x-show="cardTooltip.show"
          x-cloak
          x-transition:enter="transition ease-out duration-150"
          x-transition:enter-start="opacity-0"
          x-transition:enter-end="opacity-100"
          x-transition:leave="transition ease-in duration-100"
          class="fixed z-[100] pointer-events-none px-3 py-2 bg-white border border-brand-gray rounded-lg text_1 text-brand-gray-dark shadow-md"
          :class="cardTooltip.wide ? 'min-w-48 max-w-80 whitespace-normal text-center' : 'whitespace-nowrap'"
          :style="`left: ${cardTooltip.x}px; top: ${cardTooltip.y}px; transform: translate(-50%, ${cardTooltip.above ? '-100%' : '0'});`"
        >
          <span x-text="cardTooltip.text"></span>
        </div>
      </section>

      <!-- About Info Grids -->
      <section id="about-info-grids" class="pt-8 lg:pt-10 overflow-x-hidden">
        <div
          class="container-base grid lg:grid-cols-2 gap-10 lg:gap-15 2xl:gap-x-50 min-w-0"
        >
          <!-- About -->
          <div class="space-y-3 xs:space-y-6 lg:space-y-10 min-w-0">
            <h4>О компании</h4>
            <div
              class="space-y-5 bg-white rounded-2xl sm:rounded-brand-3xl p-4 xs:p-6 xl:p-10 min-w-0"
            >
              <h4>{{ $contractor->full_name ?: $contractor->short_name }}</h4>
              <div class="space-y-4 lg:space-y-5">
                <div class="grid 2xs:grid-cols-2 xs:gap-5 md:gap-10">
                  <p class="text-xs 2xs:text-sm sm:text_1 text-brand-gray-dark">
                    Сайт
                  </p>
                  <a
                    href="{{ $website }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="text-xs 2xs:text-sm sm:text_2 text-brand-blue hover:underline underline-offset-1"
                    >{{ $websiteText }}</a
                  >
                </div>
                <div class="grid 2xs:grid-cols-2 xs:gap-5 md:gap-10">
                  <p class="text-xs 2xs:text-sm sm:text_1 text-brand-gray-dark">
                    Социальные сети
                  </p>
                  <div class="flex-base gap-5 lg:*:gap-10">
                    <a href="{{ $socialTelegram }}" target="_blank" rel="noopener noreferrer" class="shrink-0 hover:scale-125 smooth">
                      <img
                        src="{{ asset('assets/svgs/telegram.svg') }}"
                        alt="Telegram"
                        class="size-5 lg:size-8"
                      />
                    </a>
                    <a href="{{ $socialVk }}" target="_blank" rel="noopener noreferrer" class="shrink-0 hover:scale-125 smooth">
                      <img
                        src="{{ asset('assets/svgs/wk.svg') }}"
                        alt="WK"
                        class="size-5 lg:size-8"
                      />
                    </a>
                    <a href="{{ $socialWhatsapp }}" target="_blank" rel="noopener noreferrer" class="shrink-0 hover:scale-125 smooth">
                      <img
                        src="{{ asset('assets/svgs/whatsapp.svg') }}"
                        alt="Whatsapp"
                        class="size-5 lg:size-8"
                      />
                    </a>
                  </div>
                </div>
                <div class="grid 2xs:grid-cols-2 xs:gap-5 md:gap-10">
                  <p class="text-sm xs:text_1 text-brand-gray-dark">Телефон</p>
                  <a
                    href="tel:{{ $phoneHref }}"
                    class="text-xs 2xs:text-sm sm:text_2 text-brand-dark hover:underline underline-offset-1"
                    >{{ $phone }}</a
                  >
                </div>
                <div class="grid 2xs:grid-cols-2 xs:gap-5 md:gap-10">
                  <p class="text-sm xs:text_1 text-brand-gray-dark">
                    Электронная почта
                  </p>
                  <a
                    href="mailto:{{ $email }}"
                    class="text-xs 2xs:text-sm sm:text_2 text-brand-blue hover:underline underline-offset-1"
                    >{{ $email }}
                  </a>
                </div>
                <div class="grid 2xs:grid-cols-2 xs:gap-5 md:gap-10">
                  <p class="text-sm xs:text_1 text-brand-gray-dark">
                    Категория
                  </p>
                  <p class="text-xs 2xs:text-sm sm:text_2 text-brand-gray-blue">
                    {{ $categoriesText }}
                  </p>
                </div>
                <div class="grid 2xs:grid-cols-2 xs:gap-5 md:gap-10">
                  <p class="text-sm xs:text_1 text-brand-gray-dark">
                    Рейтинг надежности
                  </p>
                  <p class="text-xs 2xs:text-sm sm:text_2 text-brand-blue">
                    <span class="text-brand-dark">{{ $ratingText }}</span>
                    <button
                      type="button"
                      class="text-brand-blue hover:underline underline-offset-1"
                      @click="ratingInfoModalOpen = true"
                    >
                      (подробнее)
                    </button>
                  </p>
                </div>
                <div class="grid 2xs:grid-cols-2 xs:gap-5 md:gap-10">
                  <p class="text-sm xs:text_1 text-brand-gray-dark">
                    Срок ответа
                  </p>
                  <p class="text-xs 2xs:text-sm sm:text_2 text-brand-dark">
                    {{ $responseTimeText }}
                  </p>
                </div>
                <div class="grid 2xs:grid-cols-2 xs:gap-5 md:gap-10">
                  <p class="text-sm xs:text_1 text-brand-gray-dark">
                    Объем выполняемых работ, ₽
                  </p>
                  <p class="text-xs 2xs:text-sm sm:text_2 text-brand-dark">
                    {{ $workVolumeText }}
                  </p>
                </div>
                <div class="grid 2xs:grid-cols-2 xs:gap-5 md:gap-10">
                  <p class="text-sm xs:text_1 text-brand-gray-dark">
                    Территория работы
                    <button
                      type="button"
                      class="text-xs 2xs:text-sm sm:text_2 text-brand-blue font-normal text-left"
                      @click="contractorTerritoryMapModalOpen = true; $nextTick(() => window.dispatchEvent(new CustomEvent('contractor-territories-map-modal-opened')))"
                    >
                      (показать на карте)
                    </button>
                  </p>
                  <div
                    class="space-y-1"
                    x-data="{ expanded: false, canExpand: false }"
                    x-init="$nextTick(() => { const el = $refs.territoriesText; canExpand = el ? (el.scrollHeight > el.clientHeight + 1) : false; })"
                  >
                    <p
                      x-ref="territoriesText"
                      class="text-xs 2xs:text-sm sm:text_2 text-brand-dark transition-[line-clamp] duration-200"
                      :class="expanded ? 'line-clamp-none' : 'line-clamp-2'"
                    >
                      {{ $territoriesText }}
                    </p>
                    <button
                      type="button"
                      class="flex-base gap-2.5 text_2 text-brand-blue group"
                      @click="expanded = !expanded"
                      x-show="canExpand"
                      x-cloak
                    >
                      <span x-text="expanded ? 'свернуть' : 'подробнее'"></span>
                      <svg
                        width="24"
                        height="24"
                        viewBox="0 0 24 24"
                        fill="none"
                        xmlns="http://www.w3.org/2000/svg"
                        class="size-4 md:size-6 group-hover:translate-x-2 transition-all duration-150"
                        :class="expanded && 'rotate-90'"
                      >
                        <path
                          d="M5 12H19"
                          stroke="currentColor"
                          stroke-width="2"
                          stroke-linecap="round"
                          stroke-linejoin="round"
                        />
                        <path
                          d="M12 5L19 12L12 19"
                          stroke="currentColor"
                          stroke-width="2"
                          stroke-linecap="round"
                          stroke-linejoin="round"
                        />
                      </svg>
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Details -->
          <div class="space-y-3 xs:space-y-6 lg:space-y-10 min-w-0">
            <h4>Реквизиты</h4>
            <div
              class="bg-white rounded-2xl sm:rounded-brand-3xl p-4 sm:p-6 xl:p-10 min-w-0"
            >
              <div class="space-y-4 lg:space-y-5">
                <div class="grid xs:grid-cols-2 xs:gap-5 md:gap-10">
                  <p class="text-sm xs:text_1 text-brand-gray-dark">ОГРН</p>
                  <p class="text-xs 2xs:text-sm sm:text_2 text-brand-gray-blue">
                    {{ $ogrnText }}
                  </p>
                </div>
                <div class="grid xs:grid-cols-2 xs:gap-5 md:gap-10">
                  <p class="text-sm xs:text_1 text-brand-gray-dark">ИНН</p>
                  <p class="text-xs 2xs:text-sm sm:text_2 text-brand-gray-blue">
                    {{ $innText }}
                  </p>
                </div>
                <div class="grid xs:grid-cols-2 xs:gap-5 md:gap-10">
                  <p class="text-sm xs:text_1 text-brand-gray-dark">КПП</p>
                  <p class="text-xs 2xs:text-sm sm:text_2 text-brand-gray-blue">
                    {{ $kppText }}
                  </p>
                </div>
                <div class="grid xs:grid-cols-2 xs:gap-5 md:gap-10">
                  <p class="text-sm xs:text_1 text-brand-gray-dark">
                    Дата регистрации
                  </p>
                  <p class="text-xs 2xs:text-sm sm:text_2 text-brand-gray-blue">
                    {{ $registrationDateText }}
                  </p>
                </div>
                <div class="flex-center xs:pt-5 sm:pb-7.5">
                  <button
                    class="text-xs 2xs:text-sm sm:text_1 button_1 mx-auto"
                  >
                    Получить подробную выписку
                  </button>
                </div>
                <div class="grid xs:grid-cols-2 xs:gap-5 md:gap-10">
                  <p class="text-sm xs:text_1 text-brand-gray-dark">
                    Юридический адрес
                  </p>
                  <p class="text-xs 2xs:text-sm sm:text_2 text-brand-gray-blue">
                    {{ $legalAddressText }}
                  </p>
                </div>

                <div class="grid xs:grid-cols-2 xs:gap-5 md:gap-10">
                  <p class="text-sm xs:text_1 text-brand-gray-dark">
                    Адреса и телефоны филиалов
                  </p>
                  <div
                    class="space-y-1"
                    x-data="{ expanded: false, canExpand: false }"
                    x-init="$nextTick(() => { const el = $refs.branchContactsText; canExpand = el ? (el.scrollHeight > el.clientHeight + 1) : false; })"
                  >
                    <p
                      x-ref="branchContactsText"
                      class="text-xs 2xs:text-sm sm:text_2 text-brand-dark transition-[line-clamp] duration-200"
                      :class="expanded ? 'line-clamp-none' : 'line-clamp-6'"
                    >
                      {{ $branchContactsText }}
                    </p>
                    <button
                      type="button"
                      class="flex-base gap-2.5 text-xs 2xs:text-sm sm:text_2 text-brand-blue group"
                      @click="expanded = !expanded"
                      x-show="canExpand"
                      x-cloak
                    >
                      <span x-text="expanded ? 'свернуть' : 'подробнее'"></span>
                      <svg
                        width="24"
                        height="24"
                        viewBox="0 0 24 24"
                        fill="none"
                        xmlns="http://www.w3.org/2000/svg"
                        class="size-4 md:size-6 group-hover:translate-x-2 transition-all duration-150"
                        :class="expanded && 'rotate-90'"
                      >
                        <path
                          d="M5 12H19"
                          stroke="currentColor"
                          stroke-width="2"
                          stroke-linecap="round"
                          stroke-linejoin="round"
                        />
                        <path
                          d="M12 5L19 12L12 19"
                          stroke="currentColor"
                          stroke-width="2"
                          stroke-linecap="round"
                          stroke-linejoin="round"
                        />
                      </svg>
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Reviews-->
          <div
            class="space-y-3 xs:space-y-6 lg:space-y-10 min-w-0"
            x-data="reviewsSwiper(@js($contractorReviews ?? []))"
            x-init="$nextTick(() => { if (reviews.length) initSwiper() })"
          >
            <h4>Отзывы</h4>
            <!-- Reviews Swiper -->
            <div
              x-show="reviews.length"
              x-cloak
              class="swiper reviews-swiper overflow-hidden rounded-3xl sm:rounded-3xl lg:rounded-brand-3xl min-w-0"
            >
              <div class="swiper-wrapper">
                <template x-for="review in reviews" :key="review.title">
                  <div
                    class="swiper-slide h-auto! bg-white rounded-lg sm:rounded-3xl lg:rounded-brand-3xl p-4 sm:p-7 lg:p-10"
                  >
                    <div
                      class="h-full flex flex-col justify-between space-y-6 lg:space-y-10"
                    >
                      <div class="space-y-5">
                        <div class="flex-between">
                          <p
                            class="text_1 text-brand-dark"
                            x-text="review.title"
                          ></p>
                          <p
                            class="text_6 text-brand-gray"
                            x-text="review.date"
                          ></p>
                        </div>

                        <p
                          class="text_2 text-brand-gray-dark"
                          x-text="review.desc"
                        ></p>
                      </div>
                      <div class="flex-between items-end">
                        <div class="text_2">
                          <p class="text-brand-blue" x-text="review.author"></p>
                          <p
                            class="text-brand-gray"
                            x-text="review.authRole"
                          ></p>
                        </div>
                        <div class="sm:space-y-2">
                          <div class="flex-base gap-1 sm:gap-2.5">
                            <template x-for="i in 5" :key="i">
                              <svg
                                width="32"
                                height="32"
                                viewBox="0 0 32 32"
                                fill="none"
                                xmlns="http://www.w3.org/2000/svg"
                                aria-hidden="true"
                                class="size-4 sm:size-6"
                              >
                                <path
                                  d="M15.999 2.6665L20.119 11.0132L29.3323 12.3598L22.6656 18.8532L24.239 28.0265L15.999 23.6932L7.75896 28.0265L9.33229 18.8532L2.66562 12.3598L11.879 11.0132L15.999 2.6665Z"
                                  :fill="i <= review.stars ? '#1450A3' : 'none'"
                                  :stroke="i <= review.stars ? '#1450A3' : 'currentColor'"
                                  stroke-linecap="round"
                                  stroke-linejoin="round"
                                />
                              </svg>
                            </template>
                          </div>
                          <p
                            class="text_2 text-brand-green shrink-0"
                            x-show="review.isRecommended"
                            x-cloak
                          >
                            Рекомендует
                          </p>
                        </div>
                      </div>
                    </div>
                  </div>
                </template>
              </div>
            </div>

            <div
              x-show="!reviews.length"
              x-cloak
              class="bg-white rounded-lg sm:rounded-3xl lg:rounded-brand-3xl p-4 sm:p-7 lg:p-10"
            >
              <p class="text_2 text-brand-gray-dark">
                Пока нет отзывов о данной организации
              </p>
            </div>

            <!-- Open Leave Review Modal & Swiper Navigation Buttons -->
            <div class="flex-between">
              @auth
                <button type="button" class="text-xs 2xs:text-sm sm:text_7 button_5" @click="contractorReviewModalOpen = true">
                  Оставить отзыв
                </button>
              @else
                <button type="button" class="text-xs 2xs:text-sm sm:text_7 button_5" @click="authModalOpen = true; authModalMode = 'login'">
                  Оставить отзыв
                </button>
              @endauth

              <div class="flex-base gap-3 lg:gap-6.5" x-show="reviews.length" x-cloak>
                <button
                  type="button"
                  class="reviews-swiper-prev hover:text-brand-blue disabled:text-brand-gray hover:bg-brand-blue/10 rounded smooth p-1 disabled:opacity-50 disabled:cursor-not-allowed"
                  :disabled="!swiper || isBeginning"
                  @click="swiper && swiper.slideTo(Math.max(swiper.activeIndex - 1, 0))"
                  aria-label="Предыдущий отзыв"
                >
                  <svg
                    width="32"
                    height="32"
                    viewBox="0 0 32 32"
                    fill="none"
                    xmlns="http://www.w3.org/2000/svg"
                  >
                    <path
                      d="M25.3327 16H6.66602"
                      stroke="currentColor"
                      stroke-width="2"
                      stroke-linecap="round"
                      stroke-linejoin="round"
                    />
                    <path
                      d="M15.9993 25.3307L6.66602 15.9974L15.9993 6.66406"
                      stroke="currentColor"
                      stroke-width="2"
                      stroke-linecap="round"
                      stroke-linejoin="round"
                    />
                  </svg>
                </button>

                <button
                  type="button"
                  class="reviews-swiper-next hover:text-brand-blue disabled:text-brand-gray hover:bg-brand-blue/10 rounded smooth p-1 disabled:opacity-50 disabled:cursor-not-allowed"
                  :disabled="!swiper || isEnd"
                  @click="swiper && swiper.slideTo(swiper.activeIndex + 1)"
                  aria-label="Следующий отзыв"
                >
                  <svg
                    width="32"
                    height="32"
                    viewBox="0 0 32 32"
                    fill="none"
                    xmlns="http://www.w3.org/2000/svg"
                    class="rotate-180"
                  >
                    <path
                      d="M25.3327 16H6.66602"
                      stroke="currentColor"
                      stroke-width="2"
                      stroke-linecap="round"
                      stroke-linejoin="round"
                    />
                    <path
                      d="M15.9993 25.3307L6.66602 15.9974L15.9993 6.66406"
                      stroke="currentColor"
                      stroke-width="2"
                      stroke-linecap="round"
                      stroke-linejoin="round"
                    />
                  </svg>
                </button>
              </div>
            </div>
          </div>

          <!-- Notes -->
          <div class="space-y-6 lg:space-y-10">
            <h4>Примечания</h4>
            <div
              class="bg-white text-brand-dark rounded-2xl sm:rounded-brand-3xl p-4 sm:p-6 xl:p-10"
              x-data="{
                expanded: false,
                canExpand: false,
                recalcCanExpand() {
                  const el = this.$refs.additionalInfoText;
                  if (!el) {
                    this.canExpand = false;
                    return;
                  }

                  const clampClass = 'line-clamp-5';
                  el.classList.add(clampClass);
                  const clampedHeight = el.clientHeight;
                  el.classList.remove(clampClass);
                  const fullHeight = el.scrollHeight;
                  el.classList.add(clampClass);

                  this.canExpand = fullHeight > clampedHeight + 1;
                }
              }"
              x-init="$nextTick(() => { recalcCanExpand(); window.addEventListener('resize', () => recalcCanExpand(), { passive: true }); })"
            >
              <p class="text_1 mb-5">Дополнительная информация о компании</p>

              <p x-ref="additionalInfoText" class="text_2" :class="{ 'line-clamp-5': !expanded }">
                {{ $additionalInfoText }}
              </p>

              <button
                type="button"
                class="flex-base gap-2.5 text_2 group text-brand-blue mt-1"
                @click="expanded = !expanded"
                x-show="canExpand"
                x-cloak
              >
                <span x-text="expanded ? 'свернуть' : 'читать далее'"></span>
                <svg
                  width="24"
                  height="24"
                  viewBox="0 0 24 24"
                  fill="none"
                  xmlns="http://www.w3.org/2000/svg"
                  class="group-hover:translate-x-2 transition-all duration-150"
                  :class="{ 'rotate-180': expanded }"
                >
                  <path
                    d="M5 12H19"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                  />
                  <path
                    d="M12 5L19 12L12 19"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                  />
                </svg>
              </button>
            </div>
          </div>
        </div>
      </section>

      <div
        x-show="contractorTerritoryMapModalOpen"
        x-cloak
        class="fixed inset-0 z-50 bg-black/50 flex-center px-4"
        role="dialog"
        aria-modal="true"
        aria-labelledby="contractor-territory-map-modal-title"
        @keydown.escape.window="contractorTerritoryMapModalOpen = false"
        @click="contractorTerritoryMapModalOpen = false"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
      >
        <div
          class="relative w-full bg-white rounded-brand-base pt-12 pb-5 xs:pb-6 md:pb-8 px-5 xs:px-6 md:px-8"
          style="max-width: 1200px;"
          @click.stop
          x-show="contractorTerritoryMapModalOpen"
          x-transition:enter="transition ease-out duration-200"
          x-transition:enter-start="opacity-0 scale-95"
          x-transition:enter-end="opacity-100 scale-100"
          x-transition:leave="transition ease-in duration-150"
          x-transition:leave-start="opacity-100 scale-100"
          x-transition:leave-end="opacity-0 scale-95"
        >
          <button
            type="button"
            class="absolute top-5 right-5 xs:right-6 md:top-6 md:right-6 p-1 -mr-1 rounded-lg hover:bg-brand-gray-light smooth text-brand-dark"
            aria-label="Закрыть"
            @click="contractorTerritoryMapModalOpen = false"
          >
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4.99951 18.998L11.9995 11.998M11.9995 11.998L18.9995 4.99805M11.9995 11.998L4.99951 4.99805M11.9995 11.998L18.9995 18.998" stroke="#193760" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" /></svg>
          </button>

          <div class="flex-center mb-4">
            <h4 id="contractor-territory-map-modal-title" class="text-center">Территория работы на карте</h4>
          </div>

          <p id="contractor-territories-map-empty" class="hidden text_2 text-brand-gray-dark text-center mb-4"></p>
          <div id="contractor-territories-map" class="w-full rounded-brand-base overflow-hidden h-[calc(100vh-12rem)] sm:h-[calc(100vh-13rem)] lg:h-[calc(100vh-14rem)] max-h-[720px]"></div>
        </div>
      </div>

      <div
        x-show="ratingInfoModalOpen"
        x-cloak
        class="fixed inset-0 z-50 bg-black/50 flex-center px-4"
        role="dialog"
        aria-modal="true"
        aria-labelledby="rating-info-modal-title"
        @keydown.escape.window="ratingInfoModalOpen = false"
        @click="ratingInfoModalOpen = false"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
      >
        <div
          class="relative max-h-[90vh] overflow-y-auto w-full bg-white rounded-brand-base pt-12 pb-5 xs:pb-6 md:pb-8 px-5 xs:px-6 md:px-8"
          style="max-width: 640px;"
          @click.stop
          x-show="ratingInfoModalOpen"
          x-transition:enter="transition ease-out duration-200"
          x-transition:enter-start="opacity-0 scale-95"
          x-transition:enter-end="opacity-100 scale-100"
          x-transition:leave="transition ease-in duration-150"
          x-transition:leave-start="opacity-100 scale-100"
          x-transition:leave-end="opacity-0 scale-95"
        >
          <button
            type="button"
            class="absolute top-5 right-5 xs:right-6 md:top-6 md:right-6 p-1 -mr-1 rounded-lg hover:bg-brand-gray-light smooth text-brand-dark"
            aria-label="Закрыть"
            @click="ratingInfoModalOpen = false"
          >
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4.99951 18.998L11.9995 11.998M11.9995 11.998L18.9995 4.99805M11.9995 11.998L4.99951 4.99805M11.9995 11.998L18.9995 18.998" stroke="#193760" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" /></svg>
          </button>

          <div class="flex-center mb-4">
            <h4 id="rating-info-modal-title" class="text-center">Шкала рейтинга надежности</h4>
          </div>

          <div class="overflow-x-auto border border-brand-gray-light-2" style="border-radius: 18px;">
            <table class="w-full text-center text-sm sm:text-base">
              <tbody>
                <tr>
                  <td class="border border-brand-gray-light-2 px-3 py-2 font-semibold" style="background-color: #d6f0db; color: #193760;">ААА</td>
                  <td rowspan="3" class="border border-brand-gray-light-2 px-3 py-2 align-middle font-medium" style="background-color: #d6f0db; color: #193760;">Наивысший рейтинг надёжности</td>
                </tr>
                <tr>
                  <td class="border border-brand-gray-light-2 px-3 py-2 font-semibold" style="background-color: #d6f0db; color: #193760;">АА</td>
                </tr>
                <tr>
                  <td class="border border-brand-gray-light-2 px-3 py-2 font-semibold" style="background-color: #d6f0db; color: #193760;">А</td>
                </tr>

                <tr>
                  <td class="border border-brand-gray-light-2 px-3 py-2 font-semibold" style="background-color: #d6e4f0; color: #193760;">ВВВ</td>
                  <td rowspan="3" class="border border-brand-gray-light-2 px-3 py-2 align-middle font-medium" style="background-color: #d6e4f0; color: #193760;">Средний рейтинг надёжности</td>
                </tr>
                <tr>
                  <td class="border border-brand-gray-light-2 px-3 py-2 font-semibold" style="background-color: #d6e4f0; color: #193760;">ВВ</td>
                </tr>
                <tr>
                  <td class="border border-brand-gray-light-2 px-3 py-2 font-semibold" style="background-color: #d6e4f0; color: #193760;">В</td>
                </tr>

                <tr>
                  <td class="border border-brand-gray-light-2 px-3 py-2 font-semibold" style="background-color: #f0ead6; color: #193760;">ССС</td>
                  <td rowspan="3" class="border border-brand-gray-light-2 px-3 py-2 align-middle font-medium" style="background-color: #f0ead6; color: #193760;">Низкий рейтинг надёжности</td>
                </tr>
                <tr>
                  <td class="border border-brand-gray-light-2 px-3 py-2 font-semibold" style="background-color: #f0ead6; color: #193760;">СС</td>
                </tr>
                <tr>
                  <td class="border border-brand-gray-light-2 px-3 py-2 font-semibold" style="background-color: #f0ead6; color: #193760;">С</td>
                </tr>

                <tr>
                  <td class="border border-brand-gray-light-2 px-3 py-2 font-semibold" style="background-color: #f0d6d6; color: #193760;">Д</td>
                  <td class="border border-brand-gray-light-2 px-3 py-2 align-middle font-medium" style="background-color: #f0d6d6; color: #193760;">Банкротство</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </main>
