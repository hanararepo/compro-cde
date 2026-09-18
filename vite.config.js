import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { google } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/css/public.css',
                'resources/js/public.js',
            ],
            refresh: true,
            fonts: [
                google('Golos Text', {
                    alias: 'site',
                    weights: ['400..900'],
                    display: 'swap',
                    preload: true,
                    optimizedFallbacks: false,
                }),
            ],
        }),
        tailwindcss(),
    ],
    css: {
        lightningcss: {
            errorRecovery: true,
        },
    },
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
