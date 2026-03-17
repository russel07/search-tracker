import ElementPlus from 'element-plus';
import 'element-plus/dist/index.css';
import { createApp } from 'vue';
import router from './router';

import Admin from './components/Admin.vue';
const app = createApp( Admin );

app.use(router);
app.use(ElementPlus);

app.mount('#search_tracker_admin_app');