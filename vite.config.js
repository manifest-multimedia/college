import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
    ],
    build: {
        rollupOptions: {
            external: ['laravel-echo', 'pusher-js'],
            output: {
                globals: {
                    'laravel-echo': 'Echo',
                    'pusher-js': 'Pusher'
                }
            }
        }
    },
    resolve: {
        alias: {
            'laravel-echo': 'laravel-echo',
            'pusher-js': 'pusher-js'
        }
    },
});
