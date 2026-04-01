# Туториал: обновление Laravel в этом проекте

## 1. Что есть сейчас

По текущему состоянию репозитория:

- DDEV-конфиг: `.ddev/config.yaml`
- PHP в DDEV: `8.3`
- База в DDEV: `PostgreSQL 16`
- PHP в `composer.json`: `^8.1`
- Laravel: `laravel/framework ^10.10`
- Установленная версия Laravel в `composer.lock`: `10.50.2`
- Sanctum: `^3.3` / `3.3.3`
- Filament: `^3.3` / `3.3.49`
- Livewire: `3.7.12`
- PHPUnit: `^10.1` / `10.5.63`
- Collision: `^7.0` / `7.12.0`

Особенности именно этого проекта:

- есть Filament-админка через [app/Providers/Filament/AdminPanelProvider.php](/home/donnie/projects/mr2/app/Providers/Filament/AdminPanelProvider.php)
- используется Sanctum в [routes/api.php](/home/donnie/projects/mr2/routes/api.php)
- тестов почти нет: фактически только примеры в [tests/Feature/ExampleTest.php](/home/donnie/projects/mr2/tests/Feature/ExampleTest.php)

Это значит, что основной риск апгрейда здесь не в самом ядре Laravel, а в связке `Laravel + Sanctum + Filament + ручная проверка админки`.

Важно: для этого проекта команды нужно выполнять **через DDEV**, а не через локальные `php`, `composer`, `npm`.

## 2. До какой версии обновлять

На дату `2026-03-28` разумная цель для этого проекта: **Laravel 12**.

Почему не останавливаться на Laravel 11:

- по официальной support policy Laravel 11 получает security fixes только до `2026-03-12`
- Laravel 12 получает security fixes до `2027-02-24`

Практический вывод:

- если обновление делается сейчас, лучше идти сразу `10 -> 12`
- промежуточно полезно **прочитать** upgrade guide `10 -> 11`, а затем `11 -> 12`
- сам код можно обновлять за один проход через Composer, но проверять изменения лучше в логике двух этапов

## 3. Что важно по совместимости

### Laravel 12 потребует

- PHP `^8.2`
- `laravel/framework ^12.0`
- `phpunit/phpunit ^11.x`

### Для этого проекта дополнительно нужно

- обновить `laravel/sanctum` до `^4.0`, потому что текущий `3.3.3` поддерживает только Laravel 9/10
- обновить `nunomaduro/collision` до `^8.x`, потому что ветка `7.x` не рассчитана на Laravel 12

### Что уже выглядит совместимым

- `filament/filament 3.3.49` в lock-файле уже декларирует поддержку `illuminate/* ^10.45|^11.0|^12.0`
- `livewire/livewire 3.7.12` также декларирует поддержку Laravel 12

Итог: для этого репозитория главным блокером выглядит не Filament, а старые версии `PHP`, `Sanctum`, `Collision` и `PHPUnit`.

## 4. Перед началом

Сделай отдельную ветку:

```bash
git checkout -b chore/upgrade-laravel-12
```

Проверь окружение:

```bash
ddev start
ddev php -v
ddev composer --version
ddev npm -v
```

Минимум, который нужен для апгрейда:

- DDEV-проект в рабочем состоянии
- PHP 8.2+ внутри контейнера
- рабочий Composer
- возможность поднять локальную БД и прогнать миграции

Сразу зафиксируй текущее состояние:

```bash
git status
ddev composer show laravel/framework laravel/sanctum filament/filament livewire/livewire nunomaduro/collision phpunit/phpunit
```

## 5. Обязательное чтение перед изменениями

Прочитай официальные материалы:

1. Laravel 11 upgrade guide: https://laravel.com/docs/11.x/upgrade
2. Laravel 12 upgrade guide: https://laravel.com/docs/12.x/upgrade
3. Laravel release notes / support policy: https://laravel.com/docs/releases

Для этого проекта особенно важны разделы:

