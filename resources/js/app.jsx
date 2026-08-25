import '../css/app.css';

import { createInertiaApp } from '@inertiajs/react';
import { createRoot } from 'react-dom/client';
import { route as ziggyRoute } from 'ziggy-js';
import AuthenticatedLayout from './Layouts/AuthenticatedLayout.jsx';
import { LoadingProvider } from './Components/LoadingContext.jsx';

const appName = import.meta.env.VITE_APP_NAME || 'Taskate';

createInertiaApp({
    title: (title) => title ? `${title} - ${appName}` : appName,
    resolve: (name) => {
        const pages = import.meta.glob('./Pages/**/*.jsx', { eager: true });
        let page = pages[`./Pages/${name}.jsx`];

        page.default.layout = page.default.layout || ((p) => <LoadingProvider><AuthenticatedLayout>{p}</AuthenticatedLayout></LoadingProvider>);

        return page;
    },
    setup({ el, App, props }) {
        window.route = (name, params, absolute) => {
            return ziggyRoute(name, params, absolute, props.initialPage.props.ziggy);
        };
        createRoot(el).render(<App {...props} />);
    },
    progress: {
        color: '#14B8A6',
    },
});
