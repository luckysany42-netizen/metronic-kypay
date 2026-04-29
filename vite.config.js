import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'
import vue from '@vitejs/plugin-vue'

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/main.ts'],
            refresh: true,
        }),
        vue(),
    ],
    define: {
        'import.meta.env.VITE_APP_API_URL': JSON.stringify(process.env.VITE_APP_API_URL || '/api'),
    },
})