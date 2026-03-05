<?php

namespace App\Services;

use App\Models\GameSession;

class GameCodeService
{
    /**
     * Generate a unique 6-digit game code.
     */
    public function generate(): string
    {
        do {
            $code = str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
        } while (GameSession::query()->where('game_code', $code)->whereNot('status', 'finished')->exists());

        return $code;
    }
}
