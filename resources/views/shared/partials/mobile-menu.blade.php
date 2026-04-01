<div class="fixed inset-0 z-50 top-15.5 sm:top-18 lg:hidden" :class="mobileMenuOpen ? 'pointer-events-auto' : 'pointer-events-none'">
    <div
        x-show="mobileMenuOpen"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-x-0 bottom-0 top-15.5 sm:top-18 bg-black/50"
        @click="mobileMenuOpen = false"
        aria-hidden="true"
    ></div>
    <div
        x-show="mobileMenuOpen"
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
                    @if (($item['url'] ?? '#') === '#')
                        <a href="#" class="hover:underline underline-offset-1 block py-1" @click="mobileMenuOpen = false">{{ $item['label'] }}</a>
                    @else
                        <a href="{{ $item['url'] }}" class="hover:underline underline-offset-1 block py-1" @click="mobileMenuOpen = false">{{ $item['label'] }}</a>
                    @endif
                </li>
            @endforeach
        </ul>
        @if (auth()->check())
            <a href="{{ $settings['header']['login_button_url'] ?? '/dashboard' }}" class="button_1 mt-8 w-full flex-center" @click="mobileMenuOpen = false">{{ $settings['header']['login_button_text_auth'] ?? 'Личный кабинет' }}</a>
        @else
            <button type="button" class="button_1 mt-8 w-full" @click="mobileMenuOpen = false; authModalOpen = true; authModalMode = 'login'">{{ $settings['header']['login_button_text_guest'] ?? 'Вход / Регистрация' }}</button>
        @endif
    </div>
</div>
