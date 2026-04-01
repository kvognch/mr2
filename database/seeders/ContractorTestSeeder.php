<?php

namespace Database\Seeders;

use App\Models\Contractor;
use App\Models\ContractorCategory;
use App\Models\GeoUnit;
use App\Models\Rating;
use App\Models\ResourceType;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class ContractorTestSeeder extends Seeder
{
    public function run(): void
    {
        $owner = User::query()->orderBy('id')->first();

        if (! $owner) {
            $this->command?->warn('Пользователь-владелец не найден. Сидер остановлен.');

            return;
        }

        $categoryIds = ContractorCategory::query()->pluck('id')->all();
        $resourceTypeIds = ResourceType::query()->pluck('id')->all();
        $ratingIds = Rating::query()->pluck('id')->all();

        if ($categoryIds === [] || $resourceTypeIds === [] || $ratingIds === []) {
            $this->command?->warn('Справочники категорий/ресурсов/рейтингов пусты. Сначала заполните их.');

            return;
        }

        $spb = GeoUnit::query()->where('name', 'Санкт-Петербург')->first();

        if (! $spb) {
            $this->command?->warn('Геоэлемент "Санкт-Петербург" не найден.');

            return;
        }

        $spbDistrictIds = GeoUnit::query()
            ->where('parent_id', $spb->id)
            ->pluck('id')
            ->all();

        if ($spbDistrictIds === []) {
            $this->command?->warn('Не найдены дочерние районы у "Санкт-Петербург".');

            return;
        }

        $companyRoots = [
            'Альфа', 'Бета', 'Гамма', 'Дельта', 'Эпсилон', 'Дзета', 'Эта', 'Тета', 'Йота', 'Каппа',
            'Лямбда', 'Мю', 'Ню', 'Кси', 'Омикрон', 'Пи', 'Ро', 'Сигма', 'Тау', 'Ипсилон',
            'Фи', 'Хи', 'Пси', 'Омега', 'Орион', 'Вега', 'Атлас', 'Аврора', 'Неон', 'Гелиос',
        ];

        $streets = [
            'Невский проспект',
            'Лиговский проспект',
            'Московский проспект',
            'Литейный проспект',
            'проспект Энгельса',
            'улица Марата',
            'Кондратьевский проспект',
            'проспект Стачек',
            'Большой Сампсониевский проспект',
            'Садовая улица',
        ];

        foreach ($companyRoots as $index => $root) {
            $shortName = sprintf('ООО «%s»', $root);
            $fullName = sprintf('Общество с ограниченной ответственностью «%s»', $root);
            $slug = Str::slug(Str::transliterate($root));

            $segments = Arr::random([
                ['b2b'],
                ['b2c'],
                ['b2b', 'b2c'],
            ]);

            $contractor = Contractor::query()->updateOrCreate(
                ['short_name' => $shortName],
                [
                    'slug' => null,
                    'full_name' => $fullName,
                    'business_segments' => $segments,
                    'website' => sprintf('https://%s.example.com', $slug ?: ('company-' . ($index + 1))),
                    'social_telegram' => 'https://t.me',
                    'social_vk' => 'https://vk.com',
                    'social_whatsapp' => 'https://www.whatsapp.com',
                    'phone' => sprintf('+7 (%03d) %03d-%02d-%02d', rand(900, 999), rand(100, 999), rand(10, 99), rand(10, 99)),
                    'email' => sprintf('info+%s%d@example.com', $slug ?: 'company', $index + 1),
                    'response_time' => rand(1, 14) . ' дней',
                    'work_volume' => rand(10, 950) . ' млн.',
                    'smr_has_sro' => (bool) rand(0, 1),
                    'pir_has_sro' => (bool) rand(0, 1),
                    'ogrn' => (string) rand(1000000000000, 9999999999999),
                    'inn' => (string) rand(1000000000, 9999999999),
                    'kpp' => (string) rand(100000000, 999999999),
                    'registration_date' => now()->subDays(rand(400, 7500))->toDateString(),
                    'legal_address' => sprintf('г. Санкт-Петербург, %s, д. %d', Arr::random($streets), rand(1, 220)),
                    'branch_contacts' => [
                        ['value' => sprintf('г. Санкт-Петербург, %s, д. %d, +7 (%03d) %03d-%02d-%02d', Arr::random($streets), rand(1, 250), rand(900, 999), rand(100, 999), rand(10, 99), rand(10, 99))],
                        ['value' => sprintf('г. Санкт-Петербург, %s, д. %d, +7 (%03d) %03d-%02d-%02d', Arr::random($streets), rand(1, 250), rand(900, 999), rand(100, 999), rand(10, 99), rand(10, 99))],
                    ],
                    'additional_info' => 'Тестовая запись: выполнение работ по инженерной инфраструктуре и сопровождение проектов.',
                    'rating_id' => Arr::random($ratingIds),
                    'status' => 'approved',
                    'owner_id' => $owner->id,
                ]
            );

            $contractor->categories()->sync(Arr::random($categoryIds, rand(1, count($categoryIds))));
            $contractor->smrResourceTypes()->sync(Arr::random($resourceTypeIds, rand(1, min(3, count($resourceTypeIds)))));
            $contractor->pirResourceTypes()->sync(Arr::random($resourceTypeIds, rand(1, min(3, count($resourceTypeIds)))));
            $contractor->territories()->sync(Arr::random($spbDistrictIds, rand(2, min(6, count($spbDistrictIds)))));
        }

        $this->command?->info('Создано/обновлено 30 тестовых подрядчиков.');
    }
}
