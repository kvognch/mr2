<?php

namespace App\Policies;

use App\Models\ContractorReview;
use App\Models\User;

class ContractorReviewPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, ContractorReview $review): bool
    {
        return $this->update($user, $review);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, ContractorReview $review): bool
    {
        return $user->isSuperadmin() || $user->isManager() || $review->user_id === $user->id;
    }

    public function delete(User $user, ContractorReview $review): bool
    {
        return $this->update($user, $review);
    }

    public function deleteAny(User $user): bool
    {
        return true;
    }
}
