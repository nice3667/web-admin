import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/js/app.js',
                'resources/css/app.css'
            ],
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
    build: {
        // Generate manifest file for Laravel
        manifest: true,
        // Output directory for built assets
        outDir: 'public/build',
        // Rollup options for better optimization
        rollupOptions: {
            output: {
                // Ensure assets are properly named for caching
                entryFileNames: 'assets/[name]-[hash].js',
                chunkFileNames: 'assets/[name]-[hash].js',
                assetFileNames: 'assets/[name]-[hash].[ext]'
            }
        },
        // Optimize for production
        minify: 'terser',
        // Source maps for debugging (optional)
        sourcemap: false,
    },
    // Base path for assets
    base: '/build/',
});
