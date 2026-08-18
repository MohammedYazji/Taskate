import { useState } from 'react';

export default function AuthenticatedLayout({ header, children }) {
    const [sidebarOpen, setSidebarOpen] = useState(true);

    return (
        <div className="bg-gray-50 h-screen flex overflow-hidden">
            <div className="flex flex-col flex-1 overflow-hidden min-w-0">
                {header && (
                    <header className="bg-white border-b border-gray-200">
                        <div className="px-6 py-4">
                            {header}
                        </div>
                    </header>
                )}
                <main className="flex-1 overflow-y-auto p-6">
                    {children}
                </main>
            </div>
        </div>
    );
}
