<?php

namespace App\Enums;

enum Importance: string
{
    case High   = 'high';
    case Medium = 'medium';
    case Low    = 'low';
    case None   = 'none';
}
