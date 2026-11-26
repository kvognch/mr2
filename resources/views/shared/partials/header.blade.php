<header
    class="sticky top-0 z-50 bg-white py-1 sm:py-3 transition-[padding,box-shadow] duration-300 ease-in-out"
    :class="[
        $store.scroll.collapsed && 'py-0!',
        ($store.scroll.y > 64 || mobileMenuOpen) && 'shadow-lg'
    ]"
>
    <nav class="container-base flex-between gap-3 py-3.75 md:!px-5 lg:!px-8 xl:!px-12 2xl:!px-20 transition-[padding] duration-300 ease-in-out" :class="$store.scroll.y > 64 ? '!py-3 xs:!py-3.75 3xl:!py-5' : ''">
        <h4 class="shrink-0 md:text-sm lg:text-lg xl:text-2xl">
            <a href="/">{{ $settings['header']['brand'] }}</a>
        </h4>

        <div class="flex-base gap-2 lg:gap-4 xl:gap-6 3xl:gap-11">
            <ul class="hidden lg:flex-base shrink-0 whitespace-nowrap font-medium md:text-xs md:gap-2 lg:text-base lg:gap-4 xl:text-lg xl:gap-6 2xl:text-xl 2xl:gap-8 3xl:text-2xl 3xl:gap-8">
                @foreach ($settings['header']['menu'] as $item)
                    @php($itemUrl = $item['url'] ?? '#')
                    @php($itemChildren = is_array($item['children'] ?? null) ? $item['children'] : [])
                    <li x-data="{ open: false }" @click.outside="open = false" @keydown.escape.window="open = false" class="relative">
                        @if ($itemChildren !== [])
                            <button
                                type="button"
                                class="inline-flex items-center gap-1 hover:underline underline-offset-1"
                                @click="open = !open"
                                :aria-expanded="open"
                            >
                                {{ $item['label'] }}
                                <svg viewBox="0 0 20 20" class="size-4 transition-transform" :class="open && 'rotate-180'" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m5 7.5 5 5 5-5" /></svg>
                            </button>
                            <ul x-show="open" x-cloak x-transition class="absolute left-0 top-full z-50 mt-3 w-max whitespace-nowrap space-y-1 rounded-xl bg-white p-2 text-xs lg:text-sm xl:text-base 2xl:text-lg 3xl:text-xl shadow-xl">
                                @foreach ($itemChildren as $child)
                                    @php($childUrl = $child['url'] ?? '#')
                                    <li>
                                        @if ($childUrl === 'modal:request')
                                            <button type="button" class="block w-full rounded-lg px-3 py-2 text-left hover:underline underline-offset-1" @click="requestModalOpen = true; open = false">{{ $child['label'] ?? '' }}</button>
                                        @else
                                            <a href="{{ $childUrl }}" class="block w-full rounded-lg px-3 py-2 hover:underline underline-offset-1" @click="open = false">{{ $child['label'] ?? '' }}</a>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        @elseif ($itemUrl === 'modal:request')
                            <button type="button" class="hover:underline underline-offset-1" @click="requestModalOpen = true">{{ $item['label'] }}</button>
                        @elseif ($itemUrl === '#')
                            <a href="#" class="hover:underline underline-offset-1">{{ $item['label'] }}</a>
                        @else
                            <a href="{{ $itemUrl }}" class="hover:underline underline-offset-1">{{ $item['label'] }}</a>
                        @endif
                    </li>
                @endforeach
            </ul>

            @if (auth()->check())
                <a href="{{ $settings['header']['login_button_url'] ?? '/dashboard' }}" class="button_1 !hidden lg:!inline-flex shrink-0 whitespace-nowrap md:!text-xs md:!px-3 md:!py-2 lg:!text-sm lg:!px-4 lg:!py-3 xl:!text-base xl:!px-6 3xl:!text-xl">{{ $settings['header']['login_button_text_auth'] ?? 'Личный кабинет' }}</a>
            @else
                <button type="button" class="button_1 !hidden lg:!inline-flex shrink-0 whitespace-nowrap md:!text-xs md:!px-3 md:!py-2 lg:!text-sm lg:!px-4 lg:!py-3 xl:!text-base xl:!px-6 3xl:!text-xl" @click="authModalOpen = true; authModalMode = 'login'">{{ $settings['header']['login_button_text_guest'] ?? 'Вход / Регистрация' }}</button>
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