- `Updating Dependencies`
- `Updating Sanctum`
- `Application Structure`
- `Carbon 3`
- `Per-Second Rate Limiting`
- `Image Validation Now Excludes SVGs`
- `Local Filesystem Disk Default Root Path`

## 6. Какие изменения внести в composer.json

В [composer.json](/home/donnie/projects/mr2/composer.json) обнови версии примерно так:

```json
{
  "require": {
    "php": "^8.2",
    "filament/filament": "^3.3",
    "guzzlehttp/guzzle": "^7.2",
    "laravel/framework": "^12.0",
    "laravel/sanctum": "^4.0",
    "laravel/tinker": "^2.10"
  },
  "require-dev": {
    "fakerphp/faker": "^1.23",
    "laravel/pint": "^1.24",
    "laravel/sail": "^1.41",
    "mockery/mockery": "^1.6",
    "nunomaduro/collision": "^8.6",
    "phpunit/phpunit": "^11.5",
    "spatie/laravel-ignition": "^2.0"
  }
}
```

Примечания:

- `filament/filament` можно пока оставить на `^3.3`, затем Composer подтянет актуальный совместимый `3.3.x`
- `laravel/tinker`, `pint`, `sail`, `faker`, `mockery` лучше подтянуть ближе к версиям актуального skeleton Laravel 12
- структуру приложения под новый skeleton переносить **не нужно**; Laravel 11/12 поддерживает старую структуру Laravel 10

## 7. Как выполнять обновление

### Вариант A: сразу на Laravel 12

Самый практичный путь для этого проекта:

```bash
ddev composer require laravel/framework:^12.0 laravel/sanctum:^4.0 laravel/tinker:^2.10 --no-update
ddev composer require --dev nunomaduro/collision:^8.6 phpunit/phpunit:^11.5 fakerphp/faker:^1.23 laravel/pint:^1.24 laravel/sail:^1.41 mockery/mockery:^1.6 --no-update
ddev composer update
```

После этого:

```bash
ddev artisan optimize:clear
ddev artisan filament:upgrade
ddev artisan vendor:publish --tag=laravel-assets --force
```

Если Sanctum попросит публикацию миграций:

```bash
ddev artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
```

Смотри, не создает ли команда дубликаты относительно [database/migrations/2019_12_14_000001_create_personal_access_tokens_table.php](/home/donnie/projects/mr2/database/migrations/2019_12_14_000001_create_personal_access_tokens_table.php). Если создает, вторую миграцию оставлять нельзя.

### Вариант B: через промежуточную точку Laravel 11

Подходит, если `composer update` на Laravel 12 упрется в конфликты:

1. Обновить `PHP -> 8.2`, `laravel/framework -> ^11.0`, `laravel/sanctum -> ^4.0`, `collision -> ^8.1`
2. Добиться зеленого состояния на Laravel 11
3. Затем поднять `laravel/framework -> ^12.0` и `phpunit -> ^11.5`

Этот вариант медленнее, но проще отлаживать.

## 8. Что проверить в коде после composer update

### 8.1. Sanctum

Проверить:

- [routes/api.php](/home/donnie/projects/mr2/routes/api.php) с `auth:sanctum`
- [app/Models/User.php](/home/donnie/projects/mr2/app/Models/User.php), где используется `HasApiTokens`
- [config/sanctum.php](/home/donnie/projects/mr2/config/sanctum.php)

Что смотреть:

- не изменились ли middleware Sanctum
- не появились ли новые обязательные миграции
- корректно ли отрабатывает `/api/user` для авторизованного пользователя

### 8.2. Filament

Проверить:

- [app/Providers/Filament/AdminPanelProvider.php](/home/donnie/projects/mr2/app/Providers/Filament/AdminPanelProvider.php)
- все ресурсы в `app/Filament/**`
- кастомный компонент [app/Filament/Forms/Components/TerritoryTreeSelect.php](/home/donnie/projects/mr2/app/Filament/Forms/Components/TerritoryTreeSelect.php)
- кастомные Blade-шаблоны в `resources/views/filament/**`

Что смотреть:

