<?php

use App\Models\GamePlayer;
use App\Models\GameSession;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

/*
|--------------------------------------------------------------------------
| Game Presence Channel
|--------------------------------------------------------------------------
|
| Authorize users to join a game's presence channel.
| Both authenticated users (hosts) and guest players can join.
|
*/
Broadcast::channel('game.{gameSessionId}', function ($user, int $gameSessionId) {
    $session = GameSession::find($gameSessionId);

    if (! $session) {
        return false;
    }

    // Host can always join
    if ($user && $user->id === $session->host_id) {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'role' => 'host',
        ];
    }

    // Authenticated players
    if ($user) {
        $player = GamePlayer::query()
            ->where('game_session_id', $gameSessionId)
            ->where('user_id', $user->id)
            ->where('is_connected', true)
            ->first();

        if ($player) {
            return [
                'id' => $player->id,
                'name' => $player->nickname,
                'role' => 'player',
            ];
        }
    }

    return false;
});
