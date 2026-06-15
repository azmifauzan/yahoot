<?php

/*
|--------------------------------------------------------------------------
| Achievement Badges
|--------------------------------------------------------------------------
|
| Declarative definition of the badges a registered user can earn. Each
| badge is awarded once when its criteria is met. The `criteria` is a
| `[stat => minimum]` map evaluated against the aggregate stats computed by
| App\Services\AchievementService. Names and descriptions live in the
| frontend i18n files under the `badges` key (badges.{key}.name / .desc).
|
| Available stats: games_played, games_won, total_correct, best_streak,
| total_xp.
|
*/

return [
    'first_game' => [
        'icon' => '🎮',
        'criteria' => ['games_played' => 1],
    ],
    'first_win' => [
        'icon' => '🏆',
        'criteria' => ['games_won' => 1],
    ],
    'veteran' => [
        'icon' => '🎖️',
        'criteria' => ['games_played' => 25],
    ],
    'champion' => [
        'icon' => '👑',
        'criteria' => ['games_won' => 10],
    ],
    'sharp_shooter' => [
        'icon' => '🎯',
        'criteria' => ['total_correct' => 100],
    ],
    'streak_master' => [
        'icon' => '🔥',
        'criteria' => ['best_streak' => 10],
    ],
    'high_scorer' => [
        'icon' => '⭐',
        'criteria' => ['total_xp' => 10000],
    ],
];
