# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project

WordPress theme ("Built-On") built on **Timber v2** (Twig templating) with a **Vite + Sass + GSAP** frontend pipeline and **ACF Pro** for content. No JS framework (Alpine/React/Vue) — vanilla JS + GSAP only. This theme directory is not a git repository.

## Commands

Run from this theme directory (where `package.json`/`composer.json` live in this checkout):

- `composer install` — install Timber (PHP deps go to `vendor/`)
- `npm install` — install Vite/Sass/GSAP (JS deps go to `node_modules/`)
- `npm run dev` — start the Vite dev server (port 5173, HMR). `functions.php` auto-detects it and serves unbundled JS/CSS when `WP_DEBUG` is true.
- `npm run build` — production build via Vite; outputs to `dist/` with `dist/.vite/manifest.json`, which `functions.php` reads to enqueue hashed assets when the dev server isn't running.

No lint, test, or CI configuration exists in this repo (no ESLint/Stylelint/PHPCS/PHPUnit/GitHub Actions).

The README also documents an alternate Docker Compose-based dev setup (`npm run docker:up` / `docker:down`, expecting an outer project root with `scripts/docker-up.sh` and a root `docker-compose.yml`) — those supporting files are **not present** in this checkout, which is run via Local by Flywheel instead (Vite `server.origin` in `vite.config.js` is set to `http://builton-local.local`).

## Architecture

**Request flow:** WordPress template hierarchy picks a PHP file at theme root (`front-page.php`, `page.php`, `page-{slug}.php`, `single.php`, `index.php`) → it pulls ACF field values via `get_field()`, normalizes them into a plain array, builds a Timber context, and calls `Timber::render('{template}.twig', $context)` → Twig renders `views/{template}.twig`, which extends `views/base.twig` and includes section partials from `views/sections/`.

- **No PHP class/namespace architecture.** `composer.json` declares a `Builton\` → `src/php/` PSR-4 mapping but `src/php/` doesn't exist; everything is procedural functions prefixed `builton_`.
- **functions.php** is the bootstrap: loads `vendor/autoload.php`, calls `Timber::init()` / sets `Timber::$dirname = ['views']`, requires `inc/acf/bootstrap.php` and `inc/content-sync/bootstrap.php`, registers theme supports, Google Fonts enqueue, WebP upload mime, the `team_member` CPT, a Twig `shortcode` filter (wraps `do_shortcode`), and `builton_enqueue_assets()` (the Vite dev/prod asset switch described above).
- **ACF fields** are registered in PHP under `inc/acf/groups/{front-page,what-we-do,project}.php`, loaded via `inc/acf/bootstrap.php` on `acf/init`. ACF Local JSON sync points at `acf-json/` (field **definitions**, exported automatically by ACF — keep these committed).
- **Page templates → Twig context** follow a repeated hand-rolled pattern (see `front-page.php` for the fullest example): fetch a field group with a small `get_field()` wrapper that defaults to `[]`, then manually map/`array_map` each repeater row into the exact shape the Twig section expects (e.g. `headline_lines[].words[]`, `outcome_cards.items[]`). Image/file/URL ACF values are normalized through shared helpers in `inc/acf/url-helpers.php`:
  - `builton_acf_resolve_url($value)` — image array / attachment ID / URL string → URL string
  - `builton_acf_hero_marquee_urls($raw)` — gallery/repeater/URL-string variants → `string[]`
  - `builton_acf_image_for_twig($img)` — → `{url, alt, width, height}` shape or `null`
  When adding a new ACF-backed section, reuse these helpers rather than re-deriving URL/image logic per template.
- **Twig templates** (`views/`): `base.twig` is the HTML shell (head, `wp_head`/`wp_footer`, GSAP ScrollSmoother `#smooth-wrapper`/`#smooth-content` wrappers, header/footer partials from `views/partials/`). Page templates extend it and `{% include 'sections/xxx.twig' with xxx|default({}) %}` per section. `views/sections/` holds ~20 self-contained section components (hero, headline-reveal, outcome-cards, project-*, timeline-accordion, etc.) — treat each as a props-in component keyed to one ACF field group entry.
- **Content sync tool** (`inc/content-sync/`): a Tools → "Builton Content" wp-admin page (`bootstrap.php` registers the menu/admin-post handlers; `import.php`/`export.php` do the work) that imports/exports ACF **field values** (not definitions) as JSON for the front page and What-we-do page, stored at `content-json/*.json`. This is how page copy is versioned: edit `content-json/front-page.json` in the repo, deploy, then run "Import bundled JSON" from wp-admin to push it into the live DB — editing the JSON file alone changes nothing on the site until imported. Default behavior skips overwriting image/file fields so wp-admin media choices survive an import. When a child theme is active, bundled JSON resolves from the **parent** theme dir first.
- **Frontend build**: `src/main.js` is the single Vite entry — imports `src/scss/main.scss` (plus `_page-what-we-do.scss`, `_single-project.scss` partials), sets up GSAP (`ScrollTrigger`, `ScrollSmoother`) and exposes `window.gsap` for inline/Twig-embedded usage, and contains the page's scroll/reveal interaction code (hero clock, marquee, carousel, accordion, parallax, etc.) directly — there's no component-per-file JS split.
- **Custom post type**: `team_member` (registered inline in `functions.php`, title-only, no archive).
- **Gutenberg blocks**: not really used for authoring — `blocks/team-blocks.css` only styles team-related markup; there's no `block.json`/`register_block_type`.

## Conventions worth following

- New PHP helper/hook functions: procedural, prefixed `builton_`, file-scoped under `inc/` by feature (mirror `inc/acf/` and `inc/content-sync/`'s `bootstrap.php`-requires-siblings pattern) rather than introducing classes.
- New ACF-backed page sections: add the field group under `inc/acf/groups/`, register it in `inc/acf/bootstrap.php`, normalize values in the page template using the existing `url-helpers.php` helpers, and add a matching `views/sections/*.twig` partial included from the page's `.twig` template.
