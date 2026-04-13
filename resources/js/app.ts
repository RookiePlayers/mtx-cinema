import { createInertiaApp } from '@inertiajs/vue3';
import { createApp, h } from 'vue';
import type { Component, DefineComponent } from 'vue';
import Navbar from '@/Components/common/Navbar.vue';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';
type InertiaPage = DefineComponent & {
    layout?: Component | Component[];
};

const pages = import.meta.glob<{ default: InertiaPage }>('./pages/**/*.vue');

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: async (name) => {
        const page = pages[`./pages/${name}.vue`];

        if (!page) {
            throw new Error(`Unknown Inertia page: ${name}`);
        }

        const module = await page();
        module.default.layout = module.default.layout || Navbar;

        return module.default;
    },
    progress: {
        color: '#4B5563',
    },
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .mount(el);
    },
});
