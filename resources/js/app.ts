import '../css/app.css';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createPinia } from 'pinia';
import piniaPluginPersistedState from 'pinia-plugin-persistedstate';
import type { DefineComponent } from 'vue';
import { createApp, h } from 'vue';
import Toast, { PluginOptions, POSITION } from 'vue-toastification';
import 'vue-toastification/dist/index.css';
import { ZiggyVue } from 'ziggy-js';
import { initializeTheme } from './composables/useAppearance';

const appName = import.meta.env.VITE_APP_NAME || 'POS Application';
const options: PluginOptions = {
    position: POSITION.TOP_RIGHT,
    timeout: 3000,
    maxToasts: 1,
    newestOnTop: true,
};
const pinia = createPinia();
pinia.use(piniaPluginPersistedState);

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) => resolvePageComponent(`./pages/${name}.vue`, import.meta.glob<DefineComponent>('./pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .use(Toast, options)
            .use(pinia)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});

initializeTheme();
