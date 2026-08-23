import ApplicationLogo from '@/Components/ApplicationLogo';

export default function Welcome() {
    return (
        <div className="min-h-screen flex flex-col items-center justify-center bg-gray-100">
            <ApplicationLogo className="w-16 h-16 text-brand-500 mb-6" />
            <h1 className="text-3xl font-bold text-gray-900 mb-2">Taskate</h1>
            <p className="text-gray-500">Inertia + React is working!</p>
        </div>
    );
}

Welcome.layout = (page) => page;
