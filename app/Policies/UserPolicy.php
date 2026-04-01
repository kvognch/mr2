<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperadmin() || $user->isManager();
    }

    public function view(User $user, User $record): bool
    {
        return $this->update($user, $record);
    }

    public function create(User $user): bool
    {
        return $this->viewAny($user);
    }

    public function update(User $user, User $record): bool
    {
        if ($user->isSuperadmin()) {
            return true;
        }

        return $user->isManager() && $record->role === UserRole::Client;
    }

    public function delete(User $user, User $record): bool
    {
        return $this->update($user, $record);
    }

    public function deleteAny(User $user): bool
    {
        return $this->viewAny($user);
    }
}
