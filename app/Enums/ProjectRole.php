<?php

namespace App\Enums;

enum ProjectRole: string
{
    case Editor = 'editor';
    case Viewer = 'viewer';
}