- открывается ли `/admin/login`
- работает ли логин
- открываются ли списки/формы ресурсов
- работают ли кастомные поля, загрузки файлов и сохранение записей

### 8.3. Laravel 11/12 low-impact changes

Проверить проект на следующие паттерны:

- ручное создание `Limit` или `GlobalLimit`
- кастомные реализации mail/database contracts
- использование `Storage::disk('local')`, если путь к файлам важен
- валидацию `image`, если где-то ожидаются SVG
- кастомную работу с UUID, если позже появится `HasUuids`

По текущему коду repo это не выглядит как основной риск, но проверить нужно.

## 9. Команды для точечной проверки после апгрейда

```bash
ddev artisan --version
ddev artisan about
ddev artisan test
ddev artisan route:list
ddev artisan config:clear
ddev artisan cache:clear
ddev artisan view:clear
ddev artisan event:clear
ddev npm install
ddev npm run build
```

Если есть локальная БД:

```bash
ddev artisan migrate --pretend
ddev artisan migrate
```

Если в проекте есть сиды для демонстрации:

```bash
ddev artisan db:seed
```

## 10. Что проверить руками в браузере

Из-за слабого тестового покрытия это обязательный этап.

Проверь:

1. Главную страницу `/`
2. Поиск `/search`
3. Гео-страницу `/geo/...`, если маршрут используется
4. API:
   - `/api/geo/tree`
   - `/api/geo/units/{id}`
   - `/api/geo/map-features`
5. Админку `/admin/login`
6. Создание и редактирование:
   - contractors
   - contractor categories
   - resource types
   - ratings
   - geo units
7. Загрузку файлов в Filament-формах
8. Авторизацию пользователя и `/api/user`

## 11. Типовые проблемы именно для этого проекта

### Проблема: Composer не может разрешить зависимости

Обычно причина одна из этих:

- локально все еще PHP 8.1
- забыли поднять `laravel/sanctum` до `^4.0`
- остался `nunomaduro/collision ^7`
- lock-файл удерживает старые версии

Диагностика:

```bash
ddev composer why-not laravel/framework ^12.0
ddev composer why-not laravel/sanctum ^4.0
```

### Проблема: сломалась админка Filament

Что делать:

```bash
ddev artisan optimize:clear
ddev artisan filament:upgrade
ddev artisan vendor:publish --tag=laravel-assets --force
```

Потом проверить браузером `admin`-маршруты и ошибки в логах:

```bash
ddev exec tail -f storage/logs/laravel.log
```

### Проблема: конфликты миграций Sanctum

В этом репозитории миграция personal access tokens уже есть. После публикации миграций нужно убедиться, что не появилось дублирования одной и той же таблицы.

## 12. Что я бы сделал в этом проекте на практике

Рекомендуемый маршрут:

1. Убедиться, что DDEV поднят и контейнер работает на PHP 8.2+
2. Обновить `composer.json` сразу под Laravel 12
3. Выполнить `ddev composer update`
4. Очистить кэши и прогнать `ddev artisan filament:upgrade`
5. Прогнать `ddev artisan test` и `ddev npm run build`
6. Ручно проверить `/`, `/search`, `/api/geo/*`, `/admin/login`, CRUD в Filament
7. Если есть ошибки, сначала чинить Sanctum и Filament, а уже потом косметически синхронизировать skeleton-файлы

## 13. Что не нужно делать

- не нужно переносить проект на новый Laravel 11/12 skeleton
- не нужно массово переписывать `config/*`, если нет конкретной причины
- не нужно публиковать все vendor-ресурсы подряд
- не нужно удалять старые миграции без понимания, используются ли они в текущей БД

## 14. Итог

Для этого проекта апгрейд до Laravel 12 выглядит реалистичным.

Ключевые изменения:

- PHP `8.1 -> 8.2+`
- Laravel `10 -> 12`
- Sanctum `3 -> 4`
- Collision `7 -> 8`
- PHPUnit `10 -> 11`

Наиболее рискованная часть не framework сам по себе, а ручная проверка Filament-админки и Sanctum-аутентификации после обновления.
