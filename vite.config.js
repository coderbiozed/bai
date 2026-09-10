import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
            fonts: [
                bunny('Archivo Black', {
                    weights: [400],
                    optimizedFallbacks: false,
                }),
                bunny('Bricolage Grotesque', {
                    weights: [500, 600, 700, 800],
                    optimizedFallbacks: false,
                }),
                bunny('Space Grotesk', {
                    weights: [400, 500, 600, 700],
                    optimizedFallbacks: false,
                }),
                bunny('Fraunces', {
                    weights: [500, 600, 700],
                    optimizedFallbacks: false,
                }),
                bunny('Syne', {
                    weights: [700, 800],
                    optimizedFallbacks: false,
                }),
            ],
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
