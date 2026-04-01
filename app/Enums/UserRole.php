<?php

namespace App\Enums;

enum UserRole: string
{
    case Superadmin = 'superadmin';
    case Manager = 'manager';
    case Client = 'client';

    public function label(): string
    {
        return match ($this) {
            self::Superadmin => 'Суперадмин',
            self::Manager => 'Менеджер',
            self::Client => 'Клиент',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $role) => [$role->value => $role->label()])
            ->all();
    }
}
