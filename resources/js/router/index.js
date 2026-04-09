import { createWebHashHistory, createRouter } from "vue-router";
import Analytics from '../components/Analytics.vue';
import Settings from '../components/Settings.vue';

const routes = [
    {
        path: '/',
        redirect: '/settings'
    },
    {
        path: '/settings',
        name: 'Settings',
        component: Settings
    },
    {
        path: '/analytics',
        name: 'Analytics',
        component: Analytics
    },
];

const router = createRouter({
    history: createWebHashHistory(),
    routes
});

export default router;
