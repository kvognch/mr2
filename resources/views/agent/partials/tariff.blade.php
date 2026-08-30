<div class="grid 2xs:grid-cols-2 xs:gap-5 md:gap-10">
  <p class="text-sm xs:text_1 text-brand-gray-dark">
    {{ $label }}
  </p>
  @if ($currentTariff)
    <a
      href="{{ $currentTariff->url }}"
      target="_blank"
      rel="noopener noreferrer"
      class="min-w-0 break-all text-xs 2xs:text-sm sm:text_2 text-brand-blue hover:underline underline-offset-1"
    >
      {{ $currentTariff->original_name }}
    </a>
  @else
    <p class="text-xs 2xs:text-sm sm:text_2 text-brand-dark">
      Не загружен
    </p>
  @endif
</div>
@if ($tariffHistory->isNotEmpty())
  <div class="grid 2xs:grid-cols-2 xs:gap-5 md:gap-10">
    <p class="text-sm xs:text_1 text-brand-gray-dark">
      {{ $historyLabel }}
    </p>
    <div class="space-y-2">
      @foreach ($tariffHistory as $tariff)
        <p>
          <a
            href="{{ $tariff->url }}"
            target="_blank"
            rel="noopener noreferrer"
            class="min-w-0 break-all text-xs 2xs:text-sm sm:text_2 text-brand-blue hover:underline underline-offset-1"
          >
            {{ $tariff->original_name }}
          </a>
        </p>
      @endforeach
    </div>
  </div>
@endif
