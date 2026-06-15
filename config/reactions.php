<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Allowed Reaction Emojis
    |--------------------------------------------------------------------------
    |
    | Whitelist of emoji players may broadcast during a game. Anything outside
    | this set is rejected by validation. Shared with the frontend reaction bar.
    |
    */
    'emojis' => ['🎉', '😂', '😮', '😍', '👏', '🔥', '😢', '👍'],

    /*
    | Minimum seconds between reactions per player (spam guard).
    */
    'throttle_seconds' => 1,
];
