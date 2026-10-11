# SEES theme — project context

Handoff notes for anyone (developer or AI agent) picking up this project. Read this with [CLAUDE.md](CLAUDE.md) (the client brief and rules) and [README.md](README.md) (commands).

_Last updated: 2026-10-11_

## What this is

A custom **classic PHP WordPress theme** (`theme/sees/`) for **SEES — Smart Electrical Engineering Solutions**, an electrical engineering company in Al Shahamah District, Makkah, Saudi Arabia. The site is English + Arabic and will run on the client's shared hosting. The client edits all content in wp-admin. Everything must be free/open-source: no page builder, no paid plugins.

- **Design reference:** 5 approved pages in the claude.ai design artifact linked in CLAUDE.md (`project/Main|About|Solutions|Projects|Contact.dc.html`).
- **Home hero:** later revised from a reference mockup the user supplied (full-bleed building photo, white fade on the copy side, feature strip over the photo).
- **Required plugins:** Polylang, Advanced Custom Fields (free), Contact Form 7.
- **Dev-only plugins:** Theme Check, Query Monitor.

## Status

| Build step (from CLAUDE.md) | State |
|---|---|
| 1. Theme scaffold, enqueue, supports, menus, image sizes | Done |
| 2. Header, footer, design tokens, self-hosted fonts | Done (design-system pass applied) |
| 3. CPTs, taxonomy, ACF field groups in `acf-json/` | Done |
| 4. Templates: Home, About, Solutions, Projects, Contact | Done |
| 5. Animations JS + reduced motion | Done |
| 6. Polylang + Arabic content + RTL pass | Done (Arabic copy needs native review) |
| 7. Seed script `bin/seed.sh` | Done (idempotent; `SEES_RESEED=1` to redo) |
| 8. QA, then export for client host | QA largely done (see below); export not started |

## Local environment

- **Stack:** Docker Compose with WordPress (PHP 8.3, Apache), MariaDB 11, phpMyAdmin, Mailpit and a WP-CLI container (profile `tools`).
- **Theme mount:** `theme/sees/` is mounted live into the container.
- **Dev-only pieces:** `config/mu-plugins/dev-mailpit.php` routes all mail to Mailpit; `config/php.ini` raises upload and memory limits.
- **URLs:** site http://localhost:8080/en/ and /ar/ · admin /wp-admin · Mailpit :8025 · phpMyAdmin :8081.
- **Settings:** in `.env`, which is git-ignored; `setup.sh` copies `.env.example` on first run.
- **Debug:** `WP_DEBUG`, `WP_DEBUG_LOG` and `SCRIPT_DEBUG` are on. The log is `wp-content/debug.log` inside the container.

## Theme architecture

```
theme/sees/
  functions.php            loads inc/*
  inc/setup.php            supports, menus, image sizes, WebP output, meta description, projects query (?cat=)
  inc/enqueue.php          CSS/JS (filemtime versions), per-language font preloads, CF7 assets only on Contact
  inc/post-types.php       solution, project (+ project_category), faq; query helpers
  inc/acf.php              acf-json load/save path, icon choices, missing-plugin notice
  inc/polylang.php         CPTs/taxonomy forced translatable, synced metas, language switcher
  inc/menus.php            menu fallbacks (theme works with no menus assigned), "Projects" current state
  inc/helpers.php          sees_field(), sees_image(), sees_page_url(), sees_contact(), sees_lang_attr(), …
  inc/icons.php            inline SVG line icons (stroke clamped to 1.4–1.7)
  inc/contact-form.php     CF7: no autop
  front-page.php           Home
  page-about.php           Template Name: About
  page-solutions.php       Template Name: Our Solutions (solution rows, process, FAQ)
  page-contact.php         Template Name: Contact (info cards, map, CF7 + success panel)
  archive-project.php      Projects + filter pills (taxonomy-project_category.php reuses it)
  single-project.php, page.php, single.php, index.php, 404.php, searchform.php
  template-parts/          page-header, cta-band (photo|brand|plain), solution-card, project-card, eyebrow
  acf-json/                7 field groups (generated — see "ACF" below)
  assets/css/main.css      everything, token-based; rtl.css = mirroring only; editor.css
  assets/js/main.js        reveal (IntersectionObserver), mobile nav, search panel, project filter, FAQ, CF7 success
  assets/fonts/            woff2 + OFL licences
  assets/img/              logos, favicon, brand photos (+ .webp copies used as fallbacks)
  languages/               sees.pot, ar.po, ar.mo
```

