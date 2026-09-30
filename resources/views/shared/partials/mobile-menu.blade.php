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
                @php($itemUrl = $item['url'] ?? '#')
                @php($itemChildren = is_array($item['children'] ?? null) ? $item['children'] : [])
                <li x-data="{ expanded: false }">
                    @if ($itemChildren !== [])
                        <button
                            type="button"
                            class="flex w-full items-center justify-between gap-3 py-1 text-left hover:text-brand-blue"
                            @click="expanded = !expanded"
                            :aria-expanded="expanded"
                        >
                            <span>{{ $item['label'] }}</span>
                            <svg viewBox="0 0 20 20" class="size-4 shrink-0 transition-transform" :class="expanded && 'rotate-180'" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m5 7.5 5 5 5-5" /></svg>
                        </button>
                        <ul x-show="expanded" x-cloak x-transition class="mt-4 space-y-2 border-l-2 border-brand-blue/20 pl-4">
                            @foreach ($itemChildren as $child)
                                @php($childUrl = $child['url'] ?? '#')
                                <li>
                                    @if ($childUrl === 'modal:request')
                                        <button type="button" class="block py-1 text-left text-brand-gray-dark hover:text-brand-blue" @click="mobileMenuOpen = false; requestModalOpen = true">{{ $child['label'] ?? '' }}</button>
                                    @else
                                        <a href="{{ $childUrl }}" class="block py-1 text-brand-gray-dark hover:text-brand-blue" @click="mobileMenuOpen = false">{{ $child['label'] ?? '' }}</a>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @elseif ($itemUrl === 'modal:request')
                        <button type="button" class="hover:underline underline-offset-1 block py-1 text-left" @click="mobileMenuOpen = false; requestModalOpen = true">{{ $item['label'] }}</button>
                    @elseif ($itemUrl === '#')
                        <a href="#" class="hover:underline underline-offset-1 block py-1" @click="mobileMenuOpen = false">{{ $item['label'] }}</a>
                    @else
                        <a href="{{ $itemUrl }}" class="hover:underline underline-offset-1 block py-1" @click="mobileMenuOpen = false">{{ $item['label'] }}</a>
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
