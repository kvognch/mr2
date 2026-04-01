@php
    $contractorReviewErrors = $errors->getBag('contractorReview');
    $shouldOpenContractorReviewModal = $contractorReviewErrors->any() || session()->has('contractor_review_success') || session('open_contractor_review_modal');
@endphp

<div
    x-show="contractorReviewModalOpen"
    x-cloak
    class="fixed inset-0 z-50 bg-black/50 flex-center px-4"
    role="dialog"
    aria-modal="true"
    aria-labelledby="contractor-review-modal-title"
    @keydown.escape.window="contractorReviewModalOpen = false"
    @click="contractorReviewModalOpen = false"
    x-init="@if($shouldOpenContractorReviewModal) contractorReviewModalOpen = true; @endif"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
>
    <div x-data="reviewModalForm({{ (int) old('rating', 0) }})" class="relative w-full xs:w-auto">
        <form
            method="POST"
            action="{{ route('reviews.contractor.store', ['contractor' => $contractor->slug]) }}"
            class="relative max-h-[90vh] overflow-y-auto max-w-150 w-full space-y-5 bg-white rounded-brand-base p-5 xs:p-6 md:p-10"
            @click.stop
            x-show="contractorReviewModalOpen"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
        >
            @csrf
            <button type="button" class="absolute top-5 right-5 xs:right-6 xs:top-6 md:top-10 md:right-10 p-1 -mr-1 rounded-lg hover:bg-brand-gray-light smooth text-brand-dark" aria-label="Закрыть" @click="contractorReviewModalOpen = false">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4.99951 18.998L11.9995 11.998M11.9995 11.998L18.9995 4.99805M11.9995 11.998L4.99951 4.99805M11.9995 11.998L18.9995 18.998" stroke="#193760" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" /></svg>
            </button>

            <div class="flex-between flex-col items-start sm:flex-row sm:items-center gap-3 mt-5 2xs:mt-8 sm:mt-12">
                <h4 id="contractor-review-modal-title">Оставьте отзыв</h4>
                <div class="flex-base gap-1 2xs:gap-1.5 xs:gap-2.5" @mouseleave="hoverRating = 0">
                    <template x-for="i in 5" :key="i">
                        <button type="button" :aria-label="'Оценка ' + i + ' из 5'" class="p-0.5 rounded hover:opacity-80 smooth" @click="rating = i" @mouseenter="hoverRating = i">
                            <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg" class="size-5 xs:size-6 lg:size-auto">
                                <path d="M15.9989 2.66663L20.1189 11.0133L29.3322 12.36L22.6655 18.8533L24.2389 28.0266L15.9989 23.6933L7.75886 28.0266L9.33219 18.8533L2.66553 12.36L11.8789 11.0133L15.9989 2.66663Z" :fill="(hoverRating || rating) >= i ? '#1450A3' : 'none'" stroke="#1450A3" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </button>
                    </template>
                </div>
            </div>
            @if (session('contractor_review_success'))
                <div class="rounded-brand-base bg-brand-gray-light-2 p-4 text_8 text-brand-dark">
                    {{ session('contractor_review_success') }}
                </div>
            @endif
            @if ($contractorReviewErrors->any())
                <div class="rounded-brand-base bg-red-50 p-4 text_8 text-red-700">
                    {{ $contractorReviewErrors->first() }}
                </div>
            @endif
            <input type="hidden" name="rating" :value="rating" />

            <div class="space-y-3 xs:space-y-6 text-brand-gray-dark">
                <div class="grid xs:grid-cols-2 gap-4">
                    <label><input name="author_name" type="text" required value="{{ old('author_name', auth()->user()?->name) }}" placeholder="Имя" class="w-full text_9 bg-brand-gray-light-2 rounded-brand-base outline-brand-dark p-4" /></label>
                    <label><input name="author_role" type="text" required value="{{ old('author_role') }}" placeholder="Род деятельности" class="w-full text_9 bg-brand-gray-light-2 rounded-brand-base outline-brand-dark p-4" /></label>
                    <label class="xs:col-span-2"><input name="title" type="text" required value="{{ old('title') }}" placeholder="Обращение в компанию по вопросу" class="w-full text_9 bg-brand-gray-light-2 rounded-brand-base outline-brand-dark p-4" /></label>
                    <label class="xs:col-span-2"><textarea name="body" required placeholder="Ваш комментарий" class="w-full min-h-45 text_9 bg-brand-gray-light-2 rounded-brand-base outline-brand-dark p-4">{{ old('body') }}</textarea></label>
                </div>
                <label class="flex-base gap-2.5 cursor-pointer text_9 text-brand-gray-dark">
                    <input name="is_recommended" type="hidden" value="0" />
                    <input name="is_recommended" type="checkbox" value="1" class="hidden peer" @checked(old('is_recommended')) />
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="peer-checked:hidden size-4 xs:size-auto"><path fill-rule="evenodd" clip-rule="evenodd" d="M12 24C13.5759 24 15.1363 23.6896 16.5922 23.0866C18.0481 22.4835 19.371 21.5996 20.4853 20.4853C21.5996 19.371 22.4835 18.0481 23.0866 16.5922C23.6896 15.1363 24 13.5759 24 12C24 10.4241 23.6896 8.86371 23.0866 7.4078C22.4835 5.95189 21.5996 4.62902 20.4853 3.51472C19.371 2.40042 18.0481 1.5165 16.5922 0.913445C15.1363 0.310389 13.5759 -2.34822e-08 12 0C8.8174 4.74244e-08 5.76516 1.26428 3.51472 3.51472C1.26428 5.76515 0 8.8174 0 12C0 15.1826 1.26428 18.2348 3.51472 20.4853C5.76516 22.7357 8.8174 24 12 24ZM11.6907 16.8533L18.3573 8.85333L16.3093 7.14667L10.576 14.0253L7.60933 11.0573L5.724 12.9427L9.724 16.9427L10.756 17.9747L11.6907 16.8533Z" fill="#8695AA" /></svg>
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="hidden peer-checked:block size-4 xs:size-auto"><path fill-rule="evenodd" clip-rule="evenodd" d="M12 24C13.5759 24 15.1363 23.6896 16.5922 23.0866C18.0481 22.4835 19.371 21.5996 20.4853 20.4853C21.5996 19.371 22.4835 18.0481 23.0866 16.5922C23.6896 15.1363 24 13.5759 24 12C24 10.4241 23.6896 8.86371 23.0866 7.4078C22.4835 5.95189 21.5996 4.62902 20.4853 3.51472C19.371 2.40042 18.0481 1.5165 16.5922 0.913445C15.1363 0.310389 13.5759 -2.34822e-08 12 0C8.8174 4.74244e-08 5.76516 1.26428 3.51472 3.51472C1.26428 5.76515 0 8.8174 0 12C0 15.1826 1.26428 18.2348 3.51472 20.4853C5.76516 22.7357 8.8174 24 12 24ZM11.6907 16.8533L18.3573 8.85333L16.3093 7.14667L10.576 14.0253L7.60933 11.0573L5.724 12.9427L9.724 16.9427L10.756 17.9747L11.6907 16.8533Z" fill="#1450A3" /></svg>
                        <span class="peer-checked:text-brand-blue">Рекомендуете компанию?</span>
                </label>
            </div>

            <button type="submit" class="button_3 w-full mt-3 lg:mt-6">Оставить отзыв</button>
        </form>
    </div>
</div>
