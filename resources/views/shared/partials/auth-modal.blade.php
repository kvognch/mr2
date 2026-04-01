@php
    $loginErrors = $errors->getBag('authLogin');
    $registerErrors = $errors->getBag('authRegister');
    $openAuthModal = session('open_auth_modal');
    $initialMode = $registerErrors->any() || $openAuthModal === 'register' ? 'register' : 'login';
    $shouldOpen = $loginErrors->any() || $registerErrors->any() || session()->has('auth_register_success') || in_array($openAuthModal, ['login', 'register'], true);
@endphp

<div
    x-show="authModalOpen"
    x-cloak
    class="fixed inset-0 z-50 bg-black/50 flex-center px-4"
    role="dialog"
    aria-modal="true"
    aria-labelledby="auth-modal-title"
    @keydown.escape.window="authModalOpen = false"
    @click="authModalOpen = false"
    x-init="@if($shouldOpen) authModalOpen = true; authModalMode = '{{ $initialMode }}'; @endif"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
>
    <div class="relative">
        <div
            class="relative max-h-[90vh] overflow-y-auto max-w-125 w-full space-y-4 bg-white rounded-brand-base pt-12 pb-5 xs:pb-6 md:pb-8 px-5 xs:px-6 md:px-8"
            @click.stop
            x-show="authModalOpen"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
        >
            <button type="button" class="absolute top-5 right-5 xs:right-6 md:top-6 md:right-6 p-1 -mr-1 rounded-lg hover:bg-brand-gray-light smooth text-brand-dark" aria-label="Закрыть" @click="authModalOpen = false">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4.99951 18.998L11.9995 11.998M11.9995 11.998L18.9995 4.99805M11.9995 11.998L4.99951 4.99805M11.9995 11.998L18.9995 18.998" stroke="#193760" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" /></svg>
            </button>

            <form
                method="POST"
                action="{{ route('login.attempt') }}"
                class="space-y-4"
                x-show="authModalMode === 'login'"
            >
                @csrf

                <div class="flex-center">
                    <h4 id="auth-modal-title" class="text-center">Вход</h4>
                </div>

                @if (session('auth_register_success'))
                    <div class="rounded-brand-base bg-brand-gray-light-2 p-4 text_8 text-brand-dark">
                        {{ session('auth_register_success') }}
                    </div>
                @endif

                @if ($loginErrors->any())
                    <div class="rounded-brand-base bg-red-50 p-4 text_8 text-red-700">
                        {{ $loginErrors->first() }}
                    </div>
                @endif

                <div class="space-y-4">
                    <label class="sr-only" for="auth-login-identifier">Логин</label>
                    <input
                        id="auth-login-identifier"
                        name="identifier"
                        type="text"
                        required
                        value="{{ old('identifier') }}"
                        placeholder="Логин*"
                        autocomplete="username"
                        class="w-full text_9 bg-brand-gray-light-2 rounded-brand-base outline-brand-dark p-4"
                    />

                    <label class="sr-only" for="auth-login-password">Пароль</label>
                    <input
                        id="auth-login-password"
                        name="password"
                        type="password"
                        required
                        placeholder="Пароль*"
                        autocomplete="current-password"
                        class="w-full text_9 bg-brand-gray-light-2 rounded-brand-base outline-brand-dark p-4"
                    />
                </div>

                <button type="submit" class="button_3 w-full">Войти</button>

                <p class="text-center text_8 text-brand-gray-dark">
                    Ещё нет аккаунта?
                    <button type="button" class="text-brand-blue hover:underline" @click="authModalMode = 'register'">Зарегистрироваться</button>
                </p>
            </form>

            <form
                method="POST"
                action="{{ route('register.store') }}"
                class="space-y-4"
                x-show="authModalMode === 'register'"
            >
                @csrf

                <div class="flex-center">
                    <h4 id="auth-modal-title" class="text-center">Регистрация</h4>
                </div>

                @if ($registerErrors->any())
                    <div class="rounded-brand-base bg-red-50 p-4 text_8 text-red-700">
                        {{ $registerErrors->first() }}
                    </div>
                @endif

                <div class="space-y-4">
                    <label class="sr-only" for="auth-register-name">Имя</label>
                    <input
                        id="auth-register-name"
                        name="name"
                        type="text"
                        required
                        value="{{ old('name') }}"
                        placeholder="Имя*"
                        autocomplete="name"
                        class="w-full text_9 bg-brand-gray-light-2 rounded-brand-base outline-brand-dark p-4"
                    />

                    <label class="sr-only" for="auth-register-email">Email</label>
                    <input
                        id="auth-register-email"
                        name="email"
                        type="email"
                        required
                        value="{{ old('email') }}"
                        placeholder="Email*"
                        autocomplete="email"
                        class="w-full text_9 bg-brand-gray-light-2 rounded-brand-base outline-brand-dark p-4"
                    />

                    <label class="sr-only" for="auth-register-phone">Телефон</label>
                    <input
                        id="auth-register-phone"
                        name="phone"
                        type="tel"
                        required
                        value="{{ old('phone') }}"
                        placeholder="+7 (___) ___-__-__"
                        autocomplete="tel"
                        maxlength="18"
                        class="w-full text_9 bg-brand-gray-light-2 rounded-brand-base outline-brand-dark p-4"
                        data-phone-mask
                    />

                    <label class="sr-only" for="auth-register-password">Пароль</label>
                    <input
                        id="auth-register-password"
                        name="password"
                        type="password"
                        required
                        placeholder="Пароль*"
                        autocomplete="new-password"
                        class="w-full text_9 bg-brand-gray-light-2 rounded-brand-base outline-brand-dark p-4"
                    />

                    <label class="sr-only" for="auth-register-password-confirmation">Подтвердите пароль</label>
                    <input
                        id="auth-register-password-confirmation"
                        name="password_confirmation"
                        type="password"
                        required
                        placeholder="Подтвердите пароль*"
                        autocomplete="new-password"
                        class="w-full text_9 bg-brand-gray-light-2 rounded-brand-base outline-brand-dark p-4"
                    />
                </div>

                <div class="flex-center">
                    <div class="g-recaptcha" data-sitekey="{{ $settings['google_recaptcha']['site_key'] ?? '' }}" data-theme="light" data-size="normal"></div>
                </div>

                <button type="submit" class="button_3 w-full">Зарегистрироваться</button>

                <p class="text-center text_8 text-brand-gray-dark">
                    Уже есть аккаунт?
                    <button type="button" class="text-brand-blue hover:underline" @click="authModalMode = 'login'">Войти</button>
                </p>
            </form>
        </div>
    </div>
