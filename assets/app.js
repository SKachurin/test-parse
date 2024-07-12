import { createApp } from 'vue';
import ImageParser from './src/vue/components/ImageParser.vue';

const app = createApp({});

app.component('image-parser', ImageParser);

app.mount('#app');