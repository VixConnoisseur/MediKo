// vite.config.js
import { defineConfig } from "vite";
import { resolve } from "path";

export default defineConfig({
  root: "./",
  publicDir: "public",
  build: {
    outDir: "public/build",
    emptyOutDir: true,
    rollupOptions: {
      input: {
        app: "./assets/js/app.js",
        css: "./assets/css/app.css",
      },
    },
  },
  server: {
    port: 3000,
    open: "/",
  },
});
