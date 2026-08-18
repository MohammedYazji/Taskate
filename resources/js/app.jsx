import '../css/app.css';

import { createInertiaApp } from '@inertiajs/react';
import { createRoot } from 'react-dom/client';
import AuthenticatedLayout from './Layouts/AuthenticatedLayout.jsx';

const appName = import.meta.env.VITE_APP_NAME || 'Taskate';

createInertiaApp({
    title: (title) => title ? `${title} - ${appName}` : appName,
    resolve: (name) => {
        const pages = import.meta.glob('./Pages/**/*.jsx', { eager: true });
        let page = pages[`./Pages/${name}.jsx`];

        page.default.layout = page.default.layout || ((p) => <AuthenticatedLayout>{p}</AuthenticatedLayout>);

        return page;
    },
    setup({ el, App, props }) {
        createRoot(el).render(<App {...props} />);
    },
    progress: {
        color: '#14B8A6',
    },
});
