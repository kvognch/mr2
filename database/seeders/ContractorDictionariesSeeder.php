<?php

namespace Database\Seeders;

use App\Models\ContractorCategory;
use App\Models\Rating;
use App\Models\ResourceType;
use Illuminate\Database\Seeder;

class ContractorDictionariesSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            'Гарантирующий поставщик',
            'Ресурсо-снабжающая организация',
            'Подрядчик',
        ] as $name) {
            ContractorCategory::query()->updateOrCreate(['name' => $name]);
        }

        foreach ([
            ['name' => 'Газоснабжение', 'abbreviation' => 'ГС'],
            ['name' => 'Наружная канализация', 'abbreviation' => 'НК'],
            ['name' => 'Наружный водопровод', 'abbreviation' => 'НВ'],
            ['name' => 'Теплоснабжение', 'abbreviation' => 'ТС'],
            ['name' => 'Электроснабжение', 'abbreviation' => 'ЭС'],
        ] as $resourceType) {
            ResourceType::query()->updateOrCreate(
                ['name' => $resourceType['name']],
                ['abbreviation' => $resourceType['abbreviation']]
            );
        }

        foreach ([
            1 => 'ААА',
            2 => 'АА',
            3 => 'А',
            4 => 'ВВВ',
            5 => 'ВВ',
            6 => 'В',
            7 => 'ССС',
            8 => 'СС',
            9 => 'С',
            10 => 'Д',
        ] as $sortOrder => $name) {
            Rating::query()->updateOrCreate(
                ['name' => $name],
                ['sort_order' => $sortOrder]
            );
        }
    }
}
