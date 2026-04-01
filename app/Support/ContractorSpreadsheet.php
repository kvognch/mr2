<?php

namespace App\Support;

use App\Models\Contractor;

class ContractorSpreadsheet
{
    public const MULTI_VALUE_SEPARATOR = '|';

    public const COLUMN_ID = 'ID';
    public const COLUMN_SHORT_NAME = 'Краткое название';
    public const COLUMN_SLUG = 'Алиас';
    public const COLUMN_FULL_NAME = 'Полное название';
    public const COLUMN_BUSINESS_SEGMENTS = 'Сегмент бизнеса';
    public const COLUMN_WEBSITE = 'Сайт';
    public const COLUMN_SOCIAL_TELEGRAM = 'Telegram';
    public const COLUMN_SOCIAL_VK = 'ВКонтакте';
    public const COLUMN_SOCIAL_WHATSAPP = 'WhatsApp';
    public const COLUMN_PHONE = 'Телефон';
    public const COLUMN_EMAIL = 'Электронная почта';
    public const COLUMN_CATEGORIES = 'Категория';
    public const COLUMN_RESPONSE_TIME = 'Сроки ответа';
    public const COLUMN_WORK_VOLUME = 'Объем выполняемых работ, ₽';
    public const COLUMN_TERRITORIES = 'Территория работы';
    public const COLUMN_SMR_RESOURCE_TYPES = 'СМР (ресурсы)';
    public const COLUMN_SMR_HAS_SRO = 'Наличие СРО СМР';
    public const COLUMN_PIR_RESOURCE_TYPES = 'ПИР/ПСД (ресурсы)';
    public const COLUMN_PIR_HAS_SRO = 'Наличие СРО ПИР/ПСД';
    public const COLUMN_OGRN = 'ОГРН';
    public const COLUMN_INN = 'ИНН';
    public const COLUMN_KPP = 'КПП';
    public const COLUMN_REGISTRATION_DATE = 'Дата регистрации';
    public const COLUMN_LEGAL_ADDRESS = 'Юридический адрес';
    public const COLUMN_BRANCH_CONTACTS = 'Адреса и телефоны филиалов';
    public const COLUMN_ADDITIONAL_INFO = 'Дополнительная информация';
    public const COLUMN_RATING = 'Рейтинг';
    public const COLUMN_STATUS = 'Статус';

    public static function headings(): array
    {
        return [
            self::COLUMN_ID,
            self::COLUMN_SHORT_NAME,
            self::COLUMN_SLUG,
            self::COLUMN_FULL_NAME,
            self::COLUMN_BUSINESS_SEGMENTS,
            self::COLUMN_WEBSITE,
            self::COLUMN_SOCIAL_TELEGRAM,
            self::COLUMN_SOCIAL_VK,
            self::COLUMN_SOCIAL_WHATSAPP,
            self::COLUMN_PHONE,
            self::COLUMN_EMAIL,
            self::COLUMN_CATEGORIES,
            self::COLUMN_RESPONSE_TIME,
            self::COLUMN_WORK_VOLUME,
            self::COLUMN_TERRITORIES,
            self::COLUMN_SMR_RESOURCE_TYPES,
            self::COLUMN_SMR_HAS_SRO,
            self::COLUMN_PIR_RESOURCE_TYPES,
            self::COLUMN_PIR_HAS_SRO,
            self::COLUMN_OGRN,
            self::COLUMN_INN,
            self::COLUMN_KPP,
            self::COLUMN_REGISTRATION_DATE,
            self::COLUMN_LEGAL_ADDRESS,
            self::COLUMN_BRANCH_CONTACTS,
            self::COLUMN_ADDITIONAL_INFO,
            self::COLUMN_RATING,
            self::COLUMN_STATUS,
        ];
    }

    public static function businessSegmentOptions(): array
    {
        return [
            'b2b' => 'B2B - для бизнеса',
            'b2c' => 'B2C - для клиента',
        ];
    }

    public static function contractorStatusOptions(): array
    {
        return [
            'pending' => 'На рассмотрении',
            'approved' => 'Одобрен',
            'rejected' => 'Отклонён',
        ];
    }

