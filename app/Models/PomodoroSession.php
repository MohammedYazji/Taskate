<?php

namespace App\Models;

use App\Enums\SessionType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PomodoroSession extends Model
{
    protected $fillable = [
        'user_id', 'task_id', 'note', 'type', 'duration', 'completed', 'started_at', 'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => SessionType::class,
            'completed' => 'boolean',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }
}
