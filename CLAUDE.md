# SEES website — custom WordPress theme

Client: **SEES — Smart Electrical Engineering Solutions** (سيز للحلول الهندسية الكهربائية الذكية), electrical engineering company in Al Shahamah District, Makkah, Saudi Arabia.

Goal: build a **custom WordPress theme** (no page builder) from the approved design, running on the client's **shared hosting**. The client edits all content in wp-admin. Everything must be **free / open-source** — no paid plugins, no third-party SaaS CMS.

Design reference (5 approved pages): https://claude.ai/artifact/KsZkEbmtcgSt8XSovezaxS
Brand guidelines: `sees-theme-kit/reference/Visual_Brand_SEES_ENG_V1_2026.pdf` (not in git — ask the project owner)

## Stack

- WordPress, PHP 8+, custom theme `sees` (classic theme with PHP templates — not a block theme)
- Plain CSS (one `assets/css/main.css` + `assets/css/rtl.css`) and one small vanilla JS file. No build step required; no jQuery.
- Plugins (all free, from wordpress.org):
  - **Polylang** — English + Arabic (`/en/`, `/ar/`), language switcher in header
  - **Advanced Custom Fields (free)** — editable page fields. Do NOT use Repeater/Flexible Content (Pro only); model repeating content as custom post types instead
  - **Contact Form 7** — contact form, styled to match
- Use **WP-CLI** for setup: activate theme, install plugins, register sample content, menus, permalinks (`/%postname%/`).

## Pages and templates

| Page | Template | Content source |
|---|---|---|
| Home | `front-page.php` | ACF fields on the Home page + latest Solutions + a featured Project |
| About | `page-about.php` | ACF fields (story, mission, vision, values) |
| Our Solutions | `page-solutions.php` (or `archive-solution.php`) | `solution` CPT + `faq` CPT |
| Projects | `archive-project.php` + `single-project.php` | `project` CPT with `project_category` taxonomy |
| Contact | `page-contact.php` | ACF fields (phone, email, address, hours, map embed) + CF7 shortcode |
| Careers | `page-jobs.php` + `single-sees_job.php` | `sees_job` CPT (inc/jobs.php); standard CF7 application form per language (inc/job-applications.php) |
| Support & Downloads | `page-support.php` | `sees_download` CPT + `sees_download_type` taxonomy (inc/downloads.php), one record serves both languages |
| Privacy Policy | `page.php` | Saudi PDPL template created by the installer |

Shared: `header.php`, `footer.php`, `template-parts/` (page-header, cta-band, solution-card, project-card, eyebrow).

### Custom post types (register in `inc/post-types.php`, `show_in_rest` true, translatable via Polylang)
- `solution`: title, excerpt, featured image, number (01–05), icon (select), bullet points (textarea, one per line), order
- `project`: title, featured image, `project_category` taxonomy (Power Distribution, Solar, Infrastructure, Smart Buildings, Maintenance), client, city, year, short description, `is_featured` (true/false)
- `faq`: question (title), answer (content), order

## Design system

Colors (from brand guidelines):
- Navy `#0e2032` (text, primary buttons), Near-black `#0e1a28` (footer)
- Petrol blue `#005b7a` (brand, links), Teal `#14a7a2` (accents, underlines)
- Green `#84c666` (energy accent, CTA on dark)
- Light bg `#f4f7f9`, border `#e1e6eb`, body grey `#4a5866`, muted on dark `#a9b8c6`

Typography:
- Latin: **Plus Jakarta Sans** 400–800 (headings 800, tight letter-spacing)
- Arabic: **IBM Plex Sans Arabic** 400–600
- Self-host both fonts in `assets/fonts/` (woff2) — no Google Fonts requests at runtime
- Eyebrow labels: 13px, 600, uppercase, letter-spacing 5–6px, followed by a 44px teal line

Layout: max-width 1360px, 40px side padding (16px on phones). Buttons: 10px radius, min-height 50–56px. Cards: 14–16px radius.

