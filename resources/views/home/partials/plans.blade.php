<section id="prices" class="pb-10 sm:pb-16 md:pb-25 lg:pb-50">
    <div class="container-base space-y-10 lg:space-y-15">
        <div class="space-y-2">
            <h2>{{ $settings['plans']['title'] }}</h2>
            <p class="text_2 max-w-5xl text-brand-gray-dark">{{ $settings['plans']['description'] }}</p>
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5 md:gap-8 lg:gap-10">
            @foreach ($settings['plans']['items'] as $item)
                <div class="space-y-5 lg:space-y-6 3xl:space-y-10 bg-brand-gray-light-2 rounded-brand-base lg:rounded-brand-3xl py-6 px-5 lg:px-6 3xl:p-10">
                    <div class="space-y-2.5">
                        <h4>{{ $item['title'] }}</h4>
                        <p class="text_2 text-brand-gray-dark">{{ $item['description'] }}</p>
                        <p class="text-xl/6.25 lg:text-2xl/8 3xl:text-[32px]/9.75"><span class="font-semibold">{{ $item['price'] }}</span></p>
                    </div>

                    <button type="button" class="button_3" @click="requestModalOpen = true">{{ $item['button_text'] }}</button>
                </div>
            @endforeach
        </div>
    </div>
</section>
