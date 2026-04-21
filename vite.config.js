import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import laravel from 'laravel-vite-plugin'
import { fileURLToPath, URL } from 'node:url'

export default defineConfig(({ mode }) => {
    const isVitest = mode === 'test' || !!process.env.VITEST

    return {
        plugins: [
            !isVitest &&
                laravel({
                    input: [
    'resources/css/app.css',
    'resources/js/main.js',
    'resources/js/public/main.js',
    'resources/css/filament/admin/theme.css',
],
                    refresh: true,
                }),
            vue(),
        ].filter(Boolean),

        resolve: {
            alias: {
                '@': fileURLToPath(new URL('./resources/js', import.meta.url)),
            },
        },

        test: {
            environment: 'jsdom',
            globals: true,
            include: [
                'resources/js/**/*.{test,spec}.js',
                'resources/js/**/__tests__/**/*.js',
            ],
            exclude: [
                'tests/e2e/**',
                'node_modules/**',
                'vendor/**',
            ],
        },
    }
})