</div>

@once
    @push('scripts')
        <script>
            window.formatAuthPhoneInput = function (input) {
                const digits = String(input.value || '').replace(/\D+/g, '');

                if (digits === '') {
                    input.value = '';
                    return;
                }

                let localDigits = digits;

                if (localDigits.startsWith('7') || localDigits.startsWith('8')) {
                    localDigits = localDigits.slice(1);
                }

                localDigits = localDigits.slice(0, 10);

                let result = '+7';

                if (localDigits.length > 0) {
                    result += ' (' + localDigits.slice(0, 3);
                }

                if (localDigits.length >= 4) {
                    result += ') ' + localDigits.slice(3, 6);
                }

                if (localDigits.length >= 7) {
                    result += '-' + localDigits.slice(6, 8);
                }

                if (localDigits.length >= 9) {
                    result += '-' + localDigits.slice(8, 10);
                }

                input.value = result;
            };

            window.addEventListener('DOMContentLoaded', () => {
                document.querySelectorAll('[data-phone-mask]').forEach((input) => {
                    if (input.value) {
                        window.formatAuthPhoneInput(input);
                    }

                    input.addEventListener('input', () => {
                        window.formatAuthPhoneInput(input);
                    });

                    input.addEventListener('focus', () => {
                        if (!input.value) {
                            input.value = '+7 (';
                        }
                    });

                    input.addEventListener('blur', () => {
                        const digits = String(input.value || '').replace(/\D+/g, '');

                        if (digits === '' || digits === '7') {
                            input.value = '';
                        }
                    });
                });
            });
        </script>
    @endpush
@endonce
