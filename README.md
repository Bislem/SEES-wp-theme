# SEES — WordPress theme

Custom classic theme for **SEES — Smart Electrical Engineering Solutions**, built from the approved 5-page design.
English + Arabic (RTL) via Polylang, editable content via ACF (free), contact form via Contact Form 7.
Project brief and rules: [CLAUDE.md](CLAUDE.md).

## Quick start

```bash
./setup.sh        # start Docker stack, install WordPress
bin/seed.sh       # plugins, Arabic language packs, EN + AR sample content, menus, forms
```

| | URL |
|---|---|
| Site (EN / AR) | http://localhost:8080/en/ · http://localhost:8080/ar/ |
| Admin | http://localhost:8080/wp-admin (`admin` / `admin`) |
| Mailpit (catches all site email) | http://localhost:8025 |
| phpMyAdmin | http://localhost:8081 |

## Everyday commands

```bash
docker compose run --rm wpcli wp <command>             # WP-CLI
SEES_RESEED=1 bin/seed.sh                              # wipe sample content and seed again
docker compose exec wordpress tail -f wp-content/debug.log
docker compose stop                                    # stop
docker compose down -v                                 # delete everything incl. database
```

## Layout

```
theme/sees/              the theme (mounted live into the container)
  front-page.php         Home
  page-about.php         About          (Template: About)
  page-solutions.php     Our Solutions  (Template: Our Solutions)
  archive-project.php    Projects + category filter; single-project.php
  page-contact.php       Contact        (Template: Contact)
  template-parts/        page-header, cta-band, solution-card, project-card, eyebrow
  inc/                   setup, enqueue, post types, ACF, Polylang, menus, helpers, icons
  acf-json/              ACF field groups (auto-loaded)
  assets/css|js|fonts|img
  languages/             ar.po / ar.mo for theme strings
bin/seed.sh, seed.php    sample content (EN + AR)
config/                  dev-only PHP settings + Mailpit mail routing
sees-theme-kit/          original brief, brand guidelines PDF, source images
```

## Editing content (for the client)

- **Pages** (Home, About, Our Solutions, Contact): every text block and photo is a field on the page edit screen.
  Each language is its own page, linked via Polylang.
- **Solutions / Projects / FAQs**: menu items in wp-admin. Order with the "Order" attribute. Tick "Featured on Home" on one project.
- **Contact details** on the Contact page are reused in the footer and the Home call-to-action.
- **Menus**: Appearance → Menus (one per language). Footer columns fall back to automatic links if left unassigned.

## Installing on a new WordPress (staging / production)

1. Install and activate **Polylang**, **Advanced Custom Fields** and **Contact Form 7**.
2. Upload `theme/sees/` (or deploy it with cPanel Git) and **activate the theme**.
3. Open any wp-admin page. The theme sets up everything else by itself:
   - English and Arabic languages, with `/en/` and `/ar/` URLs
   - the Arabic language pack and permalinks
   - Home, About, Our Solutions and Contact in both languages, with content
   - solutions, FAQs and project categories
   - placeholder projects, as drafts
   - contact forms, menus and the front page

   A notice lists what was done.

The setup only adds what's missing, never overwrites content, and runs on every activation. Check or re-run it from **Appearance → SEES setup**. The only manual step left is an SMTP plugin (e.g. WP Mail SMTP) so form emails get delivered.
