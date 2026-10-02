# andrebreia.dev

André Breia's website, built with **Statamic 6 Solo, Laravel 13, Blade, Alpine.js, and Tailwind CSS 4**. The Astro frontend's layout, fonts, icons, content, URLs, and social images are preserved.

## Local development

Use PHP 8.5 (with BCMath, cURL, DOM/XML, Exif, GD, Intl, Mbstring, SQLite, and Zip), Composer 2, and Node ≥22.18. Laravel Herd is suitable on macOS/Windows. No database or queue worker is required: content, users, sessions, and caches use local files.

```sh
composer install
cp .env.example .env # first installation only; don't overwrite an existing .env
php artisan key:generate
npm ci
npm run build
php please make:user # create your local super-user interactively
php artisan serve
```

Open the server URL printed by Artisan; sign in at `/cp`. For CSS/JS development, run `npm run dev` in a second terminal. Run `npm run build` at least once first, and again after changing social-image content. Normal content edits and uploaded images appear immediately without a build.

The control panel is enabled **only when `APP_ENV=local`**. No account, password, or application key is committed. `users/*.yaml` and `.env` are intentionally ignored. Create a local user on each development machine.

## Editing and publishing

- **Collections → Pages:** homepage, about, uses, article/service landing pages, and 404 copy. Imported layouts remain fixed in Blade; new pages use a generic heading, optional introduction, and Bard body.
- **Collections → Articles, Projects, Services:** entries, metadata, links, dates, and rich text. Bard supports headings, lists, links, tables, and horizontal rules. Two imported articles remain drafts.
- **Globals:** site identity, availability, contact/social links, main navigation, experience, certifications, testimonials, CTA, and interface labels. Testimonials remain hidden unless enabled on the homepage.
- **Assets → Images:** local uploads in `public/images/`, including asset metadata in `.meta/` directories. Logo and portrait use Glide resizing; existing project thumbnails retain their URLs.

Edit and save locally, inspect the frontend, then:

```sh
npm run build
php artisan test
git status
git add content resources public/images
git commit -m "Update website content"
# Push through your normal review/deployment workflow.
```

Include any asset metadata changes. Never commit `.env`, `users/*.yaml`, generated caches, or uploaded secrets. Git is the content history; Statamic Pro's Git integration/revisions are not required or enabled.

## Laravel Cloud

Follow [Statamic on Cloud](https://laravel.com/cloud/docs/knowledge-base/statamic-on-cloud). Production is **read-only**: edits and uploads arrive through Git deployments. The image container deliberately uses bundled `public/images` files, so this workflow needs no object-storage bucket. If production editing is introduced later, both content and uploads need persistent storage before enabling it.

Select PHP 8.5 and Node 22.18+ (or Node 24 LTS). Attach a **Cloud cache** and select its Redis connection as the application cache store. No application database is needed.

Production environment:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://andrebreia.dev
STATAMIC_PRO_ENABLED=false
STATAMIC_STACHE_WATCHER=false
STATAMIC_STATIC_CACHING_STRATEGY=half
CACHE_STORE=redis
SESSION_DRIVER=file
QUEUE_CONNECTION=sync
```

Set a unique production `APP_KEY` through Cloud's secret/environment settings. Use Cloud's injected Redis connection variables and an environment-specific cache prefix. Do not copy your development `.env` or user file to production.

Build commands:

```sh
composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader
npm ci
npm run build
```

Deploy commands:

```sh
php please stache:refresh
php please static:clear
```

Stache refresh must use the shared Redis cache: Cloud discards filesystem changes made by deploy commands. Half-measure static caching also uses this shared cache and supports multiple replicas. Full-measure caching is an alternative only with autoscaling set to **None**; it stores HTML on each instance's ephemeral disk. There are no migrations to run for this site's file-based workflow.

Cloud should serve `public/`. Check `/up`, the sitemap at `/sitemap-index.xml`, and that `/cp` returns 404 before launch. The production-only Umami script and existing event names are preserved. This repository does not create Cloud resources or deploy automatically by itself.

## Verification and structure

```sh
vendor/bin/pest
vendor/bin/pint --test
npm run build
```

Pest 5 runs the PHP suite (`php artisan test` also works). The migration regression fixture in `tests/Fixtures/astro-baseline.json` was captured from the original Astro production build. It checks all 20 original pages' content, links, and metadata. Update its affected expectations deliberately when changing that content. Tests also cover CP link values, optional booking links, blank Bard nodes, new pages, cache-store selection/invalidation, drafts, pagination, and Solo/read-only configuration. Articles use their title for SEO; services retain a separate SEO title override.

`content/` stores entries/globals; `resources/blueprints/` defines editing forms; `resources/views/` contains only Blade frontend templates; `resources/js/app.js` owns Alpine interactions. `scripts/` rebuilds icons and the 15 initial social images from committed content, with fonts installed from the npm lockfile rather than fetched at runtime.

## Amp orbs

`.agents/setup` installs PHP/extensions, Composer and locked dependencies, prepares a development `.env`, and builds assets. Repeated runs preserve dependencies and the existing application key. `.agents/resume` performs no installations or authentication. Start the supervised preview with `amp orb services ensure`; use the returned portal link. Create a local control-panel user only when needed, never in the reusable setup snapshot.

Laravel Boost is a development dependency. Amp loads its local MCP server from `.amp/settings.json`; approve `laravel-boost` when prompted. Use Boost's `search-docs` tool with `packages: ["statamic/cms"]` for Statamic documentation. `boost.json` records the selected agent, Statamic guidelines, and skills. Refresh generated `AGENTS.md` and `.agents/skills/` with `php artisan boost:update --no-interaction` after dependency updates. Inertia guidance is excluded because it is only a control-panel dependency; the public frontend uses Blade and Alpine.
