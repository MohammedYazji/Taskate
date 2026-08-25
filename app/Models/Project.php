<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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

    public function projectMembers(): HasMany
    {
        return $this->hasMany(ProjectMember::class);
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_members')
            ->withPivot('id', 'role')
            ->withTimestamps();
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(ProjectInvitation::class);
    }

    // === Access control ===

    // Returns 'owner', 'editor', 'viewer', or null if the user has no access
    public function role(User $user): ?string
    {
        if ($this->user_id === $user->id) {
            return 'owner';
        }

        $member = $this->relationLoaded('projectMembers')
            ? $this->projectMembers->firstWhere('user_id', $user->id)
            : $this->projectMembers()->where('user_id', $user->id)->first();

        return $member?->role?->value;
    }

    public function hasMember(User $user): bool
    {
        return $this->role($user) !== null;
    }

    // === Scopes ===

    // Projects the user owns or is a collaborator on
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where(function (Builder $q) use ($userId) {
            $q->where('user_id', $userId)
                ->orWhereHas('projectMembers', function (Builder $q2) use ($userId) {
                    $q2->where('user_id', $userId);
                });
        });
    }
}
