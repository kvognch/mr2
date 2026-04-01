<section id="searching-organization" class="container-base pb-10 sm:pb-16 md:pb-25 lg:pb-50">
    <div class="space-y-2 lg:space-y-5 text-center">
        <h2>{{ $settings['search']['title'] }}</h2>
        <p class="md:max-w-2/3 lg:max-w-5xl mx-auto text_2 text-brand-gray-dark xl:px-20">{{ $settings['search']['description'] }}</p>
    </div>

    <div class="grid xs:grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 2xl:grid-cols-5 gap-5 md:gap-8 mt-20">
        @foreach ($settings['search']['categories'] as $category)
            <div class="flex flex-col items-center justify-center gap-5 bg-brand-gray-light-2 rounded-brand-3xl p-10">
                <img src="{{ (fn ($path) => $path !== '' ? (str_starts_with($path, 'assets/') ? asset($path) : \Illuminate\Support\Facades\Storage::disk('public')->url($path)) : '')($category['icon'] ?? '') }}" alt="{{ $category['title'] }}" class="size-20" />
                <h4>{{ $category['title'] }}</h4>
            </div>
        @endforeach
    </div>
</section>
