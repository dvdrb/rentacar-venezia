import { defineConfig } from 'vite';
import { resolve } from 'node:path';

export default defineConfig({
  build: {
    outDir: 'theme/rentacar-venezia-v2/assets/dist',
    emptyOutDir: true,
    manifest: true,
    rollupOptions: {
      input: resolve(__dirname, 'theme/rentacar-venezia-v2/assets/src/ts/main.ts'),
      output: { entryFileNames: 'js/[name]-[hash].js', assetFileNames: 'css/[name]-[hash][extname]' }
    }
  }
});
