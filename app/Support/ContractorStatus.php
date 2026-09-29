<?php

namespace App\Support;

use App\Models\User;

final class ContractorStatus
{
    /**
     * Apply the management default only when no status was supplied.
     *
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    public static function applyManagementDefault(array $attributes, ?User $actor): array
    {
        $status = $attributes['status'] ?? null;
        $statusIsMissing = ! array_key_exists('status', $attributes)
            || $status === null
            || (is_string($status) && trim($status) === '');

        if ($actor?->canManageContractors() && $statusIsMissing) {
            $attributes['status'] = 'approved';
        }

        return $attributes;
    }
}
