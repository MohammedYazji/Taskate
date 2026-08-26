<?php

namespace App\Events;

use App\Models\Task;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TaskUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Task $task,
        public int $updatedByUserId,
    ) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel("project.{$this->task->project_id}")];
    }

    public function broadcastAs(): string
    {
        return 'task.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'task_id' => $this->task->id,
            'title' => $this->task->title,
            'status' => $this->task->status->value,
            'priority' => $this->task->priority?->value,
            'due_date' => $this->task->due_date?->format('Y-m-d'),
            'assigned_to_id' => $this->task->assigned_to_id,
            'position' => $this->task->position,
            'section_id' => $this->task->section_id,
            'updated_by_user_id' => $this->updatedByUserId,
        ];
    }
}
