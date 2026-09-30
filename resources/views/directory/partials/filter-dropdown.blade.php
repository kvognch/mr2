@php
    $selectedValue = $selectedValue === null ? '' : (string) $selectedValue;
    $displayLabel = $placeholder;

    if ($selectedValue !== '') {
        foreach ($choices as $choice) {
            if ((string) $choice['value'] === $selectedValue) {
                $displayLabel = $choice['label'];
                break;
            }
        }
    }

    $menuClass = $menuStyle === 'territory'
        ? 'w-full min-w-[200px] max-h-96 2xl:max-h-120 absolute top-full left-0 space-y-0.75 bg-white rounded-xl overflow-y-auto mt-4 z-20 shadow-lg'
        : 'w-full min-w-[200px] max-h-60 absolute top-full left-0 bg-white rounded-xl overflow-y-auto mt-2 z-20 shadow-lg border border-brand-gray-light-2';

    $optionClass = $menuStyle === 'territory'
        ? 'w-full flex-between text-base text-left hover:bg-brand-blue hover:text-white smooth px-5 py-2.5'
        : 'w-full text-base text-left px-5 py-2.5 smooth hover:bg-brand-blue hover:text-white text-brand-dark';
@endphp

<div
    x-data="{ open: false, selectedValue: '', selectedLabel: '' }"
    x-init="selectedValue = $el.dataset.selectedValue; selectedLabel = $el.dataset.selectedLabel"
    data-selected-value="{{ $selectedValue }}"
    data-selected-label="{{ $displayLabel }}"
    data-placeholder="{{ $placeholder }}"
    data-auto-submit="{{ $autoSubmit ? 'true' : 'false' }}"
    @click.outside="open = false"
    @keydown.escape.window="open = false"
    @directory-search-restored.window="
        selectedValue = new URLSearchParams(window.location.search).get($refs.selectedValue.name) || '';
        const selectedChoice = Array.from($el.querySelectorAll('button[data-value]')).find((choice) => choice.dataset.value === selectedValue);
        selectedLabel = selectedValue ? (selectedChoice?.dataset.label || $el.dataset.placeholder) : $el.dataset.placeholder;
        $refs.selectedValue.value = selectedValue;
        open = false;
    "
    class="relative"
>
    <input type="hidden" name="{{ $name }}" value="{{ $selectedValue }}" x-ref="selectedValue">
    <button
        type="button"
        aria-label="{{ $ariaLabel }}"
        class="h-10 xl:h-12.5 w-full text-sm/5.5 xl:text_6 flex-between gap-2.5 text-brand-gray-dark bg-brand-gray-light-2 outline-brand-dark rounded-xl py-2.5 px-5"
        @click="open = !open"
        :aria-expanded="open"
    >
        <span class="min-w-0 flex-1 text-left line-clamp-1" :class="selectedValue !== '' && 'text-brand-dark'" x-text="selectedLabel">{{ $displayLabel }}</span>
        <svg width="17" height="10" class="shrink-0 transition-transform duration-200" :class="open && 'rotate-180'" aria-hidden="true">
            <use href="#icon-select-chevron" />
        </svg>
    </button>

    <ul
        x-show="open"
        x-cloak
        @if ($menuStyle === 'territory')
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 -translate-y-1"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-1"
        @endif
        class="{{ $menuClass }}"
    >
        @foreach ($choices as $choice)
            <li>
                <button
                    type="button"
                    class="{{ $optionClass }}"
                    data-value="{{ $choice['value'] }}"
                    data-label="{{ $choice['label'] }}"
                    @click="
                        selectedValue = $event.currentTarget.dataset.value;
                        selectedLabel = $event.currentTarget.dataset.label;
                        $refs.selectedValue.value = selectedValue;
                        open = false;
                        if ($el.closest('[data-auto-submit]')?.dataset.autoSubmit === 'true') {
                            $el.closest('form').requestSubmit ? $el.closest('form').requestSubmit() : $el.closest('form').submit();
                        }
                    "
                >
                    <span>{{ $choice['label'] }}</span>
                    @if ($menuStyle === 'territory')
                        <svg
                            data-value="{{ $choice['value'] }}"
                            x-show="selectedValue === $el.dataset.value"
                            width="24"
                            height="24"
                            viewBox="0 0 24 24"
                            fill="none"
                            xmlns="http://www.w3.org/2000/svg"
                            class="shrink-0 size-5"
                        >
                            <path d="M9.00016 16.17L4.83016 12L3.41016 13.41L9.00016 19L21.0002 6.99997L19.5902 5.58997L9.00016 16.17Z" fill="currentColor" />
                        </svg>
                    @endif
                </button>
            </li>
        @endforeach
    </ul>
</div>
