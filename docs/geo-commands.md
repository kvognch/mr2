# Команды Geo

## Список команд

- `geo:import-units-from-geojson`
  Импортирует иерархию и геометрию из `GeoJSON` (OSM Boundaries) в таблицу `geo_units`.
  Сохраняет обе версии геометрии:
  - `geometry_osm` (`[lon, lat]`)
  - `geometry_yandex` (`[lat, lon]`)

- `geo:import-units`
  Импортирует иерархию из `CSV` в `geo_units` (служебная команда).

- `geo:set-status`
  Меняет бинарный статус отображения на карте:
  - `active` -> `is_active=true`
  - `inactive` -> `is_active=false`
  Можно применять рекурсивно (`--cascade`).

- `geo:import-polygons`
  Устаревшая команда (deprecated). Геометрия теперь импортируется через `geo:import-units-from-geojson`.

## Частые сценарии

### 1) Первый импорт батча OSMB (уровни 4-8)
```bash
php artisan geo:import-units-from-geojson OSMB.geojson \
  --source=osm \
  --admin-levels=4,5,6,7,8 \
  --boundary=administrative \
  --active=0 \
  --memory-limit=1024M
```

### 2) Дозагрузка нового батча без изменения существующих записей
```bash
php artisan geo:import-units-from-geojson OSMB_batch2.geojson \
  --source=osm \
  --admin-levels=4,5,6,7,8 \
  --boundary=administrative \
  --active=0 \
  --append-only \
  --memory-limit=1024M
```

### 3) Активировать ветку от конкретного узла
```bash
php artisan geo:set-status 12345 active --cascade
```

### 4) Деактивировать ветку от конкретного узла
```bash
php artisan geo:set-status 12345 inactive --cascade
```

### 5) Массовый импорт из CSV (служебный)
```bash
php artisan geo:import-units storage/app/imports/geo_units.csv \
  --source=osm \
  --delimiter=, \
  --active=0
```

## Рекомендации
- Для новых непересекающихся батчей используйте `--append-only`.
- Для больших файлов используйте `--memory-limit=1024M` (или выше).
- После импорта включайте только нужные ветки через `geo:set-status ... active --cascade`.
