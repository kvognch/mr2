<?php

namespace App\Support;

use App\Models\Setting;

class HomepageSettings
{
    public static function defaults(): array
    {
        return [
            'meta' => [
                'home' => [
                    'title' => 'МНОГОРЕСУРСОВ',
                    'description' => 'Поиск и подбор ресурсоснабжающих, сбытовых компаний и подрядчиков для выполнения подключений к инженерной инфраструктуре.',
                ],
                'search' => [
                    'title' => 'Поиск организаций',
                    'description' => 'Поиск подрядчиков и ресурсоснабжающих организаций',
                ],
                'contractor' => [
                    'title' => '%name%',
                    'description' => '%name% — карточка подрядчика на платформе МНОГОРЕСУРСОВ.',
                ],
            ],
            'google_recaptcha' => [
                'site_key' => '6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI',
                'secret_key' => '6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe',
            ],
            'header' => [
                'brand' => 'МНОГОРЕСУРСОВ',
                'login_button_text_guest' => 'Вход / Регистрация',
                'login_button_text_auth' => 'Личный кабинет',
                'login_button_url' => '/dashboard',
                'menu' => [
                    ['label' => 'О платформе', 'url' => '/'],
                    ['label' => 'Присоединиться', 'url' => '/'],
                    ['label' => 'Помощь в подборе', 'url' => '#'],
                ],
            ],
            'hero' => [
                'title' => 'Поиск и подбор ресурсоснабжающих, сбытовых компаний и подрядчиков',
                'description' => 'для выполнения подключений к инженерной инфраструктуре по всем видам строительных работ, необходимых частным клиентам и профессионалам рынка',
                'primary_button_text' => 'Начать поиск',
                'primary_button_url' => '/search',
                'secondary_button_text_guest' => 'Присоединиться',
                'secondary_button_text_auth' => 'Личный кабинет',
                'secondary_button_url' => '#join-the-platform',
                'video_button_text' => 'Как пользоваться платформой',
                'video_embed_code' => '<!-- Вставьте embed-код видео -->',
                'stats' => [
                    ['value' => 'с 2018', 'description' => 'года работаем на рынке'],
                    ['value' => '400+', 'description' => 'организаций в реестре'],
                    ['value' => '1400 км2', 'description' => 'территория охвата'],
                ],
            ],
            'search' => [
                'title' => 'Поиск организации занимает много времени?',
                'description' => 'На платформе мы собрали полезную информацию об организациях по направлениям, чтобы Вы меньше времени тратили на поиски',
                'categories' => [
                    ['title' => 'Вода', 'icon' => 'homepage-icons/water.svg', 'alt' => 'Water'],
                    ['title' => 'Канализация', 'icon' => 'homepage-icons/pipe-thin.svg', 'alt' => 'Pipe Thin'],
                    ['title' => 'Отопление', 'icon' => 'homepage-icons/heating-square.svg', 'alt' => 'Heat'],
                    ['title' => 'Электричество', 'icon' => 'homepage-icons/electricity.svg', 'alt' => 'Electricity'],
                    ['title' => 'Газ', 'icon' => 'homepage-icons/gas-pipe.svg', 'alt' => 'Gas'],
                ],
            ],
            'be_pro' => [
                'title' => 'Будте профессионалом в своем деле − а мы поможем Вас найти',
                'description' => 'Используйте платформу для быстрого поиска надежных поставщиков или зарегистрируйте организацию, чтобы о вас узнало как можно больше потенциальных клиентов',
                'image' => 'homepage-images/be-pro.png',
                'image_alt' => 'Be Pro',
                'cards' => [
                    [
                        'title' => 'Экономия времени',
                        'description' => 'Не нужно искать информацию на разных сайтах, спрашивать у коллег и знакомых',
                        'icon' => 'homepage-icons/time.svg',
                    ],
                    [
                        'title' => 'Ниже риски',
                        'description' => 'Наши специалисты проверяют каждую компанию и присваивают рейтинг надежности',
                        'icon' => 'homepage-icons/graph-down.svg',
                    ],
                    [
                        'title' => 'Новые связи',
                        'description' => 'Целевая аудитория платформы частные клиенты и профессионалы рынка',
                        'icon' => 'homepage-icons/handshake-light.svg',
                    ],
                ],
            ],
            'join' => [
                'title' => 'Присоединятесь к платформе',
                'cta_button_text_guest' => 'Присоединиться',
                'cta_button_text_auth' => 'Личный кабинет',
                'cta_button_url' => '/dashboard',
                'steps' => [
                    [
                        'number' => '1',
                        'title' => 'Регистрация',
                        'description' => 'Предоставьте максимально точную и полную информацию об организации',
                    ],
                    [
                        'number' => '2',
                        'title' => 'Проверка',
                        'description' => 'Наши специалисты проверят информацию о вашей организации и внесут ее в реестр',
                    ],
                    [
                        'number' => '3',
                        'title' => 'Размещение',
                        'description' => 'Информация размещена на платформе, взаимодействуйте с другими пользователями',
                    ],
                ],
            ],
            'plans' => [
                'enabled' => true,
                'title' => 'Выберите подходящую услугу',
                'description' => 'Подключайте услуги и сокращайте время на поиск организации или эффективней привлекайте новых клиентов',
                'items' => [
                    [
                        'title' => 'Подбор организации',
                        'description' => 'Устали искать? Довертье дело нам. Подберем для Вас нужную компанию',
                        'price' => 'от 1 500 ₽ / подбор',
                        'button_text' => 'Оставить заявку',
                    ],
                    [
                        'title' => 'Подбор организации',
                        'description' => 'Устали искать? Довертье дело нам. Подберем для Вас нужную компанию',
                        'price' => 'от 1 500 ₽ / подбор',
                        'button_text' => 'Оставить заявку',
                    ],
                    [
                        'title' => 'Подбор организации',
                        'description' => 'Устали искать? Довертье дело нам. Подберем для Вас нужную компанию',
                        'price' => 'от 1 500 ₽ / подбор',
                        'button_text' => 'Оставить заявку',
                    ],
                ],
            ],
            'need_help' => [
                'title' => 'Нужна помощь в подборе подходящей компании?',
                'description' => 'Поручите задачу нам − наши специалисты подберут оптимальную сетевую организацию',
                'button_text' => 'Оставить заявку',
                'image' => 'homepage-images/phone.png',
                'image_alt' => 'Phone',
            ],
            'footer' => [
                'brand' => 'МНОГОРЕСУРСОВ',
                'group_1_title' => 'Сотрудничество',
                'group_1_links' => [
                    ['label' => 'О платформе', 'url' => '/'],
                    ['label' => 'Правовые вопросы', 'url' => '/'],
                    ['label' => 'Присоединиться', 'url' => '/'],
                ],
                'group_2_title' => 'Пользователям',
                'group_2_links' => [
                    ['label' => 'Услуги', 'url' => '/'],
                    ['label' => 'Помощь в подборе', 'url' => '#'],
                    ['label' => 'Блог', 'url' => '/'],
                ],
                'email' => 'muchresources@yandex.ru',
                'phone' => '+79219837215',
                'phone_display' => '+7 (921) 983-72-15',
                'socials' => [
                    ['key' => 'telegram', 'url' => '#'],
                    ['key' => 'vk', 'url' => '#'],
                    ['key' => 'youtube', 'url' => '#'],
                ],
                'copyright' => '© 2025 МногоРесурсов. Все права защищены',
                'legal' => 'Правообладатель ООО “МР”. Заявка № 2025756515',
            ],
        ];
    }

