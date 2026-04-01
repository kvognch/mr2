@php
    $requestErrors = $errors->getBag('requestModal');
    $shouldOpenRequestModal = $requestErrors->any() || session()->has('request_modal_success') || session('open_request_modal');
@endphp

<div
    x-show="requestModalOpen"
    x-cloak
    class="fixed inset-0 z-50 bg-black/50 flex-center px-4"
    role="dialog"
    aria-modal="true"
    aria-labelledby="request-modal-title"
    @keydown.escape.window="requestModalOpen = false"
    @click="requestModalOpen = false"
    x-init="@if($shouldOpenRequestModal) requestModalOpen = true; @endif"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
>
    <div class="relative">
        <form
            method="POST"
            action="{{ route('request.store') }}"
            class="relative max-h-[90vh] overflow-y-auto max-w-125 w-full space-y-4 bg-white rounded-brand-base pt-12 pb-5 xs:pb-6 md:pb-8 px-5 xs:px-6 md:px-8"
            @click.stop
            x-show="requestModalOpen"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
        >
            @csrf
            <button type="button" class="absolute top-5 right-5 xs:right-6 md:top-6 md:right-6 p-1 -mr-1 rounded-lg hover:bg-brand-gray-light smooth text-brand-dark" aria-label="Закрыть" @click="requestModalOpen = false">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4.99951 18.998L11.9995 11.998M11.9995 11.998L18.9995 4.99805M11.9995 11.998L4.99951 4.99805M11.9995 11.998L18.9995 18.998" stroke="#193760" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" /></svg>
            </button>
            <div class="flex-center">
                <h4 id="request-modal-title" class="text-center">Оставить заявку</h4>
            </div>
            @if (session('request_modal_success'))
                <div class="rounded-brand-base bg-brand-gray-light-2 p-4 text_8 text-brand-dark">
                    {{ session('request_modal_success') }}
                </div>
            @endif
            @if ($requestErrors->any())
                <div class="rounded-brand-base bg-red-50 p-4 text_8 text-red-700">
                    {{ $requestErrors->first() }}
                </div>
            @endif
            <div class="grid grid-cols-2 gap-4">
                <label class="sr-only" for="request-name-1">Имя</label>
                <input id="request-name-1" name="name" type="text" required value="{{ old('name') }}" placeholder="Имя*" class="w-full text_9 bg-brand-gray-light-2 rounded-brand-base outline-brand-dark p-4" />
                <label class="sr-only" for="request-phone-1">Телефон</label>
                <input id="request-phone-1" name="phone" type="tel" required value="{{ old('phone') }}" placeholder="+7 (___) ___-__-__" maxlength="18" class="w-full text_9 bg-brand-gray-light-2 rounded-brand-base outline-brand-dark p-4" data-phone-mask />
            </div>
            <label class="sr-only" for="request-comment-1">Комментарий</label>
            <textarea id="request-comment-1" name="comment" placeholder="Комментарий" rows="4" class="w-full text_9 bg-brand-gray-light-2 rounded-brand-base outline-brand-dark p-4 resize-none">{{ old('comment') }}</textarea>
            <input type="hidden" name="source_url" value="{{ url()->current() }}" />
            <div class="flex-center">
                <div class="g-recaptcha" data-sitekey="{{ $settings['google_recaptcha']['site_key'] ?? '' }}" data-theme="light" data-size="normal"></div>
            </div>
            <button type="submit" class="button_3 w-full">Отправить</button>
        </form>
    </div>
</div>
