<?php

namespace App\Http\Controllers;

use App\Enums\InvitationStatus;
use App\Models\Project;
use App\Models\ProjectInvitation;
use App\Models\ProjectMember;
use App\Models\User;
use App\Notifications\ProjectInvitationNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProjectInvitationController extends Controller
{
    // === Invite an existing user to collaborate on a project ===
    public function store(Request $request, Project $project)
    {
        $this->authorize('manageMembers', $project);

        $data = $request->validate([
            'email' => 'required|email',
            'role' => 'required|string|in:editor,viewer',
        ]);

        $invitedUser = User::where('email', $data['email'])->first();

        if (!$invitedUser) {
            return back()->withErrors(['email' => 'No Taskate account found with that email.']);
        }

        if ($invitedUser->id === $project->user_id) {
            return back()->withErrors(['email' => 'That user already owns this project.']);
        }

        if ($invitedUser->id === Auth::id()) {
            return back()->withErrors(['email' => "You can't invite yourself."]);
        }

        if ($project->hasMember($invitedUser)) {
            return back()->withErrors(['email' => 'That user is already a member of this project.']);
        }

        $invitation = ProjectInvitation::updateOrCreate(
            ['project_id' => $project->id, 'invited_user_id' => $invitedUser->id],
            ['invited_by_id' => Auth::id(), 'role' => $data['role'], 'status' => InvitationStatus::Pending]
        );

        $invitedUser->notify(new ProjectInvitationNotification($invitation));

        return back()->with('success', "Invited {$invitedUser->name} to \"{$project->name}\"");
    }

    // === Accept a pending invitation ===
    public function accept(ProjectInvitation $invitation)
    {
        if ($invitation->invited_user_id !== Auth::id()) {
            abort(403);
        }

        DB::transaction(function () use ($invitation) {
            ProjectMember::updateOrCreate(
                ['project_id' => $invitation->project_id, 'user_id' => $invitation->invited_user_id],
                ['role' => $invitation->role, 'invited_by_id' => $invitation->invited_by_id]
            );

            $invitation->update(['status' => InvitationStatus::Accepted]);
        });

        return redirect()->route('projects.show', $invitation->project_id);
    }

    // === Decline a pending invitation ===
    public function decline(ProjectInvitation $invitation)
    {
        if ($invitation->invited_user_id !== Auth::id()) {
            abort(403);
        }

        $invitation->update(['status' => InvitationStatus::Declined]);

        return back();
    }

    // === Cancel a pending invitation (owner side) ===
    public function cancel(ProjectInvitation $invitation)
    {
        $this->authorize('manageMembers', $invitation->project);

        $invitation->update(['status' => InvitationStatus::Cancelled]);

        return back();
    }
}