### Key decisions

- **Page templates use a `Template Name:` header**, not slugs. Arabic pages have different slugs (Polylang free can't share slugs), so templates are matched by `_wp_page_template`. `sees_page_by_template()` finds a page in the current language.
- **Solutions and FAQs have no single pages** (`publicly_queryable => false`). Cards link to `#solution-{slug}` anchors on the Our Solutions page.
- **Projects use a CPT archive at `/projects/`.** The archive header copy is translatable theme strings, not ACF, because there's no page to attach fields to.
- **Project filter:** pills are real links (`?cat=slug`; term archives also work), and JS filters in place without a reload. Pills use `aria-current`.
- **Contact details are entered once** on the Contact page (ACF) and reused in the footer and the Home call to action.
- **The theme renders design copy as fallback** when an ACF field is empty (`sees_field($name, $default)`). It also works with ACF deactivated.
- **Floating logo mark in the Home hero** is off by default. It's controlled by the ACF true/false field `hero_mark`.
- **Post types are registered in the theme** because the brief requires `inc/post-types.php`. Theme Check flags this as plugin territory. Trade-off: if the theme is switched, the content stays in the database but disappears from the admin.

### ACF

The field groups are generated by a Python spec script that isn't in the repo. Edit them in wp-admin (ACF re-saves to `acf-json/`) or edit the JSON directly.

The groups:
- **Page header** (all pages): subtitle, secondary line, header photo
- **Home**, **About**, **Our Solutions page**, **Contact page**
- **Solution details**: number, icon, accent, bullets, intro
- **Project details**: client, city, year, summary, `is_featured`

There are no Repeater or Flexible Content fields (Pro-only). Repeating items are numbered fields or CPTs.

### Polylang

- **URLs:** directory per language (`/en/`, `/ar/`); the default language is not hidden; front pages are at `/en/` and `/ar/`.
- **Translatable:** `solution`, `project`, `faq` and `project_category`.
- **Synced across translations:** page template, menu order, thumbnail and taxonomies, plus the metas `number`, `icon`, `accent`, `year`, `is_featured` and `header_image`.
- **Setting options from code:** Polylang caches its options and saves them on shutdown, so a plain `update_option('polylang')` gets overwritten. Use `PLL()->options->set()` (see `sees_pll_option()` in `bin/seed.php`).

## Design system (summary — tokens live in `:root` of main.css)

- **Brand colours:** `--c-navy #0e2032`, `--c-petrol #005b7a`, `--c-green #84c666`, `--c-ink #0e1a28`. Official 80/60/40/20 tints exist as `--c-{name}-80…20`.
- **Web additions:** `--c-teal #14a7a2` (decorative only, never text on white), `--c-bg-soft`, `--c-border`, `--c-text`, `--c-text-on-dark`, `--grad-brand`, plus `--c-error` and `--c-warning*` for form and notice states.
- **Contrast rules:** never white text on green or teal. Green buttons use ink text. Small icons on white use petrol. The focus ring is petrol on light backgrounds and green on dark ones.
- **Fonts:** Plus Jakarta Sans for English (one variable Latin woff2, 400–800). IBM Plex Sans Arabic for Arabic (400/500/600/700, with Arabic and Latin subsets). Everything is self-hosted with no external requests. The logo is always an image.
- **Type scale:** `--fs-*`, `--lh-*`, `--ls-*` and `--fw-*` tokens are set per language on `:root, :lang(en)` and `:lang(ar)`. Arabic has no uppercase, zero letter-spacing and taller line-heights. Use `sees_lang_attr()` on mixed-language snippets so they pick up the right font and scale.
- **Layout:** container 1360px; gutters 40/24/16px; sections 110/72px. Radii: buttons 10px, cards 14–16px, large media 18px, pills 999px.
- **Buttons:** primary is navy with white text; secondary is white with a navy border; on dark backgrounds, green with ink text.
- **Motion:** load fade-up and header zoom, scroll reveal, hover lift, marquee, pulse and ping. All of it is disabled under `prefers-reduced-motion`.

## Content rules and placeholders

Never invent facts. These are still placeholders for the client to fill:

- **Stats:** `[10+]` and `[100+]`.
- **Projects:** every project is `[PROJECT NAME]`, `[Client]`, `[City]` and `[Year]`, and each has a placeholder case-study text.
- **Contact page:** working hours `[Sun – Thu, 00:00 – 00:00]`, and the map is a placeholder until a Google Maps embed URL is pasted.
- **FAQs:** `[SERVICE AREA]` and the maintenance contract terms.
- **About:** founding, leadership and certifications paragraph.
- **Home story video URL:** the play button is hidden until one is set.

Real details: +966 50 326 4000 · info@seesengineering.com · Al Shahamah District, Makkah · www.seesengineering.com.

## QA done (local)

- **Viewport fit:** all 13 page types, EN and AR, at 360, 390, 768, 1280 and 1440px. No horizontal overflow, measured with true viewport width via CDP.
- **Layout shift:** CLS ≤ 0.012.
- **Fonts and requests:** no external requests; fonts load only from the theme.
- **Attributes:** correct `lang` and `dir` on every page.
- **Lighthouse (mobile, throttled):**
  - accessibility 100
  - SEO 100
  - best practices 100
  - performance 88–94, limited by LCP of header photos
- **Contact form:** submission verified end-to-end into Mailpit. Recipient info@seesengineering.com, Reply-To the visitor. Validation errors work.
- **Re-seed:** `SEES_RESEED=1 bin/seed.sh` replaces sample content without duplicates.
- **Debug log:** clean after loading every page.
- **Not yet click-tested in a real browser:** mobile menu, search panel, project filter, FAQ accordion, the CF7 success panel and the animations. Screenshots were taken with reduced motion on.

## Open items / next steps

1. **Home hero photo:** the client needs to supply the bright daylight building photo from the reference mockup. Upload it in Pages → Home → Header photo; the current fallback is `sky.jpg`. Other pages' header photos could also use real site photography.
2. **Arabic review:** a native speaker should review all Arabic copy, both the seed content and `languages/ar.po`.
3. **Placeholders:** replace the ones listed above with real content.
4. **Insights:** the reference mockup shows an "Insights" menu item. There's no Insights page or template yet; decide whether to add a blog/insights section.
5. **Manual browser pass:** click through the interactive parts (point above) on real devices.
6. **Go-live:**
   - install the three plugins on the host
   - upload the theme
   - migrate with All-in-One WP Migration or Duplicator
   - add an SMTP plugin so form mail is delivered
   - enable browser caching
   - set a Site Icon
   - optionally add an SEO plugin; the theme's basic meta description switches off automatically when Yoast, Rank Math or The SEO Framework is active
7. **Logo:** ask the client for the official SVG logo to replace the PNGs.

## Repository

- **Remote:** https://github.com/Bislem/SEES-wp-theme (public), branch `main`.
- **Not in git** (see `.gitignore`):
  - `.env`
  - `sees-theme-kit.zip`
  - `sees-theme-kit/reference/` (the client's brand-guidelines PDF; ask the project owner for it)
- **In git:** logos and photos, because the theme needs them and they're public on the site anyway.
