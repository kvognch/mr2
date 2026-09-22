<?php

namespace App\Support;

final class OrganizationSeoSettings
{
    public static function defaults(): array
    {
        return SeoSettings::organizationDefaults();
    }

    public static function all(): array
    {
        return SeoSettings::all()['organizations'] ?? self::defaults();
    }

    public static function save(array $data): void
    {
        SeoSettings::save(['organizations' => $data]);
    }
}
