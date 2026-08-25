<?php

namespace App\Models;

use App\Enums\Importance;
use App\Enums\Priority;
use App\Enums\TaskStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Task extends Model
{
    protected $fillable = [
        "user_id","assigned_to_id","project_id","sprint_id","section_id","title","description","is_recurring","priority","importance","status", "due_date", "due_reminder_sent_at", "position"
    ];

    protected function casts(): array
    {
        return [
            'is_recurring' => 'boolean',
            'priority'=> Priority::class,
            'importance'=> Importance::class,
            'status'=> TaskStatus::class,
            'due_date'=> 'date',
            'due_reminder_sent_at' => 'datetime',
        ];
    }

    // === Relationships ===
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_id');
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

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class)->latest();
    }

    public function sprint(): BelongsTo
    {
        return $this->belongsTo(Sprint::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function isUrgent(): bool
    {
        if (!$this->due_date) return false;

        return $this->due_date->lte(now()->addDay()->endOfDay());
    }

    public function getQuadrant(): string
    {
        $important = in_array($this->importance->value, ['high', 'medium']);
        $urgent = $this->isUrgent();

        return match(true) {
            $important && $urgent     => 'do',
            $important && !$urgent    => 'schedule',
            !$important && $urgent    => 'delegate',
            default                   => 'delete',
        };
    }

    // === Scopes ===

    // Tasks the user created or is assigned to
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where(function (Builder $q) use ($userId) {
            $q->where('user_id', $userId)->orWhere('assigned_to_id', $userId);
        });
    }
}
