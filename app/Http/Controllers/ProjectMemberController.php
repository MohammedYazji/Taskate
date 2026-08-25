<?php

namespace App\Http\Controllers;

use App\Models\ProjectMember;
use Illuminate\Support\Facades\Auth;

class ProjectMemberController extends Controller
{
    // === Remove a collaborator (owner action, or a member leaving voluntarily) ===
    public function destroy(ProjectMember $member)
    {
        if ($member->user_id !== Auth::id()) {
            $this->authorize('manageMembers', $member->project);
        }

        $member->delete();

        return back()->with('success', 'Member removed');
    }
}
