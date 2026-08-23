import '../css/app.css';
import './bootstrap';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';

const appName = 'Domluveno';

createInertiaApp({
    title: (title) => title ? `${title} – ${appName}` : appName,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        const vueApp = createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)

        // Basic i18n translation helper
        vueApp.config.globalProperties.$t = function (key, replacements = {}) {
            let translation = this.$page?.props?.translations?.[key] || key;
            for (let r in replacements) {
                translation = translation.replace(`:${r}`, replacements[r]);
            }
            return translation;
        };

        vueApp.mount(el);
        return vueApp;
    },
    progress: {
        color: '#0F766E',
    },
});
