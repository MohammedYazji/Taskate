import { useState } from "react";
import { router } from "@inertiajs/react";
import InviteMemberModal from "@/Components/InviteMemberModal";

function Avatar({ member }) {
    if (member.avatar) {
        return <img src={member.avatar} alt={member.name} className="w-7 h-7 rounded-full object-cover flex-shrink-0" />;
    }
    return (
        <div className="w-7 h-7 rounded-full bg-brand-100 text-brand-600 flex items-center justify-center text-xs font-semibold flex-shrink-0">
            {member.name?.charAt(0).toUpperCase()}
        </div>
    );
}

export default function ProjectMembersPanel({ project, members, isOwner }) {
    const [open, setOpen] = useState(false);
    const [inviting, setInviting] = useState(false);

    const removeMember = (member) => {
        if (!confirm(`Remove ${member.name} from this project?`)) return;
        router.delete(`/project-members/${member.member_id}`, { preserveScroll: true });
    };

    return (
        <div className="relative">
            <button
                onClick={() => setOpen(!open)}
                className="flex items-center -space-x-1.5 hover:opacity-80 transition"
                title="Members"
            >
                {members.slice(0, 4).map((m) => (
                    <div key={m.id} className="ring-2 ring-white rounded-full">
                        <Avatar member={m} />
                    </div>
                ))}
                {members.length > 4 && (
                    <div className="w-7 h-7 rounded-full bg-gray-100 text-gray-500 flex items-center justify-center text-[10px] font-semibold ring-2 ring-white">
                        +{members.length - 4}
                    </div>
                )}
            </button>

            {open && (
                <>
                    <div className="fixed inset-0 z-40" onClick={() => setOpen(false)} />
                    <div className="absolute top-full left-0 mt-2 w-64 bg-white border border-gray-200 rounded-xl shadow-xl z-50 py-2">
                        <div className="px-3 py-1.5 flex items-center justify-between">
                            <span className="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Members</span>
                            {isOwner && (
                                <button
                                    onClick={() => {
                                        setInviting(true);
                                        setOpen(false);
                                    }}
                                    className="text-[11px] font-medium text-brand-600 hover:text-brand-700"
                                >
                                    + Invite
                                </button>
                            )}
                        </div>
                        <div className="max-h-56 overflow-y-auto">
                            {members.map((m) => (
                                <div key={m.id} className="flex items-center gap-2.5 px-3 py-2 group">
                                    <Avatar member={m} />
                                    <div className="flex-1 min-w-0">
                                        <p className="text-xs text-gray-800 truncate">{m.name}</p>
                                        <p className="text-[10px] text-gray-400 capitalize">{m.role}</p>
                                    </div>
                                    {isOwner && m.role !== "owner" && (
                                        <button
                                            onClick={() => removeMember(m)}
                                            className="p-1 text-gray-300 hover:text-red-500 opacity-0 group-hover:opacity-100 transition"
                                            title="Remove"
                                        >
                                            <svg className="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    )}
                                </div>
                            ))}
                        </div>
                    </div>
                </>
            )}

            {inviting && <InviteMemberModal project={project} onClose={() => setInviting(false)} />}
        </div>
    );
}
