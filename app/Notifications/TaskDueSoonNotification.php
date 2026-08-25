<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class TaskDueSoonNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected Task $task,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $dueDate = $this->task->due_date;
        $dueLabel = $dueDate->isPast() ? 'was due ' . $dueDate->diffForHumans() : 'is due ' . $dueDate->diffForHumans();

        return [
            'type' => 'task_due_soon',
            'task_id' => $this->task->id,
            'task_title' => $this->task->title,
            'due_date' => $dueDate->format('Y-m-d'),
            'message' => "\"{$this->task->title}\" {$dueLabel}",
            'action_url' => $this->task->project_id
                ? route('projects.show', $this->task->project_id)
                : route('dashboard'),
        ];
    }
}
