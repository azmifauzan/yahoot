<?php

namespace App\Policies;

use App\Models\GameSession;
use App\Models\User;

class GameSessionPolicy
{
    public function view(User $user, GameSession $gameSession): bool
    {
        return $gameSession->host_id === $user->id;
    }

    public function start(User $user, GameSession $gameSession): bool
    {
        return $gameSession->host_id === $user->id;
    }

    public function next(User $user, GameSession $gameSession): bool
    {
        return $gameSession->host_id === $user->id;
    }

    public function reveal(User $user, GameSession $gameSession): bool
    {
        return $gameSession->host_id === $user->id;
    }

    public function end(User $user, GameSession $gameSession): bool
    {
        return $gameSession->host_id === $user->id;
    }

    public function results(User $user, GameSession $gameSession): bool
    {
        return $gameSession->host_id === $user->id;
    }

    public function export(User $user, GameSession $gameSession): bool
    {
        return $gameSession->host_id === $user->id;
    }

    public function cancel(User $user, GameSession $gameSession): bool
    {
        return $gameSession->host_id === $user->id;
    }
}
