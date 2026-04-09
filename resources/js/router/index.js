import { createWebHashHistory, createRouter } from "vue-router";
import Analysis from '../components/Analysis.vue';
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
        path: '/analysis',
        name: 'Analysis',
        component: Analysis
    },
];

const router = createRouter({
    history: createWebHashHistory(),
    routes
});

export default router;
