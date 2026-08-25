export function SkeletonPulse({ className = "" }) {
    return (
        <div
            className={`animate-pulse bg-gray-200 rounded ${className}`}
        />
    );
}

export function StatCardSkeleton() {
    return (
        <div className="bg-white rounded-xl border border-gray-200 p-5">
            <div className="flex items-center justify-between mb-3">
                <SkeletonPulse className="h-4 w-20" />
                <SkeletonPulse className="w-8 h-8 rounded-lg" />
            </div>
            <SkeletonPulse className="h-8 w-12 mb-1" />
            <SkeletonPulse className="h-3 w-24" />
        </div>
    );
}

export function TaskRowSkeleton() {
    return (
        <div className="flex items-center gap-3 px-5 py-3">
            <SkeletonPulse className="w-[18px] h-[18px] rounded-[5px] flex-shrink-0" />
            <div className="flex-1 min-w-0">
                <SkeletonPulse className="h-4 w-48 mb-1.5" />
                <div className="flex items-center gap-2">
                    <SkeletonPulse className="h-3 w-16 rounded-full" />
                    <SkeletonPulse className="h-3 w-12" />
                </div>
            </div>
            <SkeletonPulse className="h-4 w-20" />
        </div>
    );
}

export function ProjectCardSkeleton() {
    return (
        <div className="bg-white rounded-xl border border-gray-200 p-5">
            <div className="flex items-start justify-between mb-3">
                <div className="flex items-center gap-2">
                    <SkeletonPulse className="w-3.5 h-3.5 rounded-full" />
                    <SkeletonPulse className="h-4 w-32" />
                </div>
                <SkeletonPulse className="h-3 w-16" />
            </div>
            <SkeletonPulse className="h-3 w-full mb-3" />
            <div>
                <div className="flex items-center justify-between mb-1.5">
                    <SkeletonPulse className="h-3 w-20" />
                    <SkeletonPulse className="h-3 w-8" />
                </div>
                <SkeletonPulse className="h-1.5 w-full rounded-full" />
            </div>
        </div>
    );
}

export function DashboardSkeleton() {
    return (
        <div className="flex gap-6">
            <div className="flex-1 min-w-0">
                <SkeletonPulse className="h-10 w-full max-w-md mb-6 rounded-xl" />
                <div className="mb-6">
                    <SkeletonPulse className="h-7 w-64 mb-2" />
                    <SkeletonPulse className="h-4 w-80" />
                </div>
                <div className="grid grid-cols-3 gap-4 mb-6">
                    <StatCardSkeleton />
                    <StatCardSkeleton />
                    <StatCardSkeleton />
                </div>
                <div className="bg-white rounded-xl border border-gray-200">
                    <div className="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                        <SkeletonPulse className="h-5 w-24" />
                        <div className="flex items-center gap-2">
                            <SkeletonPulse className="h-7 w-14 rounded-lg" />
                            <SkeletonPulse className="h-7 w-12 rounded-lg" />
                        </div>
                    </div>
                    <div className="divide-y divide-gray-100">
                        {[1, 2, 3, 4, 5].map((i) => (
                            <TaskRowSkeleton key={i} />
                        ))}
                    </div>
                </div>
            </div>
            <div className="w-80 flex-shrink-0">
                <div className="bg-white rounded-xl border border-gray-200 p-5 mb-4">
                    <SkeletonPulse className="h-5 w-20 mb-4" />
                    <div className="grid grid-cols-7 gap-1 mb-3">
                        {[1, 2, 3, 4, 5, 6, 7].map((i) => (
                            <SkeletonPulse key={i} className="h-6 w-full rounded" />
                        ))}
                    </div>
                    <div className="grid grid-cols-7 gap-1">
                        {[1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14].map((i) => (
                            <SkeletonPulse key={i} className="h-6 w-full rounded" />
                        ))}
                    </div>
                </div>
                <div className="bg-white rounded-xl border border-gray-200 p-5">
                    <SkeletonPulse className="h-5 w-16 mb-4" />
                    <div className="space-y-3">
                        {[1, 2, 3].map((i) => (
                            <div key={i} className="flex items-center gap-2">
                                <SkeletonPulse className="w-2 h-2 rounded-full" />
                                <SkeletonPulse className="h-3 w-24" />
                            </div>
                        ))}
                    </div>
                </div>
            </div>
        </div>
    );
}

export function ProjectsIndexSkeleton() {
    return (
        <div className="max-w-5xl">
            <div className="flex items-center justify-between mb-6">
                <SkeletonPulse className="h-7 w-28" />
                <SkeletonPulse className="h-9 w-32 rounded-lg" />
            </div>
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                {[1, 2, 3, 4, 5, 6].map((i) => (
                    <ProjectCardSkeleton key={i} />
                ))}
            </div>
        </div>
    );
}

export function PomodoroSkeleton() {
    return (
        <div className="max-w-5xl mx-auto">
            <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div className="lg:col-span-2">
                    <div className="bg-white rounded-xl border border-gray-200 p-8 flex flex-col items-center">
                        <SkeletonPulse className="h-10 w-48 mb-6 rounded-xl" />
                        <SkeletonPulse className="w-[280px] h-[280px] rounded-full mb-8" />
                        <SkeletonPulse className="h-12 w-40 rounded-xl mb-4" />
                        <SkeletonPulse className="h-4 w-32" />
                    </div>
                </div>
                <div>
                    <div className="bg-white rounded-xl border border-gray-200 p-5">
                        <SkeletonPulse className="h-5 w-24 mb-4" />
                        <div className="space-y-3">
                            {[1, 2, 3, 4].map((i) => (
                                <div key={i} className="flex items-center gap-3">
                                    <SkeletonPulse className="w-8 h-8 rounded-full" />
                                    <SkeletonPulse className="h-4 flex-1" />
                                </div>
                            ))}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}

export function HabitsSkeleton() {
    return (
        <div className="max-w-5xl">
            <div className="flex items-center justify-between mb-6">
                <SkeletonPulse className="h-7 w-20" />
                <SkeletonPulse className="h-9 w-32 rounded-lg" />
            </div>
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                {[1, 2, 3, 4, 5, 6].map((i) => (
                    <div key={i} className="bg-white rounded-xl border border-gray-200 p-5">
                        <div className="flex items-center gap-3 mb-4">
                            <SkeletonPulse className="w-10 h-10 rounded-full" />
                            <div>
                                <SkeletonPulse className="h-4 w-24 mb-1" />
                                <SkeletonPulse className="h-3 w-16" />
                            </div>
                        </div>
                        <div className="flex gap-1 mb-4">
                            {[1, 2, 3, 4, 5, 6, 7].map((j) => (
                                <SkeletonPulse key={j} className="w-8 h-8 rounded" />
                            ))}
                        </div>
                        <SkeletonPulse className="h-3 w-full rounded-full" />
                    </div>
                ))}
            </div>
        </div>
    );
}
