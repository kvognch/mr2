<?php

namespace Database\Seeders;

use App\Enums\ReviewStatus;
use App\Enums\UserRole;
use App\Models\Contractor;
use App\Models\ContractorReview;
use App\Models\ServiceReview;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ReviewDemoSeeder extends Seeder
{
    public function run(): void
    {
        $serviceTemplates = [
            [
                'title' => 'Подключал газ',
                'body' => 'Долго искал организацию которая сможет выполнить весь комплекс услуг по подключению газа. Платформа помогла найти компетентных специалистов. Рекомендовал друзьям - остались довольны подбором нужной организации',
                'author_name' => 'Александр П.',
                'author_role' => 'Строю дом',
                'rating' => 5,
                'is_recommended' => true,
                'status' => ReviewStatus::Approved,
            ],
            [
                'title' => 'Ищу новых клиентов',
                'body' => 'Интересная площадка как для профессионалов, так и для тех кто ищет надежную компанию для строительства. После регистрации на платформе работы прибавилось. Также нашли компетентных подрядчиков',
                'author_name' => 'Евгений И.',
                'author_role' => 'Директор компании',
                'rating' => 5,
                'is_recommended' => true,
                'status' => ReviewStatus::Pending,
            ],
            [
                'title' => 'Нашел подрядчика',
                'body' => 'С помощью платформы быстро нашел квалифицированного подрядчика для выполнения работ по подключению инженерных сетей. В целом интересная, удобная и полезная платформа, но хотелось бы большей территории охвата',
                'author_name' => 'Сергей Г.',
                'author_role' => 'Строительная компания',
                'rating' => 4,
                'is_recommended' => true,
                'status' => ReviewStatus::Approved,
            ],
            [
                'title' => 'Подключение электричества',
                'body' => 'Оформил заявку на подключение электричества через платформу. Менеджер связался в тот же день, все документы подготовили быстро. Очень удобный сервис для частных застройщиков.',
                'author_name' => 'Дмитрий К.',
                'author_role' => 'Владелец участка',
                'rating' => 5,
                'is_recommended' => true,
                'status' => ReviewStatus::Pending,
            ],
            [
                'title' => 'Ремонт сетей',
                'body' => 'Искали подрядчика на ремонт инженерных сетей в коттеджном поселке. Платформа позволила сравнить несколько компаний и выбрать оптимальный вариант по срокам и цене.',
                'author_name' => 'Ольга М.',
                'author_role' => 'Управляющая компания',
                'rating' => 5,
                'is_recommended' => true,
                'status' => ReviewStatus::Approved,
            ],
            [
                'title' => 'Проектирование и монтаж',
                'body' => 'Заказывали полный цикл: проект и монтаж водоснабжения и канализации. Нашли исполнителя с хорошими отзывами. Работы выполнены в срок, претензий нет.',
                'author_name' => 'Андрей В.',
                'author_role' => 'Строю дом',
                'rating' => 5,
                'is_recommended' => true,
                'status' => ReviewStatus::Approved,
            ],
            [
                'title' => 'Консультация по подключениям',
                'body' => 'Получил подробную консультацию по порядку подключения к сетям. Понятно объяснили, какие документы нужны и в какой последовательности обращаться. Рекомендую новичкам.',
                'author_name' => 'Михаил С.',
                'author_role' => 'Частный заказчик',
                'rating' => 4,
                'is_recommended' => true,
                'status' => ReviewStatus::Approved,
            ],
        ];

        $contractorTemplates = [
            [
                'title' => 'Подключал газ',
                'body' => 'Долго искал организацию которая сможет выполнить весь комплекс услуг по подключению газа. Платформа помогла найти компетентных специалистов. Рекомендовал друзьям - остались довольны подбором нужной организации',
                'author_name' => 'Александр П.',
                'author_role' => 'Строю дом',
                'rating' => 5,
                'is_recommended' => true,
            ],
            [
                'title' => 'Ищу новых клиентов',
                'body' => 'Интересная площадка как для профессионалов, так и для тех кто ищет надежную компанию для строительства. После регистрации на платформе работы прибавилось. Также нашли компетентных подрядчиков',
                'author_name' => 'Евгений И.',
                'author_role' => 'Директор компании',
                'rating' => 5,
                'is_recommended' => true,
            ],
            [
                'title' => 'Нашел подрядчика',
                'body' => 'С помощью платформы быстро нашел квалифицированного подрядчика для выполнения работ по подключению инженерных сетей. В целом интересная, удобная и полезная платформа, но хотелось бы большей территории охвата',
                'author_name' => 'Сергей Г.',
                'author_role' => 'Строительная компания',
                'rating' => 3,
                'is_recommended' => false,
            ],
        ];

        if (ServiceReview::query()->count() === 0) {
            foreach ($serviceTemplates as $index => $template) {
                $user = $this->resolveUser($template['author_name'], 1 + $index);

                ServiceReview::query()->create([
                    'user_id' => $user->id,
                    'author_name' => $template['author_name'],
                    'author_role' => $template['author_role'],
                    'title' => $template['title'],
                    'body' => $template['body'],
                    'rating' => $template['rating'],
                    'is_recommended' => $template['is_recommended'],
                    'status' => $template['status']->value,
                    'created_at' => now()->subDays(30 - $index),
                    'updated_at' => now()->subDays(30 - $index),
                ]);
            }
        }

        if (ContractorReview::query()->count() === 0) {
            Contractor::query()
                ->orderBy('short_name')
                ->limit(5)
                ->get()
                ->each(function (Contractor $contractor, int $contractorIndex) use ($contractorTemplates): void {
                    foreach ($contractorTemplates as $templateIndex => $template) {
                        $user = $this->resolveUser($template['author_name'], 100 + $contractorIndex * 10 + $templateIndex);

                        ContractorReview::query()->create([
                            'contractor_id' => $contractor->id,
                            'user_id' => $user->id,
                            'author_name' => $template['author_name'],
                            'author_role' => $template['author_role'],
                            'title' => $template['title'],
                            'body' => $template['body'],
                            'rating' => $template['rating'],
                            'is_recommended' => $template['is_recommended'],
                            'status' => $templateIndex === 0 ? ReviewStatus::Pending->value : ReviewStatus::Approved->value,
                            'created_at' => now()->subDays(20 - $contractorIndex - $templateIndex),
                            'updated_at' => now()->subDays(20 - $contractorIndex - $templateIndex),
                        ]);
                    }
                });
        }
    }

    private function resolveUser(string $name, int $index): User
    {
        $slug = Str::slug(Str::transliterate($name));

        return User::query()->firstOrCreate(
            ['email' => sprintf('%s.review.%d@mrnr.local', $slug ?: 'reviewer', $index)],
            [
                'name' => $name,
                'phone' => sprintf('+7 (900) %03d-%02d-%02d', intdiv($index, 100) % 1000, intdiv($index, 10) % 10, $index % 10),
                'role' => UserRole::Client,
                'is_active' => true,
                'password' => 'Reviewer123!',
            ],
        );
    }
}
