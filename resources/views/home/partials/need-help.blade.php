<section id="need-help" class="scroll-mt-24 lg:scroll-mt-28 xl:scroll-mt-32 2xl:scroll-mt-36 3xl:scroll-mt-44 pb-15 sm:pb-24 md:pb-37.5 lg:pb-75">
    <div class="container-base flex-base flex-col lg:flex-row gap-10 xl:gap-16 3xl:gap-26.25 2xl:px-30.5">
        <div class="sm:max-w-1/2 2xl:max-w-150.75">
            <img src="{{ (fn ($path) => $path !== '' ? (str_starts_with($path, 'assets/') ? asset($path) : \Illuminate\Support\Facades\Storage::disk('public')->url($path)) : '')($settings['need_help']['image']) }}" alt="{{ $settings['need_help']['button_text'] }}" class="size-full object-cover" />
        </div>
        <div class="space-y-5 md:space-y-7.5 lg:space-y-10">
            <div class="space-y-3 sm:space-y-5 text-center lg:text-left">
                <h2>{{ $settings['need_help']['title'] }}</h2>
                <p class="text_2 text-brand-gray-dark">{{ $settings['need_help']['description'] }}</p>
            </div>

            <div class="flex-center lg:justify-start">
                <button type="button" class="button_2" @click="requestModalOpen = true">{{ $settings['need_help']['button_text'] }}</button>
            </div>
        </div>
    </div>
</section>
