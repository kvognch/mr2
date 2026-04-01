@php
    $footerBorder = $footerBorder ?? false;
    $footerPhoneDisplay = (string) ($settings['footer']['phone_display'] ?? ($settings['footer']['phone'] ?? ''));
    $footerPhoneHref = preg_replace('/[\s()-]+/', '', $footerPhoneDisplay);
    $footerPhoneHref = is_string($footerPhoneHref) ? $footerPhoneHref : $footerPhoneDisplay;
@endphp

<footer class="bg-brand-gray-light-2 py-10 sm:py-16 lg:py-20 {{ $footerBorder ? 'border-t border-brand-gray' : '' }}">
    <div class="container-base space-y-10 lg:space-y-14">
        <div class="flex flex-col lg:flex-row justify-between gap-10 lg:gap-0 md:px-32 lg:px-0">
            <h4 class="xs:text-center lg:text-left">{{ $settings['footer']['brand'] }}</h4>

            <div class="flex-1 flex flex-col xs:flex-row justify-evenly gap-10">
                <div class="space-y-5 lg:space-y-8">
                    <h5 class="text_5">{{ $settings['footer']['group_1_title'] }}</h5>
                    <ul class="text_6 space-y-2.5 lg:space-y-3.5 text-brand-gray-dark">
                        @foreach ($settings['footer']['group_1_links'] as $link)
                            <li><a href="{{ $link['url'] }}" class="hover:underline underline-offset-1">{{ $link['label'] }}</a></li>
                        @endforeach
                    </ul>
                </div>

                <div class="space-y-5 lg:space-y-8">
                    <h5 class="text_5">{{ $settings['footer']['group_2_title'] }}</h5>
                    <ul class="text_6 space-y-2.5 lg:space-y-3.5 text-brand-gray-dark">
                        @foreach ($settings['footer']['group_2_links'] as $link)
                            <li>
                                @if (($link['url'] ?? '#') === '#')
                                    <a href="#" class="hover:underline underline-offset-1">{{ $link['label'] }}</a>
                                @else
                                    <a href="{{ $link['url'] }}" class="hover:underline underline-offset-1">{{ $link['label'] }}</a>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            <div class="flex xs:items-center lg:items-end flex-col gap-10">
                <div class="flex xs:items-center lg:items-end flex-col gap-2.25">
                    <a href="mailto:{{ $settings['footer']['email'] }}" class="text_5 hover:underline underline-offset-1">{{ $settings['footer']['email'] }}</a>
                    <a href="tel:{{ $footerPhoneHref }}" class="text_5 hover:underline underline-offset-1">{{ $footerPhoneDisplay }}</a>
                </div>

                    <div class="flex-base gap-6">
                        @foreach ($settings['footer']['socials'] as $social)
                        <a href="{{ $social['url'] }}" target="_blank" rel="noopener noreferrer" class="hover:scale-125 smooth">
                            @if (!empty($social['icon']))
                                <img src="{{ asset($social['icon']) }}" alt="{{ $social['key'] }}" class="size-6 shrink-0" />
                            @elseif ($social['key'] === 'telegram')
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M9.41718 15.1814L9.02018 20.7654C9.58818 20.7654 9.83418 20.5214 10.1292 20.2284L12.7922 17.6834L18.3102 21.7244C19.3222 22.2884 20.0352 21.9914 20.3082 20.7934L23.9302 3.82141L23.9312 3.82041C24.2522 2.32441 23.3902 1.73941 22.4042 2.10641L1.11418 10.2574C-0.338822 10.8214 -0.316822 11.6314 0.867178 11.9984L6.31018 13.6914L18.9532 5.78041C19.5482 5.38641 20.0892 5.60441 19.6442 5.99841L9.41718 15.1814Z" fill="#193760" /></svg>
                            @elseif ($social['key'] === 'vk')
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><g clip-path="url(#clip0_vk)"><path d="M1.68 1.68C3.17891e-07 3.376 0 6.088 0 11.52V12.48C0 17.904 3.17891e-07 20.616 1.68 22.32C3.376 24 6.088 24 11.52 24H12.48C17.904 24 20.616 24 22.32 22.32C24 20.624 24 17.912 24 12.48V11.52C24 6.096 24 3.384 22.32 1.68C20.624 3.17891e-07 17.912 0 12.48 0H11.52C6.096 0 3.384 3.17891e-07 1.68 1.68ZM4.048 7.304H6.8C6.888 11.88 8.904 13.816 10.504 14.216V7.304H13.088V11.248C14.664 11.08 16.328 9.28 16.888 7.296H19.464C19.2538 8.32296 18.8342 9.29556 18.2313 10.1531C17.6284 11.0106 16.8552 11.7347 15.96 12.28C16.9592 12.7772 17.8417 13.4806 18.5491 14.3438C19.2566 15.2071 19.7729 16.2105 20.064 17.288H17.224C16.616 15.392 15.096 13.92 13.088 13.72V17.288H12.768C7.296 17.288 4.176 13.544 4.048 7.304Z" fill="#193760" /></g><defs><clipPath id="clip0_vk"><rect width="24" height="24" fill="white" /></clipPath></defs></svg>
                            @else
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><g clip-path="url(#clip0_yt)"><path d="M23.4693 5.92901L23.4993 6.12501C23.2093 5.09601 22.4263 4.30201 21.4313 4.01301L21.4103 4.00801C19.5393 3.50001 12.0103 3.50001 12.0103 3.50001C12.0103 3.50001 4.50029 3.49001 2.61029 4.00801C1.59629 4.30201 0.812294 5.09601 0.527294 6.10401L0.522294 6.12501C-0.176706 9.77601 -0.181706 14.163 0.553294 18.072L0.522294 17.874C0.812294 18.903 1.59529 19.697 2.59029 19.986L2.61129 19.991C4.48029 20.5 12.0113 20.5 12.0113 20.5C12.0113 20.5 19.5203 20.5 21.4113 19.991C22.4263 19.697 23.2103 18.903 23.4953 17.895L23.5003 17.874C23.8183 16.176 24.0003 14.222 24.0003 12.226C24.0003 12.153 24.0003 12.079 23.9993 12.005C24.0003 11.937 24.0003 11.856 24.0003 11.775C24.0003 9.77801 23.8183 7.82401 23.4693 5.92901ZM9.60829 15.651V8.35801L15.8743 12.01L9.60829 15.651Z" fill="#193760" /></g><defs><clipPath id="clip0_yt"><rect width="24" height="24" fill="white" /></clipPath></defs></svg>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="text-sm 2xs:text_6 xs:text-center lg:text-left text-brand-gray">
            {{ $settings['footer']['copyright'] }} <br />
            {{ $settings['footer']['legal'] }}
        </div>
    </div>
</footer>
