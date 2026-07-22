<?php

namespace App\Enums;

enum SessionType: string
{
    case Work = 'work';
    case ShortBreak = 'short_break';
    case LongBreak = 'long_break';
}
