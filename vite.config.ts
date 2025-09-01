import tailwindcss from "@tailwindcss/vite";
import path from "path";
import { defineConfig } from "vite";
import solidPlugin from "vite-plugin-solid";
import eslint from "vite-plugin-eslint";
import Pages from "vite-plugin-pages";

export default defineConfig({
  plugins: [
    solidPlugin(),
    tailwindcss(),
    Pages({ dirs: ["src/pages"] }),
    eslint({ failOnError: false }),
  ],
  server: {
    port: 3000,
  },
  build: {
    target: "esnext",
  },
  resolve: {
    alias: {
      "@": path.resolve(__dirname, "./src"),
    },
  },
});
