# Минимальный regression checklist после обновления до Laravel 12

## 1. Базовая проверка в DDEV

```bash
ddev artisan about
ddev artisan route:list
ddev artisan migrate:status
ddev artisan test
ddev npm run build
```

Если есть ошибка:

```bash
ddev exec tail -n 200 storage/logs/laravel.log
```

## 2. Публичные страницы

Проверить вручную:

1. `/`
2. `/search`
3. `/geo-map`
4. `/agent/{slug}`
5. `/agents/{slug}`

Что должно быть:

- страницы открываются без `500`
- Blade-шаблоны рендерятся без ошибок
- статические изображения и стили загружаются
- поиск и переходы по ссылкам работают

## 3. API

Проверить:

1. `GET /api/geo/tree`
2. `GET /api/geo/units/{id}`
3. `GET /api/geo/map-features`
4. `GET /api/user`

Что должно быть:

- `geo`-эндпоинты отвечают корректным JSON
- `/api/user` без авторизации возвращает ожидаемый отказ в доступе
- `/api/user` с валидной авторизацией работает через Sanctum

## 4. Filament admin

Проверить вручную:

1. `/admin/login`
2. вход в админку
3. списки ресурсов
4. формы создания и редактирования

Минимальный набор экранов:

1. contractors
2. contractor categories
3. resource types
4. ratings
5. geo units
6. homepage settings

Что должно быть:

- логин работает
- страницы открываются без Livewire/Filament exception
- таблицы грузятся
- сохранение форм проходит успешно
- flash/notification показывается корректно

## 5. Загрузка файлов

Проверить:

1. загрузку изображения в `HomepageContentSettings`
2. загрузку иконки в `ResourceTypeResource`

Файлы для smoke test:

1. `png`
2. `jpg`
3. `webp`
4. `svg`, если SVG реально должны поддерживаться в админке

Важно:

- в Laravel 12 проверка `image` больше не считает `svg` валидным изображением
- в проекте есть поля `FileUpload->image()` с `acceptedFileTypes([... 'image/svg+xml' ...])` в [app/Filament/Pages/HomepageContentSettings.php](/home/donnie/projects/mr2/app/Filament/Pages/HomepageContentSettings.php) и [app/Filament/Resources/ResourceTypeResource.php](/home/donnie/projects/mr2/app/Filament/Resources/ResourceTypeResource.php)
- если SVG нужен, может потребоваться отдельная правка валидации или конфигурации загрузки

## 6. База данных

Проверить:

1. `ddev artisan migrate:status`
2. отсутствие дублирующих миграций Sanctum
3. основные CRUD-операции в админке на PostgreSQL

Что должно быть:

- все миграции в ожидаемом состоянии
- нет конфликтов по `personal_access_tokens`
- insert/update/delete работают без SQL-ошибок

## 7. Что считать успешным завершением

Апгрейд можно считать успешным, если:

1. `ddev artisan test` проходит
2. `ddev npm run build` проходит
3. публичные страницы открываются
4. API работает
5. `/admin/login` и базовые CRUD-сценарии Filament работают
6. загрузка файлов не ломается
7. в `storage/logs/laravel.log` нет новых повторяемых exception после smoke test
