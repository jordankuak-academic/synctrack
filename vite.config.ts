import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import { bunny } from "laravel-vite-plugin/fonts";
import { fileURLToPath, URL } from "node:url";

export default defineConfig({
  resolve: {
    alias: {
      "@": fileURLToPath(new URL("./resources/scripts", import.meta.url)),
    },
  },
  plugins: [
    laravel({
      fonts: [
        bunny("Inter", {
          weights: [400, 500, 600, 700, 800, 900],
        }),
      ],
      input: [
        "resources/styles/app.scss", 
        "resources/scripts/app.ts",
      ],
      refresh: true,
    }),
  ],
  server: {
    watch: {
      ignored: ["**/storage/framework/views/**"],
    },
  },
});
