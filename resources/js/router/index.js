import { createWebHashHistory, createRouter } from "vue-router";
import Analysis from '../components/Analysis.vue';

const routes = [
    {
        path: '/',
        redirect: '/analysis'
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
