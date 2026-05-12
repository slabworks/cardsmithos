<?php

namespace App\Policies;

use App\Models\ServiceWaiver;
use App\Models\User;

class ServiceWaiverPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, ServiceWaiver $serviceWaiver): bool
    {
        return $serviceWaiver->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, ServiceWaiver $serviceWaiver): bool
    {
        return $serviceWaiver->user_id === $user->id;
    }

    public function delete(User $user, ServiceWaiver $serviceWaiver): bool
    {
        return $serviceWaiver->user_id === $user->id;
    }
}
