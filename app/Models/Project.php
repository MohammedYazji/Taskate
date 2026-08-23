<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    protected $fillable = [
        'user_id', 'name', 'color', 'icon', 'view_type', 'pinned', 'folder_id', 'description', 'position'
    ];

    protected function casts(): array
    {
        return [
            'pinned' => 'boolean',
        ];
    }

    // === Relationships ===
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function folder(): BelongsTo
    {
        return $this->belongsTo(Folder::class);
    }

    public function sprints(): HasMany
    {
        return $this->hasMany(Sprint::class);
    }

    public function sections(): HasMany
    {
        return $this->hasMany(Section::class);
    }
}
