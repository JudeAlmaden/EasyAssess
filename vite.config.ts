import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import path from 'path';
import tailwindcss from '@tailwindcss/vite';
import { defineConfig } from 'vite';


export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/js/app.ts',
                'resources/css/app.css',
                'resources/css/omr.css',
                'resources/js/app.js',
                'resources/js/jquery-preload.js',
                'resources/js/blade.js'
            ],
            ssr: 'resources/js/ssr.ts',

            refresh: true,
        }),
        // mkcert(),
        tailwindcss(),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    server: {
        host: '0.0.0.0',
        port: 8880,
        strictPort: true,
        hmr: {
            host: '127.0.0.1'
        }
    },
    define: {
        global: 'globalThis',
    },
    resolve: {
        alias: {
            '@': path.resolve(__dirname, './resources/js'),
        },
    },
});