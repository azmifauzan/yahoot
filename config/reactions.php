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
    'emojis' => [
        'celebrate', 'laugh', 'surprised', 'love', 'applause', 'fire', 'sad', 'like',
        "\u{1F389}", "\u{1F602}", "\u{1F62E}", "\u{1F60D}",
        "\u{1F44F}", "\u{1F525}", "\u{1F622}", "\u{1F44D}",
    ],

    /*
    | Minimum seconds between reactions per player (spam guard).
    */
    'throttle_seconds' => 1,
];
