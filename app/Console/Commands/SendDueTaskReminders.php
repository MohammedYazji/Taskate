<?php

namespace App\Console\Commands;

use App\Enums\TaskStatus;
use App\Models\Task;
use App\Notifications\TaskDueSoonNotification;
use Illuminate\Console\Command;

class SendDueTaskReminders extends Command
{
    protected $signature = 'tasks:send-due-reminders';

    protected $description = 'Notify assignees (or owners) once when a task is due today or overdue';

    public function handle(): int
    {
        $sent = 0;

        Task::query()
            ->whereNotNull('due_date')
            ->where('status', '!=', TaskStatus::Done)
            ->whereNull('due_reminder_sent_at')
            ->where('due_date', '<=', now()->addDay()->toDateString())
            ->with('assignedTo', 'user')
            ->chunkById(100, function ($tasks) use (&$sent) {
                foreach ($tasks as $task) {
                    $recipient = $task->assignedTo ?? $task->user;

                    if (!$recipient) {
                        continue;
                    }

                    $recipient->notify(new TaskDueSoonNotification($task));
                    $task->update(['due_reminder_sent_at' => now()]);
                    $sent++;
                }
            });

        $this->info("Sent {$sent} due-date reminder(s).");

        return self::SUCCESS;
    }
}
