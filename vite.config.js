import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig(({ command, mode }) => {
    const isProduction = mode === 'production';
    
    return {
        plugins: [
            laravel({
                input: [
                    'resources/css/app.css',
                    'resources/js/app.js',
                ],
                refresh: !isProduction,
            }),
        ],
        build: {
            // Production-specific build settings
            sourcemap: !isProduction,
            minify: isProduction ? 'terser' : false,
            cssMinify: isProduction,
            chunkSizeWarningLimit: 1000,
            terserOptions: isProduction ? {
                compress: {
                    drop_console: true,
                    drop_debugger: true,
                }
            } : {},
            rollupOptions: {
                // Explicitly mark problematic packages as external
                // This prevents Vite from trying to bundle them incorrectly
                external: ['laravel-echo', 'pusher-js'],
                output: {
                    // Create vendor chunks to improve caching
                    manualChunks: (id) => {
                        if (id.includes('node_modules')) {
                            return 'vendor';
                        }
                    },
                }
            }
        },
        optimizeDeps: {
            exclude: ['laravel-echo', 'pusher-js']
        },
        // Handle potential memory issues during build
        server: {
            fs: {
                strict: true,
            },
        },
    };
});
