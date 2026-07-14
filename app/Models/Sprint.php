<?php

namespace App\Models;

use App\Enums\SprintStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sprint extends Model
{
    protected $fillable = [
        'project_id', 'name', 'goal', 'start_date', 'end_date', 'status'
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'status' => SprintStatus::class,
        ];
    }

    // === Relationships ===
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    // === Get all tasks in this sprint ===
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }
}