    public static function all(): array
    {
        $defaults = static::defaults();
        $settings = $defaults;

        $stored = Setting::query()
            ->where('key', 'like', 'homepage.%')
            ->pluck('value', 'key');

        foreach ($defaults as $block => $defaultValue) {
            $key = "homepage.{$block}";
            if (! isset($stored[$key])) {
                continue;
            }

            $decoded = json_decode((string) $stored[$key], true);
            if (! is_array($decoded)) {
                continue;
            }

            $settings[$block] = array_replace_recursive($defaultValue, $decoded);
        }

        if (
            isset($settings['header']['login_button_text'])
            && (($settings['header']['login_button_text_guest'] ?? null) === ($defaults['header']['login_button_text_guest'] ?? null))
        ) {
            $settings['header']['login_button_text_guest'] = $settings['header']['login_button_text'];
        }

        if (
            isset($settings['hero']['secondary_button_text'])
            && (($settings['hero']['secondary_button_text_guest'] ?? null) === ($defaults['hero']['secondary_button_text_guest'] ?? null))
        ) {
            $settings['hero']['secondary_button_text_guest'] = $settings['hero']['secondary_button_text'];
        }

        if (
            isset($settings['join']['cta_button_text'])
            && (($settings['join']['cta_button_text_guest'] ?? null) === ($defaults['join']['cta_button_text_guest'] ?? null))
        ) {
            $settings['join']['cta_button_text_guest'] = $settings['join']['cta_button_text'];
        }

        return $settings;
    }

    public static function save(array $data): void
    {
        $defaults = static::defaults();
        $current = static::all();
        $merged = array_replace_recursive($current, $data);

        foreach ($defaults as $block => $defaultValue) {
            $payload = $merged[$block] ?? $defaultValue;

            if (! is_array($payload)) {
                $payload = $defaultValue;
            }

            Setting::query()->updateOrCreate(
                ['key' => "homepage.{$block}"],
                ['value' => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]
            );
        }
    }

}
