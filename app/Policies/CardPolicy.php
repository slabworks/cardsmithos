<?php

namespace App\Policies;

use App\Models\Card;
use App\Models\User;

class CardPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Card $card): bool
    {
        return $card->customer->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Card $card): bool
    {
        return $card->customer->user_id === $user->id;
    }

    public function delete(User $user, Card $card): bool
    {
        return $card->customer->user_id === $user->id;
    }
}
