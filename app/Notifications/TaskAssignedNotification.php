<?php

namespace App\Notifications;

use App\Models\Task;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class TaskAssignedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected Task $task,
        protected User $assigner,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage(array_merge([
            'id' => $this->id,
            'read_at' => null,
            'created_at' => now()->diffForHumans(),
        ], $this->toArray($notifiable)));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'task_assigned',
            'task_id' => $this->task->id,
            'task_title' => $this->task->title,
            'project_id' => $this->task->project_id,
            'project_name' => $this->task->project?->name,
            'assigner_name' => $this->assigner->name,
            'message' => "{$this->assigner->name} assigned you \"{$this->task->title}\"",
            'action_url' => $this->task->project_id
                ? route('projects.show', $this->task->project_id)
                : route('dashboard'),
        ];
    }
}
