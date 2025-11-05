import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',  // <-- ESTA LÍNEA ES LA QUE SEGURO TE FALTA
                'resources/js/app.js',
            ],
            refresh: true,
        }),
    ],
});