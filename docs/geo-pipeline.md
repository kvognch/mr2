# Geo Pipeline (актуально)

## Текущая модель
Используется одна таблица `geo_units`:
- иерархия: `id`, `parent_id`, `source_id`, `parent_source_id`
- атрибуты: `name`, `admin_level`, `level`, `boundary`
- геометрии:
  - `geometry_osm` (исходные координаты OSM: `[lon, lat]`)
  - `geometry_yandex` (конвертированные для Яндекс: `[lat, lon]`)
- карта: бинарный статус `is_active` (`true/false`)

## Импорт из OSMB.geojson

### Базовый импорт (уровни 4-8)
```bash
php artisan geo:import-units-from-geojson OSMB.geojson \
  --source=osm \
  --admin-levels=4,5,6,7,8 \
  --boundary=administrative \
  --active=0 \
  --memory-limit=1024M
```

### Дозагрузка нового батча без изменения существующих записей
```bash
php artisan geo:import-units-from-geojson OSMB_batch2.geojson \
  --source=osm \
  --admin-levels=4,5,6,7,8 \
  --boundary=administrative \
  --active=0 \
  --append-only \
  --memory-limit=1024M
```

`--append-only`:
- вставляет только новые объекты,
- существующие не обновляет,
- их `is_active` и остальные поля не меняет.

## Управление активностью

### Активировать ветку
```bash
php artisan geo:set-status <unit_id> active --cascade
```

### Деактивировать ветку
```bash
php artisan geo:set-status <unit_id> inactive --cascade
```

## Админка Filament
- Раздел: `/admin` -> `Геоданные` -> `Геообъекты`
- Иерархический просмотр:
  - стартовый уровень: `admin_level = 4`
  - клик по названию: переход к дочерним
  - `Изменить`: редактирование записи
- Список:
  - колонки: `Название`, `Родитель`, `Статус`
  - поиск/сортировка: `Название`, `Родитель`
  - сортировка: `Статус`
  - пагинация по умолчанию: `50`

## Карта
- URL: `/geo-map`
- API: `/api/geo/map-features`
- На карту попадают только записи с `is_active = true` и непустой `geometry_yandex`.
