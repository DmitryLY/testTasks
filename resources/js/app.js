import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();


import { createApp } from 'vue/dist/vue.esm-bundler';

import rabit from '/resources/components/test_tasks.vue';

import router from "./router";
import store from "./store";

const app = createApp({
components: {
    'rabit' : rabit,
}
});

app.use(router);
app.use(store);
app.mount('#app');

