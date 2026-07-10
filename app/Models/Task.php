<?php

namespace App\Models;

use App\Enums\Priority;
use App\Enums\TaskStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Task extends Model
{
    protected $fillable = [
        "user_id","project_id","title","description","is_recurring","priority","status", "due_date"
    ];

    protected function casts(): array
    {
        return [
            'is_recurring' => 'boolean',
            'priority'=> Priority::class,
            'status'=> TaskStatus::class,
            'due_date'=> 'date',
        ];
    }

    // === Relationships ===
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'task_tag');
    }

    public function subtasks(): HasMany
    {
        return $this->hasMany(Subtask::class);
    }
}
