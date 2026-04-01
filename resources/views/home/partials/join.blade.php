<section id="join-the-platform" class="scroll-mt-12 lg:scroll-mt-14 xl:scroll-mt-16 2xl:scroll-mt-18 3xl:scroll-mt-22 pb-10 sm:pb-16 md:pb-25 lg:pb-50">
    <div class="bg-brand-gray-light-2 py-20">
        <div class="container-base space-y-20 lg:space-y-15">
            <h2 class="text-center">{{ $settings['join']['title'] }}</h2>

            <div class="relative">
                <div class="max-w-300 w-full absolute top-0 xl:top-[6%] 2xl:top-1/9 left-1/2 -translate-x-1/2 flex-between flex-col lg:flex-row">
                    <div class="size-30 shrink-0 bg-brand-gray-light-2 rounded-full"></div>
                    <div class="h-0.5 xl:h-0.75 flex-1 bg-brand-gray"></div>
                    <div class="size-30 shrink-0 bg-brand-gray-light-2 rounded-full"></div>
                    <div class="h-0.5 xl:h-0.75 flex-1 bg-brand-gray"></div>
                    <div class="size-30 shrink-0 bg-brand-gray-light-2 rounded-full"></div>
                </div>

                <div class="relative z-10 max-w-410 mx-auto grid lg:grid-cols-3 gap-32 lg:gap-15 mt-5 lg:mt-10">
                    @foreach ($settings['join']['steps'] as $step)
                        <div class="relative flex-base flex-col text-center">
                            @if (! $loop->first)
                                <div class="w-0.5 h-28 lg:hidden shrink-0 absolute -top-1/2 3xs:-top-[55%] xs:-top-1/2 sm:-top-[45%] md:-top-1/2 left-1/2 -translate-x-1/2 bg-brand-gray"></div>
                            @endif
                            <div class="bg-brand-gray-light-2 rounded-full p-5 xl:p-10">
                                <div class="size-16 xs:size-20 2xl:size-30 shrink-0 flex-center border-2 xl:border-[3px] border-brand-gray-dark rounded-full">
                                    <h2>{{ $loop->iteration }}</h2>
                                </div>
                            </div>
                            <div class="xs:max-w-2/3 sm:max-w-1/2 lg:max-w-none space-y-3 sm:space-y-5">
                                <h4 class="text-2xl/8 md:text-3xl/6.25">{{ $step['title'] }}</h4>
                                <p class="text-base/6 sm:text_2 text-brand-gray-dark 3xl:px-6">{{ $step['description'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="flex-center">
                @if (auth()->check())
                    <a href="{{ $settings['join']['cta_button_url'] ?? '/dashboard' }}" class="button_3 max-w-125 w-full text-center justify-center">{{ $settings['join']['cta_button_text_auth'] ?? 'Личный кабинет' }}</a>
                @else
                    <button type="button" class="button_3 max-w-125 w-full" @click="authModalOpen = true; authModalMode = 'register'">{{ $settings['join']['cta_button_text_guest'] ?? 'Присоединиться' }}</button>
                @endif
            </div>
        </div>
    </div>
</section>
