import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const routes = [
    {
        path: '/login',
        name: 'login',
        component: () => import('../views/Login.vue'),
        meta: { guestOnly: true },
    },
    {
        path: '/admin',
        name: 'admin.forms',
        component: () => import('../views/AdminFormsList.vue'),
        meta: { requiresAuth: true },
    },
    {
        path: '/admin/users',
        name: 'admin.users',
        component: () => import('../views/AdminUsersList.vue'),
        meta: { requiresAuth: true },
    },
    {
        path: '/admin/forms/:id/edit',
        name: 'admin.forms.edit',
        component: () => import('../views/FormBuilder.vue'),
        meta: { requiresAuth: true },
        props: true,
    },
    {
        path: '/admin/forms/:id/responses',
        name: 'admin.forms.responses',
        component: () => import('../views/ResponsesPage.vue'),
        meta: { requiresAuth: true },
        props: true,
    },
    {
        path: '/forms/:id',
        name: 'public.form',
        component: () => import('../views/PublicForm.vue'),
        props: true,
    },
    {
        path: '/',
        redirect: '/forms/1',
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

router.beforeEach(async (to) => {
    const authStore = useAuthStore();

    if (!authStore.checked) {
        await authStore.fetchUser();
    }

    if (to.meta.requiresAuth && !authStore.isAuthenticated) {
        return { name: 'login', query: { redirect: to.fullPath } };
    }

    if (to.meta.guestOnly && authStore.isAuthenticated) {
        return { name: 'admin.forms' };
    }

    return true;
});

export default router;
