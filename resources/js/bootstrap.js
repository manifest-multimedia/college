import axios from 'axios';
window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allows your team to easily build robust real-time web applications.
 */

// Dynamically import Echo and Pusher to avoid build issues
try {
    window.Pusher = await import('pusher-js').then(module => module.default);
    window.Echo = await import('laravel-echo').then(module => {
        const Echo = module.default;
        return new Echo({
            broadcaster: 'pusher',
            key: import.meta.env.VITE_PUSHER_APP_KEY,
            cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
            forceTLS: true
        });
    });
} catch (e) {
    console.error('Error loading Echo or Pusher:', e);
}
