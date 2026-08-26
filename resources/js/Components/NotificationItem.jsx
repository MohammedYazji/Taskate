import { router } from "@inertiajs/react";

export default function NotificationItem({ notification, onRead }) {
    const respondToInvitation = (accept) => {
        const url = accept
            ? `/invitations/${notification.invitation_id}/accept`
            : `/invitations/${notification.invitation_id}/decline`;
        router.post(url, {}, { preserveScroll: true, onSuccess: () => onRead(notification.id) });
    };

    const open = () => {
        if (notification.type === "project_invitation" && notification.invitation_id) {
            onRead(notification.id);
            router.post(`/invitations/${notification.invitation_id}/accept`, {}, {
                preserveScroll: false,
                onSuccess: () => {
                    if (notification.project_id) router.visit(`/projects/${notification.project_id}`);
                },
            });
            return;
        }
        onRead(notification.id);
        const url = notification.action_url || (notification.project_id ? `/projects/${notification.project_id}` : null);
        if (url) router.visit(url);
    };

    return (
        <div className={`px-4 py-3 border-b border-gray-50 last:border-0 ${notification.read_at ? "" : "bg-brand-50/40"}`}>
            <button onClick={open} className="text-left w-full">
                <p className="text-xs text-gray-700 leading-snug">{notification.message}</p>
                <p className="text-[10px] text-gray-400 mt-1">{notification.created_at}</p>
            </button>
            {notification.type === "project_invitation" && !notification.read_at && (
                <div className="flex items-center gap-2 mt-2">
                    <button
                        onClick={() => respondToInvitation(true)}
                        className="text-[11px] font-medium px-2.5 py-1 rounded-lg bg-brand-500 text-white hover:bg-brand-600 transition"
                    >
                        Accept
                    </button>
                    <button
                        onClick={() => respondToInvitation(false)}
                        className="text-[11px] font-medium px-2.5 py-1 rounded-lg bg-gray-100 text-gray-600 hover:bg-gray-200 transition"
                    >
                        Decline
                    </button>
                </div>
            )}
        </div>
    );
}
