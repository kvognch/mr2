<?php

namespace App\Policies;

use App\Models\Contractor;
use App\Models\User;

class ContractorPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Contractor $contractor): bool
    {
        return $this->update($user, $contractor);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Contractor $contractor): bool
    {
        if ($user->isSuperadmin() || $user->isManager()) {
            return true;
        }

        return $user->isClient() && $contractor->owner_id === $user->id;
    }

    public function delete(User $user, Contractor $contractor): bool
    {
        return $this->update($user, $contractor);
    }

    public function deleteAny(User $user): bool
    {
        return $user->isSuperadmin() || $user->isManager();
    }
}
