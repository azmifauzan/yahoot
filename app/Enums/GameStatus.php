<?php

namespace App\Enums;

enum GameStatus: string
{
    case Waiting = 'waiting';
    case Playing = 'playing';
    case Reviewing = 'reviewing';
    case Finished = 'finished';
}
