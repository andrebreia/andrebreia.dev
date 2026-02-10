// @ts-check
import { defineConfig } from 'astro/config';
import tailwindcss from '@tailwindcss/vite';
import mdx from '@astrojs/mdx';
import icon from 'astro-icon';
import sitemap from '@astrojs/sitemap';

// https://astro.build/config
export default defineConfig({
  site: 'https://andrebreia.dev',
  vite: {
    plugins: [tailwindcss()],
  },
  integrations: [mdx(), icon(), sitemap()],
});