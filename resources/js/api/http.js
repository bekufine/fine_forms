import axios from 'axios';

const http = axios.create({
    baseURL: '/api',
    withCredentials: true,
    withXSRFToken: true,
    headers: {
        Accept: 'application/json',
    },
});

export async function ensureCsrfCookie() {
    await axios.get('/sanctum/csrf-cookie', { baseURL: '/', withCredentials: true });
}

export default http;
