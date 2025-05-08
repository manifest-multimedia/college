import axios from 'axios';
window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allows your team to easily build robust real-time web applications.
 */

if (typeof import.meta.env.VITE_PUSHER_APP_KEY === 'string') {
    // Only import and initialize Echo if Pusher configuration is available
    try {
        // Import synchronously to ensure they're available in the bundle
        import('pusher-js')
            .then((PusherModule) => {
                const Pusher = PusherModule.default;
                window.Pusher = Pusher;
                
                return import('laravel-echo');
            })
            .then((EchoModule) => {
                const Echo = EchoModule.default;
                window.Echo = new Echo({
                    broadcaster: 'pusher',
                    key: import.meta.env.VITE_PUSHER_APP_KEY,
                    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER ?? 'mt1',
                    wsHost: import.meta.env.VITE_PUSHER_HOST ? import.meta.env.VITE_PUSHER_HOST : `ws-${import.meta.env.VITE_PUSHER_APP_CLUSTER}.pusher.com`,
                    wsPort: import.meta.env.VITE_PUSHER_PORT ?? 80,
                    wssPort: import.meta.env.VITE_PUSHER_PORT ?? 443,
                    forceTLS: (import.meta.env.VITE_PUSHER_SCHEME ?? 'https') === 'https',
                    enabledTransports: ['ws', 'wss'],
                });
            })
            .catch((error) => {
                console.error('Failed to initialize Laravel Echo:', error);
            });
    } catch (e) {
        console.warn('Laravel Echo could not be initialized. Pusher configuration might be missing.');
    }
}
