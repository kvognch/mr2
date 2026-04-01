<section
    id="hero"
    class="bg-hero bg-no-repeat bg-cover bg-center relative after:content-[''] after:absolute after:bottom-0 after:left-0 after:w-full after:h-1/8 after:bg-linear-to-t after:from-white after:to-transparent after:pointer-events-none before:content-[''] before:absolute before:top-0 before:left-0 before:w-full before:h-1/8 before:bg-linear-to-b before:from-white before:to-transparent before:pointer-events-none"
>
    <div class="container-base pt-28 3xl:pt-32 pb-16 md:pb-25 lg:pb-50">
        <div>
            <h1 class="lg:max-w-2/3 3xl:max-w-348 font-semibold text-3xl/8.75 sm:text-4xl/11.25 md:text-4xl/11.25 lg:text-[40px]/12.5 xl:text-[54px]/16.25 2xl:text-[64px]/19.25 3xl:text-[70px]/20">
                {{ $settings['hero']['title'] }}
            </h1>
            <p class="text_2 lg:max-w-2/3 3xl:max-w-315 text-brand-gray-dark mt-2 3xl:mt-5">
                {{ $settings['hero']['description'] }}
            </p>
        </div>

        <div class="flex-base flex-col md:flex-row items-center sm:items-start md:items-center gap-6 3xl:gap-10 mt-16 3xl:mt-25">
            <div class="w-full md:w-auto grid xs:grid-cols-2 gap-4 md:gap-6">
                <a href="{{ $settings['hero']['primary_button_url'] ?? '#' }}" class="button_2 text-center justify-center">{{ $settings['hero']['primary_button_text'] }}</a>
                @if (auth()->check())
                    <a href="{{ $settings['hero']['secondary_button_url'] ?? '/dashboard' }}" class="button_2 text-center justify-center">{{ $settings['hero']['secondary_button_text_auth'] ?? 'Личный кабинет' }}</a>
                @else
                    <button type="button" class="button_2 text-center justify-center" @click="authModalOpen = true; authModalMode = 'register'">{{ $settings['hero']['secondary_button_text_guest'] ?? 'Присоединиться' }}</button>
                @endif
            </div>
            <button type="button" class="flex-center sm:flex-base gap-4 group text-brand-gray-dark hover:text-brand-blue smooth" @click="videoModalOpen = true">
                <svg width="60" height="60" viewBox="0 0 60 60" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M30 55C43.8071 55 55 43.8071 55 30C55 16.1929 43.8071 5 30 5C16.1929 5 5 16.1929 5 30C5 43.8071 16.1929 55 30 55Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    <path d="M25 20L40 30L25 40V20Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                <span class="text_8 text-left">
                    {!! collect(preg_split('/\s+/u', trim($settings['hero']['video_button_text'] ?? ''), 3))
                        ->filter(fn ($part) => $part !== '')
                        ->pipe(function ($parts) {
                            if ($parts->count() <= 2) {
                                return e($parts->implode(' '));
                            }

                            return e($parts->slice(0, 2)->implode(' ')) . '<br>' . e($parts->slice(2)->implode(' '));
                        }) !!}
                </span>
            </button>
        </div>

        <div class="max-w-278 mx-auto grid sm:grid-cols-3 gap-20 sm:gap-10 3xl:gap-40 text-center mt-20 3xl:mt-45">
            @foreach ($settings['hero']['stats'] as $stat)
                <div class="space-y-2.5">
                    <h3 class="text-3xl/6.25 sm:text-4xl/11.25 3xl:text-[50px]/15">{!! strip_tags($stat['value'], '<sup><sub><br>') !!}</h3>
                    <p class="text_4 text-brand-gray-dark">{!! nl2br(e($stat['description'])) !!}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>
