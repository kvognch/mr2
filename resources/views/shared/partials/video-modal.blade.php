<div
    x-show="videoModalOpen"
    x-cloak
    class="fixed inset-0 z-50 bg-black/50 flex-center px-4"
    role="dialog"
    aria-modal="true"
    aria-labelledby="video-modal-title"
    @keydown.escape.window="videoModalOpen = false"
    @click="videoModalOpen = false"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
>
    <div
        class="relative inline-block max-h-[90vh] max-w-[calc(100vw-2rem)] overflow-y-auto space-y-6 bg-white rounded-brand-base pt-12 pb-5 xs:pb-6 md:pb-8 px-5 xs:px-6 md:px-8"
        @click.stop
        x-show="videoModalOpen"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
    >
        <button type="button" class="absolute top-5 right-5 xs:right-6 md:top-6 md:right-6 p-1 -mr-1 rounded-lg hover:bg-brand-gray-light smooth text-brand-dark" aria-label="Закрыть" @click="videoModalOpen = false">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4.99951 18.998L11.9995 11.998M11.9995 11.998L18.9995 4.99805M11.9995 11.998L4.99951 4.99805M11.9995 11.998L18.9995 18.998" stroke="#193760" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" /></svg>
        </button>

        <div class="flex-center">
            <h4 id="video-modal-title" class="text-center">{{ $settings['hero']['video_button_text'] }}</h4>
        </div>

        <template x-if="videoModalOpen">
            <div class="inline-block max-w-full [&_iframe]:aspect-video [&_iframe]:h-auto [&_iframe]:max-w-full">
                {!! $settings['hero']['video_embed_code'] ?? '' !!}
            </div>
        </template>
    </div>
</div>
