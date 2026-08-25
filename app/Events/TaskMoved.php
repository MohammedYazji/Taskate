<?php

namespace App\Events;

use App\Models\Task;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TaskMoved implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Task $task,
        public int $movedByUserId,
    ) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel("project.{$this->task->project_id}")];
    }

    public function broadcastAs(): string
    {
        return 'task.moved';
    }

    public function broadcastWith(): array
    {
        return [
            'task_id' => $this->task->id,
            'section_id' => $this->task->section_id,
            'position' => $this->task->position,
            'status' => $this->task->status->value,
            'moved_by_user_id' => $this->movedByUserId,
        ];
    }
}
