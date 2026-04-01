<header class="admin-shell-header bg-white" x-data="{ adminHeaderMenuOpen: false }">
    <nav class="container-base flex-between py-3 xs:py-3.75 3xl:py-5">
        <h4>
            <a href="/">{{ $settings['header']['brand'] }}</a>
        </h4>

        <div class="flex-base gap-11">
            <ul class="text_4 hidden lg:flex-base gap-6 3xl:gap-10">
                @foreach ($settings['header']['menu'] as $item)
                    <li>
                        <a href="{{ $item['url'] ?? '#' }}" class="hover:underline underline-offset-1">{{ $item['label'] }}</a>
                    </li>
                @endforeach
            </ul>

            @auth
                <form method="POST" action="{{ filament()->getLogoutUrl() }}" class="admin-shell-header__desktop-exit">
                    @csrf
                    <button type="submit" class="button_1">Выход</button>
                </form>
            @endauth

            <div class="relative lg:hidden">
                <button
                    type="button"
                    class="p-1 -mr-1 rounded-lg hover:bg-brand-gray-light smooth"
                    @click="adminHeaderMenuOpen = !adminHeaderMenuOpen"
                    :aria-expanded="adminHeaderMenuOpen"
                    aria-label="Меню"
                >
                    <svg x-show="!adminHeaderMenuOpen" x-cloak xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                    <svg x-show="adminHeaderMenuOpen" x-cloak width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M5 18.998L12 11.998M12 11.998L19 4.99805M12 11.998L5 4.99805M12 11.998L19 18.998" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </button>
            </div>
        </div>
    </nav>

    <div class="fixed inset-0 z-50 top-15.5 sm:top-18 lg:hidden" :class="adminHeaderMenuOpen ? 'pointer-events-auto' : 'pointer-events-none'">
        <div
            x-show="adminHeaderMenuOpen"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-x-0 bottom-0 top-15.5 sm:top-18 bg-black/50"
            @click="adminHeaderMenuOpen = false"
            aria-hidden="true"
        ></div>
        <div
            x-show="adminHeaderMenuOpen"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-x-full"
            x-transition:enter-end="opacity-100 translate-x-0"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-x-0"
            x-transition:leave-end="opacity-0 translate-x-full"
            class="fixed top-15.5 sm:top-18 right-0 h-screen w-full max-w-[min(20rem,85vw)] bg-white shadow-xl flex flex-col py-8 px-6"
            role="dialog"
            aria-modal="true"
            aria-label="Меню"
        >
            <ul class="text_4 flex flex-col gap-6">
                @foreach ($settings['header']['menu'] as $item)
                    <li>
                        <a href="{{ $item['url'] ?? '#' }}" class="hover:underline underline-offset-1 block py-1" @click="adminHeaderMenuOpen = false">{{ $item['label'] }}</a>
                    </li>
                @endforeach
            </ul>
            @auth
                <form method="POST" action="{{ filament()->getLogoutUrl() }}">
                    @csrf
                    <button type="submit" class="button_1 mt-8 w-full">Выход</button>
                </form>
            @endauth
        </div>
    </div>
</header>
