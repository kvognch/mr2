<?php

namespace App\Console\Commands;

use App\Models\GeoUnit;
use App\Services\Geo\GeoJsonFeatureStreamReader;
use App\Services\Geo\GeometryService;
use App\Services\Geo\NameNormalizer;
use App\Services\Geo\YandexGeometryConverter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportGeoUnitsFromGeoJsonCommand extends Command
{
    protected $signature = 'geo:import-units-from-geojson
        {file : Relative path from project root to GeoJSON file}
        {--source=osm : Source key}
        {--admin-levels=4,5,6,7,8 : Comma-separated admin levels}
        {--boundary=administrative : Boundary filter}
        {--active=0 : Initial active status (0/1)}
        {--append-only : Insert only new records, never update existing ones}
        {--memory-limit=1024M : PHP memory limit for large GeoJSON}';

    protected $description = 'Import hierarchy + geometry from OSM Boundaries GeoJSON into single table';

    public function __construct(
        private readonly NameNormalizer $normalizer,
        private readonly GeoJsonFeatureStreamReader $featureReader,
        private readonly GeometryService $geometryService,
        private readonly YandexGeometryConverter $yandexGeometryConverter,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        ini_set('memory_limit', (string) $this->option('memory-limit'));

        $path = base_path((string) $this->argument('file'));
        if (! is_file($path)) {
            $this->error("File not found: {$path}");

            return self::FAILURE;
        }

        $source = (string) $this->option('source');
        $allowedAdminLevels = $this->parseCsvOption((string) $this->option('admin-levels'));
        $boundaryFilter = trim((string) $this->option('boundary'));
        $isActive = in_array((string) $this->option('active'), ['1', 'true', 'yes'], true);
        $appendOnly = (bool) $this->option('append-only');

        $allowedIds = $this->collectAllowedIds($path, $allowedAdminLevels, $boundaryFilter);
        $existingSourceIds = $appendOnly
            ? array_fill_keys(
                GeoUnit::query()
                    ->where('source', $source)
                    ->pluck('source_id')
                    ->map(fn ($v) => (string) $v)
                    ->all(),
                true
            )
            : [];

        $count = 0;
        $inserted = 0;
        $updated = 0;
        $skipped = 0;
        $insertedSourceIds = [];

        foreach ($this->featureReader->iterate($path) as $feature) {
            $geometry = $feature['geometry'] ?? null;
            if (! is_array($geometry) || ! in_array($geometry['type'] ?? '', ['Polygon', 'MultiPolygon'], true)) {
                continue;
            }

            $properties = is_array($feature['properties'] ?? null) ? $feature['properties'] : [];
            $name = trim((string) ($properties['name'] ?? $properties['NAME'] ?? ''));
            $adminLevel = trim((string) ($properties['admin_level'] ?? ''));
            $boundary = trim((string) ($properties['boundary'] ?? ''));
            $sourceId = (string) ($properties['osm_id'] ?? $feature['id'] ?? '');

            if ($name === '' || $adminLevel === '' || $sourceId === '' || ! isset($allowedIds[$sourceId])) {
                continue;
            }

            if ($appendOnly && isset($existingSourceIds[$sourceId])) {
                $skipped++;
                continue;
            }

            $parentSourceId = $this->extractParentSourceId(
                $properties['parents_administrative'] ?? $properties['parents'] ?? [],
                $allowedIds
            );

            $centroid = $this->geometryService->centroid($geometry);
            $bbox = $this->geometryService->bbox($geometry);

            $attributes = [
                'parent_source_id' => $parentSourceId,
                'name' => $name,
                'normalized_name' => $this->normalizer->normalize($name),
                'admin_level' => (int) $adminLevel,
                'level' => $this->mapUnitLevel((int) $adminLevel),
                'boundary' => $boundary !== '' ? $boundary : null,
                'geometry_osm' => $geometry,
                'geometry_yandex' => $this->yandexGeometryConverter->fromOsmGeometry($geometry),
                'center_lat' => $centroid['lat'] ?? null,
                'center_lon' => $centroid['lon'] ?? null,
                'bbox_min_lat' => $bbox['min_lat'] ?? null,
                'bbox_min_lon' => $bbox['min_lon'] ?? null,
                'bbox_max_lat' => $bbox['max_lat'] ?? null,
                'bbox_max_lon' => $bbox['max_lon'] ?? null,
                'is_active' => $isActive,
                'properties' => $properties,
                'meta' => ['importer' => 'geo:import-units-from-geojson'],
            ];

            if ($appendOnly) {
                GeoUnit::query()->create([
                    'source' => $source,
                    'source_id' => $sourceId,
                    ...$attributes,
                ]);
                $inserted++;
                $insertedSourceIds[] = $sourceId;
                $existingSourceIds[$sourceId] = true;
            } else {
                $model = GeoUnit::query()->updateOrCreate(
                    [
                        'source' => $source,
                        'source_id' => $sourceId,
                    ],
                    $attributes
                );

                if ($model->wasRecentlyCreated) {
                    $inserted++;
                    $insertedSourceIds[] = $sourceId;
                } else {
                    $updated++;
                }
            }

            $count++;
        }

        $resolved = $appendOnly
            ? $this->resolveParentsForInserted($source, $insertedSourceIds)
            : $this->resolveParents($source);

        $this->info("Processed {$count}; inserted {$inserted}; updated {$updated}; skipped {$skipped}; parent links resolved: {$resolved}");

        return self::SUCCESS;
    }

    private function extractParentSourceId(mixed $parents, array $allowedIds): ?string
    {
        if (! is_array($parents) || $parents === []) {
            return null;
        }

        foreach ($parents as $candidate) {
            $candidateId = trim((string) $candidate);
            if ($candidateId !== '' && isset($allowedIds[$candidateId])) {
                return $candidateId;
            }
        }

        return null;
    }

    private function collectAllowedIds(string $path, array $allowedAdminLevels, string $boundaryFilter): array
    {
        $ids = [];

        foreach ($this->featureReader->iterate($path) as $feature) {
            $properties = is_array($feature['properties'] ?? null) ? $feature['properties'] : [];
            $name = trim((string) ($properties['name'] ?? $properties['NAME'] ?? ''));
            $adminLevel = trim((string) ($properties['admin_level'] ?? ''));
            $boundary = trim((string) ($properties['boundary'] ?? ''));

            if ($name === '' || $adminLevel === '') {
                continue;
            }
            if ($allowedAdminLevels !== [] && ! in_array($adminLevel, $allowedAdminLevels, true)) {
                continue;
            }
            if ($boundaryFilter !== '' && $boundary !== '' && $boundary !== $boundaryFilter) {
                continue;
            }

            $sourceId = (string) ($properties['osm_id'] ?? $feature['id'] ?? '');
            if ($sourceId !== '') {
                $ids[$sourceId] = true;
            }
        }

        return $ids;
    }

    private function mapUnitLevel(int $adminLevel): string
    {
        return match ($adminLevel) {
            4 => 'Субъект',
            5 => 'Макрорегион',
            6 => 'Район',
            7 => 'Территория',
            8 => 'Населенный пункт',
            default => 'Другое',
        };
    }

    private function resolveParents(string $source): int
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'pgsql') {
            return DB::affectingStatement(
                'UPDATE geo_units AS child
                 SET parent_id = parent.id
                 FROM geo_units AS parent
                 WHERE child.source = ?
                   AND parent.source = ?
                   AND child.parent_source_id IS NOT NULL
                   AND child.parent_source_id = parent.source_id
                   AND (child.parent_id IS NULL OR child.parent_id <> parent.id)',
                [$source, $source]
            );
        }

        if ($driver === 'mysql') {
            return DB::affectingStatement(
                'UPDATE geo_units child
                 JOIN geo_units parent
                   ON parent.source = child.source
                  AND parent.source_id = child.parent_source_id
                 SET child.parent_id = parent.id
                 WHERE child.source = ?
                   AND child.parent_source_id IS NOT NULL
                   AND (child.parent_id IS NULL OR child.parent_id <> parent.id)',
                [$source]
            );
        }

        return 0;
    }

    private function parseCsvOption(string $value): array
    {
        $parts = array_filter(array_map(static fn (string $item): string => trim($item), explode(',', $value)));

        return array_values(array_unique($parts));
    }

    private function resolveParentsForInserted(string $source, array $sourceIds): int
    {
        if ($sourceIds === []) {
            return 0;
        }

        $updated = 0;

        GeoUnit::query()
            ->where('source', $source)
            ->whereIn('source_id', $sourceIds)
            ->whereNotNull('parent_source_id')
            ->chunkById(500, function ($chunk) use (&$updated, $source): void {
                foreach ($chunk as $unit) {
                    $parent = GeoUnit::query()
                        ->where('source', $source)
                        ->where('source_id', $unit->parent_source_id)
                        ->first();

                    if ($parent && $unit->parent_id !== $parent->id) {
                        $unit->parent_id = $parent->id;
                        $unit->save();
                        $updated++;
                    }
                }
            });

        return $updated;
    }
}