Logo files (do not redraw): `assets/img/logo-light.png` (light bg), `logo-dark.png` (navy footer), `logo-mark.png` (icon only, hero + favicon). Ask the client for the official SVG later.

Photos: `assets/img/` — sky, concrete, engineer, pylons, facade, sign, sitebanner (cut from the brand guidelines). Use as defaults; real content comes from wp-admin.

## Animations (match the design)
- Page load: header items fade-up with staggered delays; header photo slow zoom-out (2.6s)
- Scroll: sections fade-up when entering viewport — use `IntersectionObserver` adding `.is-visible` (works in all browsers)
- Hover: cards lift 8px + shadow + gradient top bar sweeps in; images zoom 1.07; arrows nudge 5px; nav links teal underline grows
- Continuous: services marquee on Home; pulsing play button; floating logo mark in Home hero; flowing gradient line on process steps; map pin ping
- Interactive: Projects category filter (JS, no reload, with `?cat=` fallback), FAQ accordion (`<details>`-based or button + aria-expanded)
- Respect `prefers-reduced-motion`: disable all animation

## Arabic / RTL
- `rtl.css` loaded when `is_rtl()`; prefer CSS logical properties (`margin-inline-start`, `padding-inline`, `inset-inline-end`, `text-align: start`) in main.css so RTL needs few overrides
- Mirror directional arrows in RTL; hero photo/fade flips to the left side
- All theme strings wrapped in `__()` / `esc_html_e()` with text domain `sees`; register ACF/CPT strings with Polylang

## Content rules
- Never invent stats, client names or project facts. Use bracketed placeholders like `[10+]`, `[PROJECT NAME]`, `[Client]` until the client supplies real content.
- Real contact details: +966 50 326 4000 · info@seesengineering.com · Al Shahamah District, Makkah, Saudi Arabia · www.seesengineering.com
- Taglines: "Engineering Smarter, Powering Better" / نهندس بذكاء ونُشغّل بشكل أفضل — "Engineering Comfort, Perfected" / نصمّم الراحة بهندسة ذكية

## Quality bar
- Escape all output (`esc_html`, `esc_url`, `esc_attr`, `wp_kses_post`); nonces where relevant
- Accessible: real buttons/links, labels on inputs, alt text, visible focus states, 4.5:1 contrast
- Responsive down to 360px; test at 360, 768, 1280, 1440
- Lighthouse performance target 90+ on shared hosting: lazy-load images, `srcset` via `wp_get_attachment_image`, defer JS
- Check `wp-content/debug.log` is clean with `WP_DEBUG` on

## Build order
1. Theme scaffold (style.css, functions.php, enqueue, theme supports, menus, image sizes)
2. Header + footer + design tokens + fonts
3. CPTs, taxonomy, ACF field groups (export to `acf-json/` in the theme)
4. Templates: Home → About → Solutions → Projects → Contact
5. Animations JS + reduced-motion
6. Polylang setup + Arabic sample content + RTL pass
7. WP-CLI seed script (`bin/seed.sh`) creating pages, menus, sample solutions/projects/FAQs in EN + AR
8. QA pass (responsive, RTL, accessibility, debug log), then export with All-in-One WP Migration / Duplicator for the client host

## Local development (this repo)

- Docker stack: `./setup.sh` (WordPress + MariaDB + phpMyAdmin + Mailpit), theme mounted from `theme/sees/`.
- Seed EN + AR sample content: `bin/seed.sh` (re-run from scratch: `SEES_RESEED=1 bin/seed.sh`). Logic lives in `bin/seed.php`.
- WP-CLI: `docker compose run --rm wpcli wp …`
- Theme translations: `theme/sees/languages/ar.po` → compile with `msgfmt -o ar.mo ar.po`. Regenerate the POT with `wp i18n make-pot`.
- ACF field groups are JSON in `theme/sees/acf-json/` (edit in wp-admin and ACF re-saves them there, or edit the JSON).
- Design reference artboards: `project/*.dc.html` in the design artifact above.
