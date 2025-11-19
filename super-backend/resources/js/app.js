import './bootstrap';
import { createApp } from 'vue';
import ApiDocs from './components/ApiDocs.vue';

const app = createApp(ApiDocs);
app.mount('#app');
