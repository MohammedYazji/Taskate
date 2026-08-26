import { useEffect, useState } from 'react';
import echo from '@/echo';

export default function usePresence(channelName) {
    const [members, setMembers] = useState([]);

    useEffect(() => {
        let cancelled = false;
        const channel = echo.join(channelName);

        channel.here((users) => {
            if (!cancelled) setMembers(users);
        });

        channel.joining((user) => {
            if (!cancelled) setMembers((prev) => [...prev, user]);
        });

        channel.leaving((user) => {
            if (!cancelled) setMembers((prev) => prev.filter((u) => u.id !== user.id));
        });

        return () => {
            cancelled = true;
            try { echo.leave(channelName); } catch {}
        };
    }, [channelName]);

    return members;
}
