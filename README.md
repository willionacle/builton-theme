# Built-On

Timber v2 theme with Vite, Sass, and GSAP.

## Requirements

- PHP >= 7.4
- WordPress >= 5.3
- Node.js (for Vite build) — **run from project root, not this theme folder**

## Setup

### 1. Composer (Timber)

From the theme directory:

```bash
cd wordpress/wp-content/themes/builton
composer install
```

### 2. npm (Vite, Sass, GSAP) — project root

Development tools live at the **project root** (parent of `wordpress/`). From there:

```bash
cd /path/to/built-in-tandem
npm install
```

### 3. Development

From the **project root**:

```bash
npm run dev
```

Open the site (Docker Compose default: **http://127.0.0.1:18877** — run `./scripts/docker-up.sh` or `npm run docker:up` from the repo root; ports are written to `.env` if defaults are busy). Bundled nginx-proxy profile: **http://builton.test:8080** — see repo root `docker-compose.yml`. The theme will load scripts from `http://localhost:5173` when the dev server is up and `WP_DEBUG` is true.

### 4. Production

From the **project root**, build assets (output goes into this theme’s `dist/`):

```bash
npm run build
```

The theme enqueues from `dist/` using `dist/.vite/manifest.json` when the Vite dev server is not detected.

## Structure

- `views/` — Twig templates (base.twig, index.twig, single.twig, page.twig, front-page.twig).
- `src/main.js` — Entry: imports `scss/main.scss` and GSAP; `window.gsap` is set for use in markup.
- `src/scss/main.scss` — Base styles.
- `dist/` — Vite build output (do not commit; add to .gitignore).

## Notes

- For HMR to work, use **http://builton.test** in the browser so the Vite dev server origin matches.
- Run `composer install` in the theme directory and `npm run build` from the project root before deploying so `vendor/` and `dist/` are present.

## Front-page content sync (ACF + JSON)

Front-page section copy can live in version control as [`content-json/front-page.json`](content-json/front-page.json). Field **definitions** still sync via ACF Local JSON in `acf-json/`; this file is for **values** (post meta) only.

### Client workflow

- Edit the static **front page** in wp-admin (ACF fields). Text and media are stored in the database.
- **Text blocks:** use the **Typography** toggle (**Default** = medium weight 500, **Regular** = normal 400). In JSON, set `"regular_weight"` under `text_block_1`, `text_block_2`, or `text_block_3` (after outcome cards on the front page).

### Developer workflow (e.g. after FTP deploy)

1. Update `content-json/front-page.json` in the repo (e.g. from Cursor).
2. Deploy the theme (include `content-json/front-page.json` in the **parent** Builton theme folder).
3. In wp-admin go to **Tools → Builton Content**.
4. Click **Import bundled JSON**. Leave **Skip image and file fields** checked so wp-admin media choices are not overwritten when you only ship copy changes.
5. Uncheck that option only when you intentionally want JSON (or uploads) to set images/files.

The live homepage reads **ACF values from the database**. Changing the JSON file on disk does nothing visible until you import (or edit the page in wp-admin).

**Child theme:** If a child theme is active, bundled JSON is resolved from the **parent (template)** theme directory first, then the child—so the file can stay in `builton/content-json/` without copying it into the child theme.

### Export from production

On **Tools → Builton Content**, use **Download JSON** to pull live ACF values back into the repo when needed.

### Cautions

- **`hero.marquee_images`:** In JSON this is an **array of image URL strings** (the ACF field is a **gallery** in wp-admin). On import, URLs are matched to the Media Library when possible; external URLs won’t map until those files are uploaded.
- Imports **overwrite** the top-level keys present in the JSON for the front page. Coordinate with the client before re-importing on a live site.
- Requires **Settings → Reading**: a static front page must be set.
- Requires **ACF** (Pro) active; capability **manage_options** to use the tools screen.
