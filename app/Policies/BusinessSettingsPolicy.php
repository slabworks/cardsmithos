<?php

namespace App\Policies;

use App\Models\BusinessSettings;
use App\Models\User;

class BusinessSettingsPolicy
{
    public function update(User $user, BusinessSettings $businessSettings): bool
    {
        return $businessSettings->user_id === $user->id;
    }
}
