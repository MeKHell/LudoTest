/* @refresh reload */
import '../css/app.css'
import './bootstrap';
import { createInertiaApp } from 'inertia-adapter-solid'
import { render } from 'solid-js/web'
import { ThemeProvider } from './contexts/ThemeContext'

// @ts-expect-error ok
createInertiaApp({
    title: (title?: string) => title || 'Laravel',
    resolve(name) {
        const pages = import.meta.glob('./Pages/**/*.{tsx,jsx}', { eager: true })
        const page = pages[`./Pages/${name}.tsx`]
        if (!page) {
            throw new Error(`Page ${name} not found`)
        }

        // @ts-expect-error ok
        return page.default || page
    },
    setup({ el, App, props }) {
        render(() => (
            <ThemeProvider>
                <App {...props} />
            </ThemeProvider>
        ), el)
    },
})
