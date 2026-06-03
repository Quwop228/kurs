import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    // Настройки dev-сервера для работы в Docker (на production-сборку не влияют)
    server: {
        host: '0.0.0.0',
        port: 5173,
        strictPort: true,
        // HMR-клиент в браузере подключается к этому хосту
        hmr: {
            host: 'localhost',
        },
        // Polling нужен, чтобы изменения файлов виделись через bind-mount (особенно на Windows)
        watch: {
            usePolling: true,
        },
    },
    plugins: [
        laravel({
            input: 'resources/js/app.js',
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
});
