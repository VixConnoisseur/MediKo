import { defineConfig } from "vite";
import { resolve } from "path";

export default defineConfig({
  root: "./assets",
  base: "/assets/",
  publicDir: "../public",
  build: {
    outDir: "../public/build",
    emptyOutDir: true,
    manifest: true,
    rollupOptions: {
      input: {
        app: "./assets/js/app.js",
        css: "./assets/css/app.css",
      },
    },
  },
  server: {
    strictPort: true,
    port: 3000,
    proxy: {
      "^(?!/assets)": "http://localhost:8000",
    },
  },
});
