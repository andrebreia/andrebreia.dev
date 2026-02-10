# andrebreia.dev

Personal website and portfolio for Andre Breia, freelance full-stack developer.

Built with [Astro](https://astro.build), [Tailwind CSS v4](https://tailwindcss.com), and [MDX](https://mdxjs.com).

## Tech stack

- **Framework**: Astro 5
- **Styling**: Tailwind CSS v4 (CSS-based config)
- **Content**: MDX collections for articles and projects
- **Typography**: Inter (body) + Geist (headings), variable fonts
- **Icons**: astro-icon with Iconify (Solar, Lucide, Simple Icons)
- **OG images**: Auto-generated at build time via satori + resvg
- **Sitemap**: Auto-generated via @astrojs/sitemap

## Project structure

```
src/
  components/       # Astro components
  content/
    articles/       # MDX blog posts
    projects/       # MDX project pages
  data/             # Static data (site config, experience, services, etc.)
  layouts/          # BaseLayout
  lib/              # Utilities (OG image generation)
  pages/            # Routes
    og/             # Dynamic OG image endpoints
    articles/       # Article listing + detail pages
    projects/       # Project detail pages
  styles/           # Global CSS + Tailwind config
public/
  fonts/            # Inter + Geist variable fonts
  images/           # Static images and logos
```

## Commands

| Command           | Action                                       |
| :---------------- | :------------------------------------------- |
| `npm install`     | Install dependencies                         |
| `npm run dev`     | Start dev server at `localhost:4321`         |
| `npm run build`   | Build production site to `./dist/`           |
| `npm run preview` | Preview the build locally before deploying   |