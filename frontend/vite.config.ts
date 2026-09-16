import { defineConfig } from 'vitest/config';
import react from '@vitejs/plugin-react';

export default defineConfig({
  plugins: [react()],
  server: {
    port: 5173,
    proxy: Object.fromEntries(['/api', '/login', '/logout', '/sanctum'].map(path => [path, {
      target: process.env.API_PROXY_TARGET || 'http://127.0.0.1:8080',
      changeOrigin: true,
    }])),
  },
  test: {
    environment: 'jsdom',
    setupFiles: ['./tests/components/setup.ts'],
    include: ['tests/components/**/*.test.{ts,tsx}'],
  },
});
