import { createApp, h } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'
import { ZiggyVue } from 'ziggy-js'
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';


createInertiaApp({
  title: title => title ? `${title} - EduLink` : 'EduLink',
  resolve: name => {
    // Using Laravel's recommended helper for resolving pages.
    // This is functionally similar to your original code but is the conventional approach.
    const pages = import.meta.glob('./Pages/**/*.vue', { eager: true });
    return pages[`./Pages/${name}.vue`];
  },
  setup({ el, App, props, plugin }) {
    createApp({ render: () => h(App, props) })
      .use(plugin)
      .use(ZiggyVue) 
      .mount(el)
  },
  progress: {
        color: '#10b981', // Primary color
        showSpinner: true,
    },
})