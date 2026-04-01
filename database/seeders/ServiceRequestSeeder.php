<?php

namespace Database\Seeders;

use App\Enums\ServiceRequestStatus;
use App\Models\ServiceRequest;
use Illuminate\Database\Seeder;

class ServiceRequestSeeder extends Seeder
{
    public function run(): void
    {
        $pendingNames = ['Иван', 'Пётр', 'Николай', 'Алексей', 'Сергей'];
        $processedNames = ['Дмитрий', 'Андрей', 'Михаил', 'Владимир', 'Евгений'];
        $comment = 'Ищу подрядчика на ремонт электросетей в коттеджном посёлке';
        $sourceUrl = 'https://многоресурсов.рф/';

        ServiceRequest::query()->delete();

        foreach ($pendingNames as $index => $name) {
            $i = $index + 1;

            ServiceRequest::query()->create([
                'name' => $name,
                'phone' => "+7999000000{$i}",
                'comment' => $comment,
                'status' => ServiceRequestStatus::Pending,
                'admin_note' => null,
                'source_url' => $sourceUrl,
                'created_at' => now()->subHours(10 - $i),
                'updated_at' => now()->subHours(10 - $i),
            ]);
        }

        foreach ($processedNames as $index => $name) {
            $i = $index + 1;

            ServiceRequest::query()->create([
                'name' => $name,
                'phone' => "+7888000000{$i}",
                'comment' => $comment,
                'status' => ServiceRequestStatus::Processed,
                'admin_note' => "Примечание менеджера для обработанной заявки #{$i}",
                'source_url' => $sourceUrl,
                'created_at' => now()->subDays($i),
                'updated_at' => now()->subDays($i),
            ]);
        }
    }
}
