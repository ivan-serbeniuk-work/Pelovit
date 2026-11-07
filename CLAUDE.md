# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project

**Pelovit** — a Ukrainian cosmetics e-commerce site, split into two apps in one repo:

- **Backend** (repo root) — Laravel 13 (PHP 8.3). Serves a JSON API under `/api` and the
  **MoonShine 4** admin panel under `/admin`. No public Blade storefront: the only Blade views
  left are the admin layout and the LiqPay redirect page.
- **Frontend** (`frontend/`) — Nuxt 4 + Vue 3 SSR storefront, Pinia for state, SCSS + Bootstrap 5
  (CDN) for styles. Also builds as an Android app via Capacitor (`BUILD_TARGET=app`), where SSR is
  off and the WebView talks to the production backend directly.

The site is Ukrainian-language (`lang="uk"`). Comments and commit messages are in Ukrainian.

## Commands

**First-time setup** (installs composer + npm deps for both apps, generates key, migrates):
```bash
composer run setup
```

**Dev — everything at once** (PHP server :8000, queue, pail logs, Nuxt :3000):
```bash
composer run dev
# backend only: composer run dev:back
# frontend only: composer run dev:front
```
Storefront is http://localhost:3000, API and admin http://localhost:8000.

**Tests** (PHPUnit 12, SQLite in-memory — see `phpunit.xml`):
```bash
composer run test
php artisan test tests/Feature/Api/OrderApiTest.php   # single file
```

**Lint (Laravel Pint):**
```bash
./vendor/bin/pint
./vendor/bin/pint --test   # check only
```

**Database** (MySQL/MariaDB in dev, `pelovit_db` on 127.0.0.1:3306):
```bash
php artisan migrate
php artisan migrate:fresh --seed
php artisan tinker
php artisan moonshine:user      # create an admin user
```
`database/database.sqlite` is a leftover from the early SQLite phase and is not used.

## Architecture

### Data model

Sixteen Eloquent models in `app/Models`. The core ones:

- **Category** — self-referencing tree (`parent_id`, `sort_order`); only roots show in the catalog
  sidebar, children are eager-loaded via `with('children')`.
- **Product** — belongs to a category. Auto-generates a unique `slug` from `name` on create.
  `image` is the thumbnail, `images` a JSON gallery array, `video_url` an optional YouTube link
  (`youtube_id` accessor). `old_price` drives the computed `discount_percent` accessor;
  `is_featured` surfaces it on the homepage. Full SEO column set (`meta_*`, `og_*`, `no_index`).
- **Order / PromoCode** — checkout. Orders carry Nova Poshta city/warehouse and LiqPay payment
  state; `OrderTrackController` exposes a public status page by signed token.
- **Setting** — key/value store read through `Setting::allKeyed()`, cached with
  `Cache::rememberForever('settings_all')`. **Any write must flush that cache key.**
- Content models: `Post` (journal), `Banner`, `Faq`, `Testimonial`, `TeamMember`, `BrandCase`,
  `CalculatorOption` / `CalculatorTier` (contract-manufacturing calculator), `Lead`, `Subscriber`.

### API (`routes/api.php`)

One thin controller per page in `app/Http/Controllers/Api`, each returning exactly the payload its
Nuxt page needs: `/home`, `/catalog`, `/products/{slug}`, `/journal`, `/journal/{slug}`,
`/contacts`, `/contract`, `/calculator`, `/settings`. Writes: `/orders`, `/promo/validate`,
`/leads`, `/subscribe`. Integrations: `/nova-poshta/cities|warehouses`, `/liqpay/callback`.
`/admin/edit-link` is the odd one out — it uses the `web` middleware because it needs the
MoonShine session cookie to decide whether to show the “edit in admin” button.

The non-`Api` controllers in `app/Http/Controllers` (`CatalogController`, `ProductController`,
`PostController`, `OrderController`, `WishlistController`) are dead code from the Blade era.

### `routes/web.php`

Not a storefront — just admin-adjacent endpoints: the signed `/pay/{order}` route (Capacitor opens
LiqPay in an external browser, which can only GET, so this page self-submits the POST form) and
CSV exports for orders/leads/subscribers.

### Services

`app/Services/LiqPayService.php` (checkout payload + signature) and `OrderNotifier.php`
(customer + admin mail via Resend, mailables in `app/Mail`).

### Admin (MoonShine)

`app/MoonShine/Resources/<Model>/` — one resource plus its form/index pages per model.
`app/MoonShine/Pages/SettingsPage.php` edits the `Setting` key/value table. Route prefix comes from
`MOONSHINE_ROUTE_PREFIX` (`admin`). Admin tests live in `tests/Feature/Admin`.

### Frontend (`frontend/app/`)

File-based routes in `pages/` mirror the site map (`index`, `catalog/`, `product/`, `cart`,
`order/`, `journal/`, `masters`, `opt`, `kontractne-vyrobnyctvo`, `wishlist`, …), one `layouts/
default.vue`, Pinia stores for `cart` and `wishlist` (persisted client-side), and composables
wrapping each API concern (`useProduct`, `useSettings`, `useNovaPoshta`, `useSeoPage`,
`useAdminEditLink`, `useAsset`, `useToast`). Icons are inline SVG in `<AppIcon>` — no Font Awesome.

`nuxt.config.ts` exposes `apiBase`, `assetBase`, `siteUrl` via `runtimeConfig.public`, each with a
dev default pointing at `localhost:8000` and an app/prod default pointing at `develop-site.online`.
Build images through `useAsset()` rather than hardcoding a host.

### Styling: tokens, not literals

`assets/sass/_variables.scss` is the single source for color, type, radius, shadow and spacing.
Every partial starts with `@use 'variables' as *;` and uses the tokens — **do not write a new hex,
`border-radius` or `font-size` literal into a partial.** If a value is genuinely missing, add a
token first. Scoped component styles are plain CSS and cannot see Sass variables, so `_base.scss`
re-exports the main tokens as CSS custom properties on `:root` (`--brand-700`, `--accent`,
`--text-muted`, …) — use `var(--token)` there.

Two accent colors look alike and are not interchangeable: `$accent` (#1A7B9E) is the one for
buttons and anything with text on it; `$accent-light` (#69BDDB) is decoration only — its contrast
with white is 2.12:1 and it fails WCAG as a button. Small grey text belongs on `$text-muted` or
`$text-subtle`; the lighter neutrals are for borders, placeholders and disabled states.

What is deliberately still literal: ~18 one-off colors in the contract-manufacturing infographic,
plus black in shadows and overlays. Breakpoints are tokenized only where they matched
`$bp-sm/md/lg`; the custom ones are tied to specific blocks and were left alone.

### Image storage

Uploads live in `public/products/`, `public/categories/`, `public/images/` (gitignored, mounted as
Docker volumes in production) and are referenced by relative path. `deploy/make-webp.sh` generates
WebP variants.

## Deployment

Docker Compose: `db` (MariaDB 11) + `laravel` + `nuxt`, behind host nginx which routes `/api`,
`/admin`, `/vendor`, `/storage` and the upload dirs to Laravel and everything else to Nuxt SSR.
Images are built by GitHub Actions (`.github/workflows/deploy.yml`) and pushed to GHCR; the server
only pulls. Migrations run from the `laravel` entrypoint on start. Full runbook in `DEPLOY.md`.
