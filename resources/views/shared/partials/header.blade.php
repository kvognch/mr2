<header
    class="sticky top-0 z-50 bg-white pt-1 pb-1 sm:pt-3 sm:pb-3 lg:pt-6 lg:pb-8 xl:pt-8 2xl:pt-10 3xl:pt-20 transition-[padding,transform,box-shadow] duration-300 ease-in-out"
    :class="[
        $store.scroll.collapsed ? '!pb-0' : '',
        ($store.scroll.y > 64 || mobileMenuOpen) && 'shadow-lg',
        $store.scroll.y > 64 && '-translate-y-1 sm:-translate-y-3 lg:-translate-y-6 xl:-translate-y-8 2xl:-translate-y-10 3xl:-translate-y-20'
    ]"
>
    <nav class="container-base flex-between py-3.75 transition-[padding] duration-300 ease-in-out" :class="$store.scroll.y > 64 ? '!py-3 xs:!py-3.75 3xl:!py-5' : ''">
        <h4>
            <a href="/">{{ $settings['header']['brand'] }}</a>
        </h4>

        <div class="flex-base gap-11">
            <ul class="text_4 hidden lg:flex-base gap-6 3xl:gap-10">
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
                            <ul x-show="open" x-cloak x-transition class="absolute left-0 top-full z-50 mt-3 w-max whitespace-nowrap space-y-1 rounded-xl bg-white p-2 lg:text-base 3xl:text-xl shadow-xl">
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
                <a href="{{ $settings['header']['login_button_url'] ?? '/dashboard' }}" class="button_1 !hidden lg:!inline-flex">{{ $settings['header']['login_button_text_auth'] ?? 'Личный кабинет' }}</a>
            @else
                <button type="button" class="button_1 !hidden lg:!inline-flex" @click="authModalOpen = true; authModalMode = 'login'">{{ $settings['header']['login_button_text_guest'] ?? 'Вход / Регистрация' }}</button>
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
