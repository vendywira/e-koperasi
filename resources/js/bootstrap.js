import axios from 'axios';
import { csrfToken } from '@/lib/csrf';

window.axios = axios;

/**
 * Default headers untuk semua Axios request.
 */
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

/**
 * Global Axios request interceptor:
 * Mengisi header X-CSRF-TOKEN secara otomatis dari meta tag / cookie.
 * Disable untuk request ke domain lain (cross-origin).
 */
window.axios.interceptors.request.use((config) => {
    // Hanya tambahkan CSRF untuk request ke origin yang sama
    if (config.url && (config.url.startsWith('/') || config.url.startsWith(window.location.origin))) {
        const token = csrfToken();
        if (token) {
            config.headers['X-CSRF-TOKEN'] = token;
        }
    }
    return config;
});
