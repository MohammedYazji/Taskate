import { useEffect, useState } from 'react';
import echo from '@/echo';

export default function usePresence(channelName) {
    const [members, setMembers] = useState([]);

    useEffect(() => {
        let cancelled = false;
        let channel;

        try {
            channel = echo.join(channelName);
        } catch (e) {
            console.error('[Presence] Failed to join channel:', channelName, e);
            return;
        }

        channel.here((users) => {
            if (!cancelled) setMembers(users);
        });

        channel.joining((user) => {
            if (!cancelled) setMembers((prev) => [...prev, user]);
        });

        channel.leaving((user) => {
            if (!cancelled) setMembers((prev) => prev.filter((u) => u.id !== user.id));
        });

        channel.error((e) => {
            console.error('[Presence] Channel error:', channelName, e);
        });

        return () => {
            cancelled = true;
            try { echo.leave(channelName); } catch {}
        };
    }, [channelName]);

    return members;
}
