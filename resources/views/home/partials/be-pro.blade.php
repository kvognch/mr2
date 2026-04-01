<section id="be-pro" class="pb-10 sm:pb-16 md:pb-25 lg:pb-50">
    <div class="container-base">
        <div class="flex-base flex-col lg:flex-row gap-10 3xl:gap-17 pb-20">
            <div class="space-y-3 lg:space-y-5">
                <div class="space-y-2 2xs:space-y-5 3xl:pr-10">
                    <h2>{{ $settings['be_pro']['title'] }}</h2>
                    <p class="text_2 text-brand-gray-dark">{{ $settings['be_pro']['description'] }}</p>
                </div>
            </div>
            <div class="lg:max-w-130 w-full">
                <img src="{{ (fn ($path) => $path !== '' ? (str_starts_with($path, 'assets/') ? asset($path) : \Illuminate\Support\Facades\Storage::disk('public')->url($path)) : '')($settings['be_pro']['image']) }}" alt="{{ $settings['be_pro']['title'] }}" class="size-full object-cover" />
            </div>
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-8 3xl:gap-14">
            @foreach ($settings['be_pro']['cards'] as $card)
                <div class="flex flex-col items-center justify-center gap-4 3xl:gap-5 text-center bg-brand-gray-light-2 rounded-brand-3xl p-10">
                    <img src="{{ (fn ($path) => $path !== '' ? (str_starts_with($path, 'assets/') ? asset($path) : \Illuminate\Support\Facades\Storage::disk('public')->url($path)) : '')($card['icon'] ?? '') }}" alt="{{ $card['title'] }}" class="size-20 lg:size-25" />
                    <div class="space-y-2 sm:space-y-4">
                        <h4>{{ $card['title'] }}</h4>
                        <p class="text_2 text-brand-gray-dark">{{ $card['description'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