    public static function contractorToRow(Contractor $contractor): array
    {
        return [
            self::COLUMN_ID => $contractor->id,
            self::COLUMN_SHORT_NAME => $contractor->short_name,
            self::COLUMN_SLUG => $contractor->slug,
            self::COLUMN_FULL_NAME => $contractor->full_name,
            self::COLUMN_BUSINESS_SEGMENTS => static::implodeValues(
                collect($contractor->business_segments ?? [])
                    ->map(fn (string $key): string => static::businessSegmentOptions()[$key] ?? $key)
                    ->all()
            ),
            self::COLUMN_WEBSITE => $contractor->website,
            self::COLUMN_SOCIAL_TELEGRAM => $contractor->social_telegram,
            self::COLUMN_SOCIAL_VK => $contractor->social_vk,
            self::COLUMN_SOCIAL_WHATSAPP => $contractor->social_whatsapp,
            self::COLUMN_PHONE => $contractor->phone,
            self::COLUMN_EMAIL => $contractor->email,
            self::COLUMN_CATEGORIES => static::implodeValues($contractor->categories->pluck('name')->sort()->values()->all()),
            self::COLUMN_RESPONSE_TIME => $contractor->response_time,
            self::COLUMN_WORK_VOLUME => $contractor->work_volume,
            self::COLUMN_TERRITORIES => static::implodeValues($contractor->territories->pluck('name')->sort()->values()->all()),
            self::COLUMN_SMR_RESOURCE_TYPES => static::implodeValues($contractor->smrResourceTypes->pluck('name')->sort()->values()->all()),
            self::COLUMN_SMR_HAS_SRO => static::formatBoolean($contractor->smr_has_sro),
            self::COLUMN_PIR_RESOURCE_TYPES => static::implodeValues($contractor->pirResourceTypes->pluck('name')->sort()->values()->all()),
            self::COLUMN_PIR_HAS_SRO => static::formatBoolean($contractor->pir_has_sro),
            self::COLUMN_OGRN => $contractor->ogrn,
            self::COLUMN_INN => $contractor->inn,
            self::COLUMN_KPP => $contractor->kpp,
            self::COLUMN_REGISTRATION_DATE => $contractor->registration_date?->format('Y-m-d'),
            self::COLUMN_LEGAL_ADDRESS => $contractor->legal_address,
            self::COLUMN_BRANCH_CONTACTS => static::implodeValues(
                collect($contractor->branch_contacts ?? [])
                    ->pluck('value')
                    ->filter(fn (?string $value): bool => filled($value))
                    ->values()
                    ->all()
            ),
            self::COLUMN_ADDITIONAL_INFO => $contractor->additional_info,
            self::COLUMN_RATING => $contractor->rating?->name,
            self::COLUMN_STATUS => static::contractorStatusOptions()[$contractor->status] ?? $contractor->status,
        ];
    }

    public static function implodeValues(array $values): ?string
    {
        $normalized = collect($values)
            ->map(fn ($value): string => trim((string) $value))
            ->filter(fn (string $value): bool => $value !== '')
            ->values()
            ->all();

        return $normalized === [] ? null : implode(' ' . self::MULTI_VALUE_SEPARATOR . ' ', $normalized);
    }

    public static function explodeValues(mixed $value): array
    {
        $string = trim((string) ($value ?? ''));

        if ($string === '') {
            return [];
        }

        return collect(preg_split('/\s*\|\s*/u', $string) ?: [])
            ->map(fn (string $item): string => trim($item))
            ->filter(fn (string $item): bool => $item !== '')
            ->values()
            ->all();
    }

    public static function normalizeLookupValue(mixed $value): string
    {
        return mb_strtolower(trim((string) ($value ?? '')));
    }

    public static function formatBoolean(bool $value): string
    {
        return $value ? 'Да' : 'Нет';
    }

    public static function parseBoolean(mixed $value): bool
    {
        $normalized = static::normalizeLookupValue($value);

        return in_array($normalized, ['1', 'true', 'yes', 'y', 'да'], true);
    }
}
