// @ts-check
import { defineConfig } from 'astro/config';
import { readdirSync, readFileSync } from 'node:fs';
import { join } from 'node:path';
import tailwindcss from '@tailwindcss/vite';
import mdx from '@astrojs/mdx';
import icon from 'astro-icon';
import sitemap from '@astrojs/sitemap';

const articleLastmod = new Map(
  readdirSync('./src/content/articles')
    .filter((file) => file.endsWith('.mdx'))
    .map((file) => {
      const source = readFileSync(join('./src/content/articles', file), 'utf8');
      const [, frontmatter = ''] = source.match(/^---\n([\s\S]*?)\n---/) ?? [];
      const isDraft = /^draft:\s*true\s*$/m.test(frontmatter);
      const date = frontmatter.match(/^date:\s*["']?([^"'\n]+)["']?\s*$/m)?.[1];
      const dateModified = frontmatter.match(/^dateModified:\s*["']?([^"'\n]+)["']?\s*$/m)?.[1];

      if (isDraft || !date) {
        return null;
      }

      const slug = file.replace(/\.mdx$/, '');
      return [`/articles/${slug}`, dateModified ?? date];
    })
    .filter(Boolean)
);

// https://astro.build/config
export default defineConfig({
  site: 'https://andrebreia.dev',
  trailingSlash: 'never',
  vite: {
    plugins: [tailwindcss()],
  },
  integrations: [
    mdx(),
    icon(),
    sitemap({
      serialize(item) {
        const pathname = new URL(item.url).pathname;
        const lastmod = articleLastmod.get(pathname);

        if (lastmod) {
          return { ...item, lastmod };
        }

        return item;
      },
    }),
  ],
});
