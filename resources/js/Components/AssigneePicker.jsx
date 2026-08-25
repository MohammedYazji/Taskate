import { useState } from "react";

function Avatar({ member, size = "w-6 h-6" }) {
    if (member.avatar) {
        return <img src={member.avatar} alt={member.name} className={`${size} rounded-full object-cover flex-shrink-0`} />;
    }
    return (
        <div className={`${size} rounded-full bg-brand-100 text-brand-600 flex items-center justify-center text-[10px] font-semibold flex-shrink-0`}>
            {member.name?.charAt(0).toUpperCase()}
        </div>
    );
}

// members: [{ id, name, avatar, role }], value: assigned_to_id or null
export default function AssigneePicker({ members, value, onChange, iconMode = false }) {
    const [open, setOpen] = useState(false);

    if (!members || members.length <= 1) return null;

    const current = members.find((m) => m.id === value) || null;

    return (
        <div className="relative">
            <button
                onClick={() => setOpen(!open)}
                className={
                    iconMode
                        ? "w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition"
                        : "flex items-center gap-1.5 px-2 py-1 rounded-lg text-xs text-gray-600 hover:bg-gray-100 transition"
                }
                title={current ? `Assigned to ${current.name}` : "Assign"}
            >
                {current ? (
                    <Avatar member={current} />
                ) : (
                    <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                )}
                {!iconMode && <span>{current ? current.name : "Assign"}</span>}
            </button>

            {open && (
                <>
                    <div className="fixed inset-0 z-40" onClick={() => setOpen(false)} />
                    <div className="absolute top-full right-0 mt-1 w-48 bg-white border border-gray-200 rounded-xl shadow-xl z-50 py-1.5 max-h-64 overflow-y-auto">
                        {current && (
                            <button
                                onClick={() => {
                                    onChange(null);
                                    setOpen(false);
                                }}
                                className="w-full flex items-center gap-2 px-3 py-2 text-xs text-gray-500 hover:bg-gray-50 transition"
                            >
                                <svg className="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                Unassign
                            </button>
                        )}
                        {members.map((member) => (
                            <button
                                key={member.id}
                                onClick={() => {
                                    onChange(member.id);
                                    setOpen(false);
                                }}
                                className={`w-full flex items-center gap-2 px-3 py-2 text-xs transition ${
                                    member.id === value ? "bg-brand-50 text-brand-600" : "text-gray-700 hover:bg-gray-50"
                                }`}
                            >
                                <Avatar member={member} />
                                <span className="flex-1 text-left truncate">{member.name}</span>
                            </button>
                        ))}
                    </div>
                </>
            )}
        </div>
    );
}
