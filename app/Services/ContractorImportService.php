<?php

namespace App\Services;

use App\Imports\ContractorsSheetImport;
use App\Models\Contractor;
use App\Models\ContractorCategory;
use App\Models\GeoUnit;
use App\Models\Rating;
use App\Models\ResourceType;
use App\Support\ContractorSpreadsheet;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class ContractorImportService
{
    public function importFromLocalPath(string $path): array
    {
        $sheet = Excel::toCollection(new ContractorsSheetImport(), storage_path('app/' . ltrim($path, '/')))->first();

        if ($sheet === null || $sheet->isEmpty()) {
            return ['created' => 0, 'updated' => 0, 'skipped' => 0, 'errors' => 0];
        }

        $rows = $sheet->values();
        $headerRow = $rows->shift();

        if ($headerRow === null) {
            return ['created' => 0, 'updated' => 0, 'skipped' => 0, 'errors' => 0];
        }

        $headers = collect($headerRow)
            ->map(fn ($heading): string => trim((string) $heading))
            ->values()
            ->all();

        $headerIndexes = collect($headers)
            ->mapWithKeys(fn (string $heading, int $index): array => [$heading => $index])
            ->all();

        $stats = ['created' => 0, 'updated' => 0, 'skipped' => 0, 'errors' => 0];

        $categoryMap = $this->buildLookupMap(ContractorCategory::query()->pluck('id', 'name')->all());
        $territoryMap = $this->buildLookupMap(GeoUnit::query()->pluck('id', 'name')->all());
        $resourceTypeMap = $this->buildLookupMap(ResourceType::query()->pluck('id', 'name')->all());
        $ratingMap = $this->buildLookupMap(Rating::query()->pluck('id', 'name')->all());

        foreach ($rows as $row) {
            $row = collect($row)->values()->all();
            $mappedRow = $this->mapRowByHeadings($row, $headerIndexes);

            if ($this->isRowEmpty($mappedRow)) {
                $stats['skipped']++;

                continue;
            }

            try {
                DB::transaction(function () use ($mappedRow, $headerIndexes, $categoryMap, $territoryMap, $resourceTypeMap, $ratingMap, &$stats): void {
                    $id = $this->parseInteger($mappedRow[ContractorSpreadsheet::COLUMN_ID] ?? null);
                    $contractor = $id ? Contractor::query()->find($id) : null;
                    $isExisting = $contractor !== null;

                    if (! $contractor) {
                        $contractor = new Contractor();
                    }

                    $attributes = [];

                    foreach ([
                        ContractorSpreadsheet::COLUMN_SHORT_NAME => 'short_name',
                        ContractorSpreadsheet::COLUMN_FULL_NAME => 'full_name',
                        ContractorSpreadsheet::COLUMN_WEBSITE => 'website',
                        ContractorSpreadsheet::COLUMN_SOCIAL_TELEGRAM => 'social_telegram',
                        ContractorSpreadsheet::COLUMN_SOCIAL_VK => 'social_vk',
                        ContractorSpreadsheet::COLUMN_SOCIAL_WHATSAPP => 'social_whatsapp',
                        ContractorSpreadsheet::COLUMN_PHONE => 'phone',
                        ContractorSpreadsheet::COLUMN_EMAIL => 'email',
                        ContractorSpreadsheet::COLUMN_RESPONSE_TIME => 'response_time',
                        ContractorSpreadsheet::COLUMN_WORK_VOLUME => 'work_volume',
                        ContractorSpreadsheet::COLUMN_OGRN => 'ogrn',
                        ContractorSpreadsheet::COLUMN_INN => 'inn',
                        ContractorSpreadsheet::COLUMN_KPP => 'kpp',
                        ContractorSpreadsheet::COLUMN_LEGAL_ADDRESS => 'legal_address',
                        ContractorSpreadsheet::COLUMN_ADDITIONAL_INFO => 'additional_info',
                    ] as $heading => $attribute) {
                        if ($this->hasHeading($headerIndexes, $heading)) {
                            $attributes[$attribute] = $this->parseNullableString($mappedRow[$heading] ?? null);
                        }
                    }

                    if ($this->hasHeading($headerIndexes, ContractorSpreadsheet::COLUMN_SHORT_NAME)) {
                        $attributes['short_name'] ??= '';
                    }

                    if ($this->hasHeading($headerIndexes, ContractorSpreadsheet::COLUMN_BUSINESS_SEGMENTS)) {
                        $attributes['business_segments'] = $this->parseBusinessSegments($mappedRow[ContractorSpreadsheet::COLUMN_BUSINESS_SEGMENTS] ?? null);
                    }

                    if ($this->hasHeading($headerIndexes, ContractorSpreadsheet::COLUMN_SMR_HAS_SRO)) {
                        $attributes['smr_has_sro'] = ContractorSpreadsheet::parseBoolean($mappedRow[ContractorSpreadsheet::COLUMN_SMR_HAS_SRO] ?? null);
                    }

                    if ($this->hasHeading($headerIndexes, ContractorSpreadsheet::COLUMN_PIR_HAS_SRO)) {
                        $attributes['pir_has_sro'] = ContractorSpreadsheet::parseBoolean($mappedRow[ContractorSpreadsheet::COLUMN_PIR_HAS_SRO] ?? null);
                    }

                    if ($this->hasHeading($headerIndexes, ContractorSpreadsheet::COLUMN_REGISTRATION_DATE)) {
                        $attributes['registration_date'] = $this->parseDate($mappedRow[ContractorSpreadsheet::COLUMN_REGISTRATION_DATE] ?? null);
                    }

                    if ($this->hasHeading($headerIndexes, ContractorSpreadsheet::COLUMN_BRANCH_CONTACTS)) {
                        $attributes['branch_contacts'] = collect(ContractorSpreadsheet::explodeValues($mappedRow[ContractorSpreadsheet::COLUMN_BRANCH_CONTACTS] ?? null))
                            ->map(fn (string $value): array => ['value' => $value])
                            ->values()
                            ->all();
                    }

                    if ($this->hasHeading($headerIndexes, ContractorSpreadsheet::COLUMN_RATING)) {
                        $attributes['rating_id'] = $this->resolveSingleRelationId($mappedRow[ContractorSpreadsheet::COLUMN_RATING] ?? null, $ratingMap);
                    }

                    if ($this->hasHeading($headerIndexes, ContractorSpreadsheet::COLUMN_STATUS)) {
                        $attributes['status'] = $this->parseContractorStatus($mappedRow[ContractorSpreadsheet::COLUMN_STATUS] ?? null);
                    }

                    $contractor->fill($attributes);
                    $contractor->save();

                    if ($this->hasHeading($headerIndexes, ContractorSpreadsheet::COLUMN_SLUG)) {
                        $slugSource = $this->parseNullableString($mappedRow[ContractorSpreadsheet::COLUMN_SLUG] ?? null)
                            ?: (string) $contractor->short_name;

                        Contractor::query()
                            ->whereKey($contractor->id)
                            ->update([
                                'slug' => Contractor::generateUniqueSlug($slugSource, $contractor->id),
                            ]);
                    }

                    if ($this->hasHeading($headerIndexes, ContractorSpreadsheet::COLUMN_CATEGORIES)) {
                        $contractor->categories()->sync(
                            $this->resolveManyRelationIds($mappedRow[ContractorSpreadsheet::COLUMN_CATEGORIES] ?? null, $categoryMap)
                        );
                    }

                    if ($this->hasHeading($headerIndexes, ContractorSpreadsheet::COLUMN_TERRITORIES)) {
                        $contractor->territories()->sync(
                            $this->resolveManyRelationIds($mappedRow[ContractorSpreadsheet::COLUMN_TERRITORIES] ?? null, $territoryMap)
                        );
                    }

                    if ($this->hasHeading($headerIndexes, ContractorSpreadsheet::COLUMN_SMR_RESOURCE_TYPES)) {
                        $contractor->smrResourceTypes()->sync(
                            $this->resolveManyRelationIds($mappedRow[ContractorSpreadsheet::COLUMN_SMR_RESOURCE_TYPES] ?? null, $resourceTypeMap)
                        );
                    }

                    if ($this->hasHeading($headerIndexes, ContractorSpreadsheet::COLUMN_PIR_RESOURCE_TYPES)) {
                        $contractor->pirResourceTypes()->sync(
                            $this->resolveManyRelationIds($mappedRow[ContractorSpreadsheet::COLUMN_PIR_RESOURCE_TYPES] ?? null, $resourceTypeMap)
                        );
                    }

                    $stats[$isExisting ? 'updated' : 'created']++;
                });
            } catch (\Throwable) {
                $stats['errors']++;
            }
        }

        return $stats;
    }

    private function mapRowByHeadings(array $row, array $headerIndexes): array
    {
        $mapped = [];

        foreach ($headerIndexes as $heading => $index) {
            $mapped[$heading] = $row[$index] ?? null;
        }

        return $mapped;
    }

    private function hasHeading(array $headerIndexes, string $heading): bool
    {
        return array_key_exists($heading, $headerIndexes);
    }

    private function isRowEmpty(array $mappedRow): bool
    {
        foreach ($mappedRow as $value) {
            if (is_numeric($value)) {
                return false;
            }

            if (trim((string) ($value ?? '')) !== '') {
                return false;
            }
        }

        return true;
    }

    private function buildLookupMap(array $items): array
    {
        $map = [];

        foreach ($items as $name => $id) {
            $map[ContractorSpreadsheet::normalizeLookupValue($name)] = (int) $id;
        }

        return $map;
    }

    private function resolveManyRelationIds(mixed $value, array $lookup): array
    {
        return collect(ContractorSpreadsheet::explodeValues($value))
            ->map(fn (string $name): ?int => $lookup[ContractorSpreadsheet::normalizeLookupValue($name)] ?? null)
            ->filter(fn (?int $id): bool => $id !== null)
            ->unique()
            ->values()
            ->all();
    }

    private function resolveSingleRelationId(mixed $value, array $lookup): ?int
    {
        $parsed = $this->parseNullableString($value);

        if ($parsed === null) {
            return null;
        }

        return $lookup[ContractorSpreadsheet::normalizeLookupValue($parsed)] ?? null;
    }

    private function parseBusinessSegments(mixed $value): array
    {
        $reverseOptions = collect(ContractorSpreadsheet::businessSegmentOptions())
            ->mapWithKeys(fn (string $label, string $key): array => [ContractorSpreadsheet::normalizeLookupValue($label) => $key])
            ->all();

        return collect(ContractorSpreadsheet::explodeValues($value))
            ->map(function (string $item) use ($reverseOptions): ?string {
                $normalized = ContractorSpreadsheet::normalizeLookupValue($item);

                return $reverseOptions[$normalized] ?? (array_key_exists($normalized, ContractorSpreadsheet::businessSegmentOptions()) ? $normalized : null);
            })
            ->filter(fn (?string $item): bool => $item !== null)
            ->unique()
            ->values()
            ->all();
    }

    private function parseContractorStatus(mixed $value): string
    {
        $normalized = ContractorSpreadsheet::normalizeLookupValue($value);

        if ($normalized === '') {
            return 'pending';
        }

        $reverse = collect(ContractorSpreadsheet::contractorStatusOptions())
            ->mapWithKeys(fn (string $label, string $key): array => [ContractorSpreadsheet::normalizeLookupValue($label) => $key])
            ->all();

        return $reverse[$normalized] ?? (array_key_exists($normalized, ContractorSpreadsheet::contractorStatusOptions()) ? $normalized : 'pending');
    }

    private function parseNullableString(mixed $value): ?string
    {
        $string = trim((string) ($value ?? ''));

        return $string === '' ? null : $string;
    }

    private function parseInteger(mixed $value): ?int
    {
        $string = trim((string) ($value ?? ''));

        if ($string === '' || ! ctype_digit($string)) {
            return null;
        }

        return (int) $string;
    }

    private function parseDate(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return Carbon::instance(ExcelDate::excelToDateTimeObject((float) $value))->format('Y-m-d');
        }

        try {
            return Carbon::parse((string) $value)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }
}
