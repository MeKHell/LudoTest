import '../css/app.css';

import { createInertiaApp } from '@inertiajs/react';
import React, { StrictMode } from 'react';
import { createRoot } from 'react-dom/client';
import { initializeTheme } from './hooks/use-appearance';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';


createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) => {
        const pages = import.meta.glob<() => never>('./pages/**/*.tsx');
        return pages[`./pages/${name}.tsx`]() ?? pages[`./pages/404.tsx`]();
    },
    setup({ el, App, props }) {
        const root = createRoot(el);

        root.render(
            <StrictMode>
                <App {...props} />
            </StrictMode>,
        );
    },
    progress: {
        color: '#4B5563',
    },
    defaults: {
        form: {
            recentlySuccessfulDuration: 4000,
        },
    },
});

// This will set light / dark mode on load...
initializeTheme();
