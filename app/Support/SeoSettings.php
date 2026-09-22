<?php

namespace App\Support;

use App\Models\Setting;

final class SeoSettings
{
    public static function defaults(): array
    {
        return [
            'home' => [
                'title' => 'МНОГОРЕСУРСОВ',
                'description' => 'Поиск и подбор ресурсоснабжающих, сбытовых компаний и подрядчиков для выполнения подключений к инженерной инфраструктуре.',
            ],
            'search' => [
                'title' => 'Поиск организаций',
                'description' => 'Поиск подрядчиков и ресурсоснабжающих организаций',
            ],
            'tracking' => [
                'yandex_metrica' => '',
            ],
            'organizations' => [
                'description_prefix' => 'Подробная информация об организации',
                'description_suffix' => 'на платформе Многоресурсов.',
            ],
        ];
    }

    public static function all(): array
    {
        $settings = self::defaults();
        $stored = Setting::query()
            ->where('key', 'seo')
            ->pluck('value', 'key');

        $value = $stored->get('seo');
        $decoded = is_string($value) ? json_decode($value, true) : null;

        if (is_array($decoded)) {
            $settings = array_replace_recursive($settings, $decoded);
        }

        unset($settings['contractor']);
        unset($settings['tracking']['yandex_tag_manager']);

        return $settings;
    }

    public static function save(array $data): void
    {
        $payload = array_replace_recursive(self::all(), $data);
        unset($payload['contractor']);
        unset($payload['tracking']['yandex_tag_manager']);

        Setting::query()->updateOrCreate(
            ['key' => 'seo'],
            ['value' => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]
        );

        Setting::query()
            ->whereIn('key', ['homepage.meta', 'seo.organizations'])
            ->delete();
    }

    public static function organizationDefaults(): array
    {
        return self::defaults()['organizations'];
    }
}
