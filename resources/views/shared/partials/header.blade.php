<header
    class="sticky top-0 z-50 bg-white pt-1 sm:pt-3 lg:pt-6 xl:pt-10 2xl:pt-12 3xl:pt-20 transition-[padding,transform,box-shadow] duration-300 ease-in-out"
    :class="[
        $store.scroll.collapsed ? 'pb-0' : 'pb-1 sm:pb-3 lg:pb-8',
        ($store.scroll.y > 64 || mobileMenuOpen) && 'shadow-lg',
        $store.scroll.y > 64 && '-translate-y-1 sm:-translate-y-3 lg:-translate-y-6 xl:-translate-y-10 2xl:-translate-y-12 3xl:-translate-y-20'
    ]"
>
    <nav class="container-base flex-between transition-[padding] duration-300 ease-in-out" :class="$store.scroll.y > 64 ? 'py-3 xs:py-3.75 3xl:py-5' : 'py-3.75'">
        <h4>
            <a href="/">{{ $settings['header']['brand'] }}</a>
        </h4>

        <div class="flex-base gap-11">
            <ul class="text_4 hidden lg:flex-base gap-6 3xl:gap-10">
                @foreach ($settings['header']['menu'] as $item)
                    <li>
                        @if (($item['url'] ?? '#') === '#')
                            <a href="#" class="hover:underline underline-offset-1">{{ $item['label'] }}</a>
                        @else
                            <a href="{{ $item['url'] }}" class="hover:underline underline-offset-1">{{ $item['label'] }}</a>
                        @endif
                    </li>
                @endforeach
            </ul>

            @if (auth()->check())
                <a href="{{ $settings['header']['login_button_url'] ?? '/dashboard' }}" class="button_1 hidden lg:block">{{ $settings['header']['login_button_text_auth'] ?? 'Личный кабинет' }}</a>
            @else
                <button type="button" class="button_1 hidden lg:block" @click="authModalOpen = true; authModalMode = 'login'">{{ $settings['header']['login_button_text_guest'] ?? 'Вход / Регистрация' }}</button>
            @endif

            <div class="relative lg:hidden">
                <button
                    type="button"
                    class="p-1 -mr-1 rounded-lg hover:bg-brand-gray-light smooth"
                    @click="mobileMenuOpen = !mobileMenuOpen"
                    :aria-expanded="mobileMenuOpen"
                    aria-label="Меню"
                >
                    <svg x-show="!mobileMenuOpen" x-cloak xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                    <svg x-show="mobileMenuOpen" x-cloak width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M5 18.998L12 11.998M12 11.998L19 4.99805M12 11.998L5 4.99805M12 11.998L19 18.998" stroke="#193760" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </button>
            </div>
        </div>
    </nav>
</header>

@include('shared.partials.mobile-menu', ['settings' => $settings])
