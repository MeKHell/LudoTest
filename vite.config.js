import { defineConfig } from 'vite'
import solid from 'vite-plugin-solid'
import laravel from 'laravel-vite-plugin'
import tailwindcss from '@tailwindcss/vite'

export default defineConfig({
  plugins: [
    solid(),
    laravel({
      input: ['resources/js/app.tsx'],
      refresh: true,
    }),
    tailwindcss(),
  ],
  resolve: {
    alias: {
      '@': '/resources/js',
    },
  },
  optimizeDeps: {
    exclude: ['@solidjs/meta'],
  },
})
