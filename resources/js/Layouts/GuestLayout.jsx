import ApplicationLogo from '@/Components/ApplicationLogo';

export default function GuestLayout({ children }) {
    return (
        <div className="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gradient-to-br from-brand-50 via-white to-brand-50">
            <div className="flex flex-col items-center">
                <a href="/" className="flex items-center gap-3">
                    <ApplicationLogo className="w-12 h-12 text-brand-500" />
                    <span className="text-2xl font-bold text-gray-900">Taskate</span>
                </a>
            </div>

            <div className="w-full sm:max-w-md mt-6 px-6 py-8 bg-white shadow-lg overflow-hidden sm:rounded-2xl border border-gray-100">
                {children}
            </div>
        </div>
    );
}
