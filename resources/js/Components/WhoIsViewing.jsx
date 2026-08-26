import { usePage } from '@inertiajs/react';
import usePresence from '@/hooks/usePresence';

export default function WhoIsViewing({ projectId }) {
    const { props } = usePage();
    const currentUserId = props.auth.user.id;
    const viewers = usePresence(`presence:project.${projectId}`);
    const others = viewers.filter((u) => u.id !== currentUserId);

    if (others.length === 0) return null;

    return (
        <div className="flex items-center gap-1.5 text-[11px] text-gray-400">
            <span className="w-1.5 h-1.5 rounded-full bg-green-400 animate-pulse" />
            <span>
                {others.length === 1
                    ? `${others[0].name} is viewing`
                    : others.slice(0, 3).map((u) => u.name).join(', ') + (others.length > 3 ? ` +${others.length - 3}` : '') + ' viewing'}
            </span>
        </div>
    );
}
