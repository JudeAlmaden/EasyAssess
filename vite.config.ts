import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import path from 'path';
import tailwindcss from '@tailwindcss/vite';
import { defineConfig } from 'vite';
import mkcert from 'vite-plugin-mkcert'

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/js/app.ts',
                'resources/css/app.css',
                'resources/js/app.js'
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
        host: '192.168.1.121' 				
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