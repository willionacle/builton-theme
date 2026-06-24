import path from 'path';
import { fileURLToPath } from 'url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
// Corrected: __dirname is already the theme folder!
const themeRoot = __dirname; 

export default {
  root: themeRoot,
  base: './',
  build: {
    outDir: 'dist',
    emptyOutDir: true,
    manifest: true,
    rollupOptions: {
      input: path.join(themeRoot, 'src/main.js'),
    },
  },
  server: {
    port: 5173,
    // Note: I also updated the origin to match your LocalWP setup
    origin: 'http://builton-local.local', 
    strictPort: true,
    hmr: {
      host: 'localhost',
      port: 5173,
    },
  },
};