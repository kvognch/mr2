<section id="searching-organization" class="container-base pb-10 sm:pb-16 md:pb-25 lg:pb-32 3xl:pb-50">
    <div class="space-y-2 lg:space-y-5 text-center">
        <h2>{{ $settings['search']['title'] }}</h2>
        <p class="md:max-w-2/3 lg:max-w-5xl mx-auto text_2 text-brand-gray-dark xl:px-20">{{ $settings['search']['description'] }}</p>
    </div>

    <div class="grid xs:grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4 md:gap-5 3xl:gap-8 mt-12 3xl:mt-20">
        @foreach ($settings['search']['categories'] as $category)
            <div class="flex flex-col items-center justify-center gap-4 3xl:gap-5 bg-brand-gray-light-2 rounded-brand-base 3xl:rounded-brand-3xl p-6 3xl:p-10">
                <img src="{{ (fn ($path) => $path !== '' ? (str_starts_with($path, 'assets/') ? asset($path) : \Illuminate\Support\Facades\Storage::disk('public')->url($path)) : '')($category['icon'] ?? '') }}" alt="{{ $category['title'] }}" class="size-16 3xl:size-20" />
                <h4 class="text-center">{{ $category['title'] }}</h4>
            </div>
        @endforeach
    </div>
</section>
