import { defineConfig, loadEnv } from 'vite';
import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';

export default defineConfig(({ mode }) => {
  const env = loadEnv(mode, process.cwd(), '');

  return {
    plugins: [
      laravel({
        input: ['resources/css/app.css', 'resources/js/main.js', 'resources/js/public/main.js'],
        refresh: true,
      }),
      vue(),
    ],
    resolve: {
      alias: {
        '@': '/resources/js',
      },
    },
    server: {
      host: env.VITE_DEV_SERVER_HOST || '127.0.0.1',
      port: Number(env.VITE_DEV_SERVER_PORT || 5173),
      strictPort: true,
    },
    test: {
      environment: 'jsdom',
      globals: true,
      exclude: ['tests/e2e/**', 'node_modules/**', 'vendor/**'],
    },
  };
});
