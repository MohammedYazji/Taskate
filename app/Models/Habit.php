<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Habit extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'name', 'icon', 'frequency_type', 'frequency_config',
        'goal_type', 'goal_config', 'start_date', 'goal_days',
        'section', 'reminders', 'auto_popup', 'is_archived', 'position',
    ];

    protected function casts(): array
    {
        return [
            'frequency_config' => 'array',
            'goal_config' => 'array',
            'reminders' => 'array',
            'start_date' => 'date',
            'auto_popup' => 'boolean',
            'is_archived' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function checkins(): HasMany
    {
        return $this->hasMany(HabitCheckin::class);
    }

    public function getStreakAttribute(): int
    {
        $streak = 0;
        $date = now()->toDateString();

        while (true) {
            $checkin = $this->checkins()->where('date', $date)->where('completed', true)->first();
            if (!$checkin) break;
            $streak++;
            $date = date('Y-m-d', strtotime($date . ' -1 day'));
        }

        return $streak;
    }

    public function getTotalCheckinsAttribute(): int
    {
        return $this->checkins()->where('completed', true)->count();
    }

    public function isCompletedOn(string $date): bool
    {
        return $this->checkins()->where('date', $date)->where('completed', true)->exists();
    }

    public function getFrequencyLabelAttribute(): string
    {
        return match($this->frequency_type) {
            'daily' => 'Daily',
            'weekly' => $this->frequency_config && isset($this->frequency_config['days'])
                ? 'Weekly (' . implode(', ', $this->frequency_config['days']) . ')'
                : 'Weekly',
            'interval' => isset($this->frequency_config['every'])
                ? 'Every ' . $this->frequency_config['every'] . ' days'
                : 'Interval',
            default => 'Daily',
        };
    }

    public function getGoalLabelAttribute(): string
    {
        if ($this->goal_type === 'boolean') return 'Achieve it all';
        $config = $this->goal_config ?? [];
        $target = $config['target'] ?? 1;
        $unit = $config['unit'] ?? 'Count';
        return "{$target} {$unit}/Day";
    }
}
