<?php

namespace App\Enums;

enum TaskStatus: string
{
    case TODO = 'todo';
    case InProgress = 'in_progress';
    case DONE = 'done';
}
