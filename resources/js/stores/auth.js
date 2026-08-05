import { defineStore } from 'pinia';
import http, { ensureCsrfCookie } from '../api/http';

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: null,
        checked: false,
    }),

    getters: {
        isAuthenticated: (state) => state.user !== null,
    },

    actions: {
        async fetchUser() {
            try {
                const { data } = await http.get('/user');
                this.user = data.user;
            } catch (error) {
                this.user = null;
            } finally {
                this.checked = true;
            }
        },

        async login(credentials) {
            await ensureCsrfCookie();
            const { data } = await http.post('/login', credentials);
            this.user = data.user;
            this.checked = true;
        },

        async register(payload) {
            await ensureCsrfCookie();
            const { data } = await http.post('/register', payload);
            this.user = data.user;
            this.checked = true;
        },

        async logout() {
            await http.post('/logout');
            this.user = null;
        },

        async updateProfile(payload) {
            const { data } = await http.patch('/user', payload);
            this.user = data.user;
            return data.user;
        },
    },
});
