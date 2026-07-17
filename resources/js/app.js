import './bootstrap';
import '../css/app.css';

import { createInertiaApp } from '@inertiajs/vue3';
import { createApp, h } from 'vue';

const pages = import.meta.glob('./Pages/**/*.vue', { eager: true });

createInertiaApp({
    title: (title) => (title ? `${title} - Leaderboard` : 'Leaderboard'),
    resolve: (name) => {
        const page = pages[`./Pages/${name}.vue`];

        if (!page) {
            throw new Error(`Page not found: ${name}`);
        }

        return page;
    },
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .mount(el);
    },
    progress: {
        color: '#0f766e',
    },
});
