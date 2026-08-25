import { useEffect, useRef, useState } from "react";
import NotificationItem from "@/Components/NotificationItem";
import echo from "@/echo";

function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content;
}

function jsonFetch(url, options = {}) {
    return fetch(url, {
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": csrfToken(),
            "X-Requested-With": "XMLHttpRequest",
        },
        ...options,
    });
}

export default function NotificationBell({ userId }) {
    const [open, setOpen] = useState(false);
    const [notifications, setNotifications] = useState([]);
    const [unreadCount, setUnreadCount] = useState(0);
    const pollRef = useRef(null);

    const load = () => {
        jsonFetch("/notifications")
            .then((res) => res.json())
            .then((data) => {
                setNotifications(data.notifications || []);
                setUnreadCount(data.unread_count || 0);
            })
            .catch(() => {});
    };

    useEffect(() => {
        load();
        // Fallback poll in case the socket connection drops or reconnects late.
        pollRef.current = setInterval(load, 120000);
        return () => clearInterval(pollRef.current);
    }, []);

    useEffect(() => {
        if (!userId) return;

        const channel = echo.private(`App.Models.User.${userId}`);
        channel.notification((notification) => {
            setNotifications((prev) => [notification, ...prev]);
            setUnreadCount((prev) => prev + 1);
        });

        return () => {
            echo.leave(`App.Models.User.${userId}`);
        };
    }, [userId]);

    const markRead = (id) => {
        setNotifications((prev) => prev.map((n) => (n.id === id ? { ...n, read_at: new Date().toISOString() } : n)));
        setUnreadCount((prev) => Math.max(0, prev - 1));
        jsonFetch(`/notifications/${id}/read`, { method: "PATCH" }).catch(() => {});
    };

    const markAllRead = () => {
        setNotifications((prev) => prev.map((n) => ({ ...n, read_at: n.read_at || new Date().toISOString() })));
        setUnreadCount(0);
        jsonFetch("/notifications/read-all", { method: "PATCH" }).catch(() => {});
    };

    return (
        <div className="relative">
            <button
                title="Notifications"
                onClick={() => setOpen(!open)}
                className="w-10 h-10 rounded-xl flex items-center justify-center text-gray-400 hover:text-gray-700 hover:bg-gray-200/60 transition relative"
            >
                <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path
                        strokeLinecap="round"
                        strokeLinejoin="round"
                        strokeWidth="1.5"
                        d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"
                    />
                </svg>
                {unreadCount > 0 && (
                    <span className="absolute top-2 right-2 w-2 h-2 bg-brand-500 rounded-full"></span>
                )}
            </button>

            {open && (
                <>
                    <div className="fixed inset-0 z-40" onClick={() => setOpen(false)} />
                    <div className="absolute bottom-full left-0 mb-2 w-80 max-h-96 bg-white border border-gray-200 rounded-xl shadow-xl z-50 flex flex-col">
                        <div className="flex items-center justify-between px-4 py-2.5 border-b border-gray-100 flex-shrink-0">
                            <span className="text-xs font-semibold text-gray-700">Notifications</span>
                            {unreadCount > 0 && (
                                <button onClick={markAllRead} className="text-[11px] text-brand-600 hover:text-brand-700">
                                    Mark all read
                                </button>
                            )}
                        </div>
                        <div className="overflow-y-auto">
                            {notifications.length === 0 ? (
                                <p className="text-xs text-gray-400 text-center py-8">No notifications yet</p>
                            ) : (
                                notifications.map((n) => (
                                    <NotificationItem key={n.id} notification={n} onRead={markRead} />
                                ))
                            )}
                        </div>
                    </div>
                </>
            )}
        </div>
    );
}
