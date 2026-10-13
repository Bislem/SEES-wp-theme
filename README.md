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
- **Careers**: Jobs → Add new job. Fill in the vacancy (title, description, department, location, type, closing date) and publish, in one or both languages.
  - The application form appears automatically: CV upload, consent, and emails with the CV attached.
  - Applications go to Careers page → *Applications* tab email, or to the Contact email if that's empty. The applicant gets a confirmation.
  - Vacancies past their closing date, or set to *Closed*, disappear from the list and stop accepting applications.
- **Support & Downloads**: Downloads → Add new document.
  - Upload the file and give it an English title (and optionally an Arabic title, descriptions, language, version and date). Pick a document type.
  - One document appears on both language versions of the Support page. Visitors can search and filter by type and language.
- **News**: Posts → Add New Post. Write the article, set a featured image, an excerpt (shown on cards and under the title) and a category (News or Insights).
  - Translate it with Polylang's "+" column to publish the Arabic version.
  - Posts appear on the News page, newest first (the latest shown large), with category filters.
  - Comments are switched off.
- **Submissions** (wp-admin menu): a copy of every quote request and job application, saved even if the email fails.
  - *Quote requests* and *Job applications* views. New items are marked, with a count on the menu. Open one to see the details, reply by email or download the CV.
  - Each vacancy shows its application count (Jobs list column, and a box on the edit screen) with a link to its applications.
  - CVs are kept in a private folder that can't be opened from the web; only logged-in editors and administrators can download them.
  - Everything is deleted automatically after 12 months (Submissions → Retention). Keep that in line with the privacy policy.
  - A "delete my data" request: Tools → Erase Personal Data, using the person's email (also removes their CV). Tools → Export Personal Data gives them a copy.
- **Top bar**: phone, email, address (and working hours, once they're no longer a placeholder) come from the Contact page.

## Going live on www.sees-engineering.com

1. **Domain and SSL (cPanel):** point sees-engineering.com at the hosting, and add both `sees-engineering.com` and `www.sees-engineering.com`. Then run **SSL/TLS Status → Run AutoSSL**, so both get a certificate.
2. **Email:**
   - Create the **contact@sees-engineering.com** mailbox, and **careers@sees-engineering.com** if you want a separate one for applications.
   - In **Email Deliverability**, fix SPF/DKIM.
   - Install **WP Mail SMTP** and connect it to contact@.
3. **WordPress:** install it in the domain's folder (usually `public_html`).
   - **Settings → General:** set both addresses to **https://www.sees-engineering.com**.
   - **Settings → Reading:** make sure "Discourage search engines" is **unticked**.
4. **Redirects:** paste `config/production/htaccess-redirects.conf` at the top of that site's `.htaccess`. Then http:// and the bare domain go to https://www.sees-engineering.com.
5. **Theme deploy:**
   - Clone the repo in cPanel Git Version Control.
   - Run `echo "public_html" > ~/.sees-deploy-path` once (in Terminal or over SSH), using the folder from step 3.
   - Click **Deploy HEAD Commit**, then activate the theme.
6. **Content:**
   - **Fresh start:** the theme sets everything up automatically. Open wp-admin once and follow **Appearance → SEES setup**.
   - **Moving staging content over:** export with All-in-One WP Migration and import on the live site; it rewrites the URLs. Then run **Appearance → SEES setup → Run automatic setup**.
7. **Final check:** **Appearance → SEES setup** checks the address (https + www) and that search engines are allowed.

Staging and local copies automatically send `noindex` to search engines. Only the live domain is indexed.

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
