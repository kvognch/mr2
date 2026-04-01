<?php

namespace App\Console\Commands;

use App\Models\GeoUnit;
use App\Services\Geo\NameNormalizer;
use Illuminate\Console\Command;

class ImportGeoUnitsCommand extends Command
{
    protected $signature = 'geo:import-units
        {file : Relative path from project root to CSV file}
        {--delimiter=, : CSV delimiter}
        {--source=osm : Source key}
        {--active=0 : Initial active status (0/1)}';

    protected $description = 'Import hierarchy data from CSV into single geo_units table';

    public function __construct(private readonly NameNormalizer $normalizer)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $path = base_path((string) $this->argument('file'));
        if (! is_file($path)) {
            $this->error("File not found: {$path}");

            return self::FAILURE;
        }

        $delimiter = (string) $this->option('delimiter');
        $source = (string) $this->option('source');
        $isActive = in_array((string) $this->option('active'), ['1', 'true', 'yes'], true);

        $handle = fopen($path, 'rb');
        if (! $handle) {
            $this->error('Unable to open CSV file.');

            return self::FAILURE;
        }

        $header = fgetcsv($handle, 0, $delimiter);
        if (! is_array($header)) {
            fclose($handle);
            $this->error('Invalid CSV header.');

            return self::FAILURE;
        }

        $header = array_map('trim', $header);
        $count = 0;

        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            if (! is_array($row) || $row === []) {
                continue;
            }

            $data = array_combine($header, $row);
            if (! is_array($data)) {
                continue;
            }

            $sourceId = trim((string) ($data['external_id'] ?? $data['source_id'] ?? ''));
            $name = trim((string) ($data['name'] ?? ''));
            if ($sourceId === '' || $name === '') {
                continue;
            }

            GeoUnit::query()->updateOrCreate(
                [
                    'source' => $source,
                    'source_id' => $sourceId,
                ],
                [
                    'parent_source_id' => $this->nullable($data['parent_source_id'] ?? $data['parent_external_id'] ?? null),
                    'name' => $name,
                    'normalized_name' => $this->normalizer->normalize($name),
                    'admin_level' => $this->nullableInt($data['admin_level'] ?? null),
                    'level' => trim((string) ($data['level'] ?? 'Другое')),
                    'boundary' => $this->nullable($data['boundary'] ?? null),
                    'is_active' => $isActive,
                    'meta' => ['imported_from' => $path],
                ]
            );

            $count++;
        }

        fclose($handle);

        $this->info("Imported/updated {$count} rows.");

        return self::SUCCESS;
    }

    private function nullable(mixed $value): ?string
    {
        $value = trim((string) ($value ?? ''));

        return $value === '' ? null : $value;
    }

    private function nullableInt(mixed $value): ?int
    {
        $value = trim((string) ($value ?? ''));

        return $value === '' ? null : (int) $value;
    }
}
