<section id="need-help" class="scroll-mt-24 lg:scroll-mt-28 xl:scroll-mt-32 2xl:scroll-mt-36 3xl:scroll-mt-44 pb-15 sm:pb-25 md:pb-25 lg:pb-25 3xl:pb-50">
    <div class="container-base flex-base flex-col lg:flex-row gap-10 xl:gap-12 3xl:gap-26.25 2xl:px-30.5">
        <div class="sm:max-w-1/2 xl:max-w-135 3xl:max-w-150.75 flex justify-center xl:block">
            <img src="{{ (fn ($path) => $path !== '' ? (str_starts_with($path, 'assets/') ? asset($path) : \Illuminate\Support\Facades\Storage::disk('public')->url($path)) : '')($settings['need_help']['image']) }}" alt="{{ $settings['need_help']['button_text'] }}" class="size-full object-cover max-w-[80%] xl:max-w-none" />
        </div>
        <div class="space-y-5 md:space-y-7.5 lg:space-y-7">
            <div class="space-y-3 sm:space-y-5 text-center lg:text-left">
                <h2>{{ $settings['need_help']['title'] }}</h2>
                <p class="text_2 text-brand-gray-dark">{{ $settings['need_help']['description'] }}</p>
            </div>

            <div class="flex-center lg:justify-start">
                <a href="#" class="button_2" @click.prevent="requestModalOpen = true">{{ $settings['need_help']['button_text'] }}</a>
            </div>
        </div>
    </div>
</section>
