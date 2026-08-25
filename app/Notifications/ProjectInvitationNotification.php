<?php

namespace App\Notifications;

use App\Models\ProjectInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class ProjectInvitationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected ProjectInvitation $invitation,
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
        $project = $this->invitation->project;
        $inviter = $this->invitation->invitedBy;

        return [
            'type' => 'project_invitation',
            'invitation_id' => $this->invitation->id,
            'project_id' => $project->id,
            'project_name' => $project->name,
            'inviter_name' => $inviter->name,
            'role' => $this->invitation->role->value,
            'message' => "{$inviter->name} invited you to \"{$project->name}\" as {$this->invitation->role->value}",
            'action_url' => null,
        ];
    }
}
