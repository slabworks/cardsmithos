<?php

namespace App\Policies;

use App\Models\BusinessStatistic;
use App\Models\User;

class BusinessStatisticPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, BusinessStatistic $businessStatistic): bool
    {
        return $businessStatistic->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, BusinessStatistic $businessStatistic): bool
    {
        return $businessStatistic->user_id === $user->id;
    }

    public function delete(User $user, BusinessStatistic $businessStatistic): bool
    {
        return $businessStatistic->user_id === $user->id;
    }
}
