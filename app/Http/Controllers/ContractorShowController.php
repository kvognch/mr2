<?php

namespace App\Http\Controllers;

use App\Models\Contractor;
use App\Models\ContractorReview;
use App\Models\ResourceType;
use App\Support\HomepageSettings;
use Illuminate\View\View;

class ContractorShowController extends Controller
{
    public function __invoke(string $slug): View
    {
        $contractor = Contractor::query()
            ->with(['categories', 'rating', 'territories', 'smrResourceTypes', 'pirResourceTypes'])
            ->where('slug', $slug)
            ->firstOrFail();

        $website = (string) ($contractor->website ?? '');
        $websiteText = $website !== '' ? preg_replace('#^https?://#', '', $website) : 'name.ru';
        $phone = (string) ($contractor->phone ?: '+7 (999) 999-99-99');
        $phoneHref = preg_replace('/\D+/', '', $phone);
        $email = (string) ($contractor->email ?: 'mail@name.ru');

        $categoriesText = $contractor->categories->pluck('name')->implode(', ');
        $territoriesText = $contractor->territories->pluck('name')->implode(', ');
        $ratingText = (string) ($contractor->rating?->name ?? 'ААА');
        $registrationDate = (string) ($contractor->registration_date?->format('d.m.Y') ?? '01.04.2025');

        $branchContacts = collect($contractor->branch_contacts ?? [])
            ->map(fn ($item) => is_array($item) ? ($item['value'] ?? null) : null)
            ->filter()
            ->implode(' ');

        $segmentBadges = collect($contractor->business_segments ?? [])
            ->map(function (string $segment): ?array {
                return match ($segment) {
                    'b2b' => ['code' => 'B2B', 'tooltip' => 'Бизнес для бизнеса'],
                    'b2c' => ['code' => 'B2C', 'tooltip' => 'Бизнес для потребителя'],
                    default => null,
                };
            })
            ->filter()
            ->values()
            ->all();

        if ($segmentBadges === []) {
            $segmentBadges = [
                ['code' => 'B2B', 'tooltip' => 'Бизнес для бизнеса'],
            ];
        }

        $settings = HomepageSettings::all();
        $settings['footer']['email'] = $email;
        $settings['footer']['phone'] = $phoneHref !== '' ? $phoneHref : '79999999999';
        $settings['footer']['phone_display'] = $phone;
        $settings['footer']['socials'] = [
            ['key' => 'telegram', 'url' => (string) ($contractor->social_telegram ?: '#'), 'icon' => 'assets/svgs/telegram.svg'],
            ['key' => 'vk', 'url' => (string) ($contractor->social_vk ?: '#'), 'icon' => 'assets/svgs/wk.svg'],
            ['key' => 'whatsapp', 'url' => (string) ($contractor->social_whatsapp ?: '#'), 'icon' => 'assets/svgs/whatsapp.svg'],
        ];

        $resourceOrder = ['ГС' => 1, 'НВ' => 2, 'НК' => 3, 'ТС' => 4, 'ЭС' => 5];

        $resourceColumns = ResourceType::query()
            ->get()
            ->sortBy(fn (ResourceType $resourceType): int => $resourceOrder[mb_strtoupper(trim((string) $resourceType->abbreviation))] ?? 999)
            ->values()
            ->map(fn (ResourceType $resourceType): array => [
                'abbreviation' => mb_strtoupper(trim((string) $resourceType->abbreviation)),
                'label' => $resourceType->name,
                'icon' => $resourceType->resolveIconUrl(true) ?? asset('assets/svgs/minus.svg'),
            ])
            ->all();

        if ($resourceColumns === []) {
            $resourceColumns = [
                ['abbreviation' => 'ГС', 'label' => 'Газ', 'icon' => asset('assets/svgs/gas-pipe-sm.svg')],
                ['abbreviation' => 'НВ', 'label' => 'Вода', 'icon' => asset('assets/svgs/water-sm.svg')],
                ['abbreviation' => 'НК', 'label' => 'Канализация', 'icon' => asset('assets/svgs/pipe-thin-sm.svg')],
                ['abbreviation' => 'ТС', 'label' => 'Отопление', 'icon' => asset('assets/svgs/heating-square-sm.svg')],
                ['abbreviation' => 'ЭС', 'label' => 'Электричество', 'icon' => asset('assets/svgs/electricity-sm.svg')],
            ];
        }

        $smrResourceAbbreviations = $contractor->smrResourceTypes
            ->pluck('abbreviation')
            ->map(fn ($value) => mb_strtoupper(trim((string) $value)))
            ->filter()
            ->values()
            ->all();

        $pirResourceAbbreviations = $contractor->pirResourceTypes
            ->pluck('abbreviation')
            ->map(fn ($value) => mb_strtoupper(trim((string) $value)))
            ->filter()
            ->values()
            ->all();

        return view('agent.show', [
            'contractor' => $contractor,
            'settings' => $settings,
            'segmentBadges' => $segmentBadges,
            'website' => $website !== '' ? $website : '#',
            'websiteText' => (string) ($websiteText ?: 'name.ru'),
            'phone' => $phone,
            'phoneHref' => $phoneHref !== '' ? $phoneHref : '79999999999',
            'email' => $email,
            'categoriesText' => $categoriesText !== '' ? $categoriesText : 'Подрядчик',
            'ratingText' => $ratingText,
            'territoriesText' => $territoriesText !== '' ? $territoriesText : 'Санкт-Петербург',
            'contractorTerritoryIds' => $contractor->territories->pluck('id')->map(fn ($id) => (int) $id)->values()->all(),
            'responseTimeText' => (string) ($contractor->response_time ?: '2 дня'),
            'workVolumeText' => (string) ($contractor->work_volume ?: 'от 1 млн'),
            'ogrnText' => (string) ($contractor->ogrn ?: '1184704017222'),
            'innText' => (string) ($contractor->inn ?: '4703159440'),
            'kppText' => (string) ($contractor->kpp ?: '470301001'),
            'registrationDateText' => $registrationDate,
            'legalAddressText' => (string) ($contractor->legal_address ?: 'Санкт-Петербург, Дворцовая площадь д. 1'),
            'branchContactsText' => (string) $branchContacts,
            'additionalInfoText' => (string) ($contractor->additional_info ?: 'Дополнительная информация о компании, режимы работы, условия оплаты, нюансы по территории работы компании, дублирующая информация по компаниям, важные моменты по работе, специфика работы, любая другая важная информация необходимая пользователю'),
            'socialTelegram' => (string) ($contractor->social_telegram ?: '#'),
            'socialVk' => (string) ($contractor->social_vk ?: '#'),
            'socialWhatsapp' => (string) ($contractor->social_whatsapp ?: '#'),
            'resourceColumns' => $resourceColumns,
            'smrResourceAbbreviations' => $smrResourceAbbreviations,
            'pirResourceAbbreviations' => $pirResourceAbbreviations,
            'contractorReviews' => ContractorReview::query()
                ->approved()
                ->where('contractor_id', $contractor->id)
                ->latest()
                ->get()
                ->map(fn (ContractorReview $review): array => [
                    'id' => $review->id,
                    'title' => $review->title,
                    'desc' => $review->body,
                    'author' => $review->author_name,
                    'authRole' => $review->author_role,
                    'date' => $review->created_at?->format('d.m.Y') ?? '',
                    'stars' => $review->rating,
                    'isRecommended' => $review->is_recommended,
                ])
                ->values()
                ->all(),
            'yandexMapsApiKey' => (string) env('YANDEX_MAPS_API_KEY', ''),
        ]);
    }
}
