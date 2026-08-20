# ClientcareX — website

A rebuild of `clientcarex.com`, reconstructed from the Wayback Machine capture
of **29 July 2025** after the original site was lost.

The original was WordPress 6.8.2 + Elementor 3.30.3 running the *Zenix* theme.
This rebuild is **plain PHP 8** — no framework, no database, no Composer, no
build step. It runs on any host with PHP and serves pages from a full-page
cache, so a warm request costs under a millisecond of PHP time.

---

## How it is put together

```
index.php              front controller — routes, renders, caches
.htaccess              clean URLs, cache headers, compression, security headers

app/
  config.php           site constants, page registry, navigation, footer links
  content.php          page content as data: plans, FAQs, reviews, modules, steps
  icons.php            every SVG, defined once
  helpers.php          e() url() asset() view() part() minify_html()
  response.php         page cache, gzip, ETag / 304
  enquiry.php          demo-request validation, CSRF, mail, enquiry log

views/
  layout.php           the one HTML skeleton
  partials/            header, footer, plans, faq, split, steps, stats, cta …
  pages/               one file per page, body only
  feeds/               sitemap.xml and robots.txt templates

assets/                css, js, images (served straight from disk)
storage/               cache, enquiry log, signing key — not web-accessible
```

Nothing is generated and committed. Edit a file, reload the page.

### The two rules that keep it small

1. **Anything repeated is data, not markup.** The three pricing tables are
   rows in `PLANS`; the FAQ accordion is `FAQS`; reviews, integrations,
   modules, steps and stats work the same way. Adding a plan feature is one
   line in `app/content.php`.
2. **Anything shared is a partial.** The check mark that appeared ~50 times in
   the old HTML is now `icon('check')`. The hero, section heading, CTA band,
   contact cards and button rows are each defined once in `views/partials/`.

### Adding a page

1. Add a route to `PAGES` in `app/config.php` (title, description, nav key).
2. Create `views/pages/<view>.php` with just the body.
3. Add it to `NAV` if it belongs in the header.

The sitemap and robots.txt pick it up automatically.

---

## Running it

```bash
php -S localhost:4173 index.php
```

Then open <http://localhost:4173>. Template edits appear on the next reload —
the cache invalidates itself whenever a file under `app/` or `views/` changes.

---

## Performance

| What | How |
|---|---|
| Full-page cache | Each page is rendered once, minified, and written to `storage/cache/` as plain and pre-gzipped copies. Warm requests skip rendering entirely. |
| Conditional requests | Every response carries an `ETag`; a returning visitor gets `304 Not Modified` with no body. |
| Compression | HTML is gzipped once at cache-write time (level 9) rather than on every request. |
| Immutable assets | CSS, JS and images are served with `?v=<mtime>` and a one-year `immutable` cache header. |
| No render-blocking JS | 2.6 KB of vanilla JS, deferred. The copyright year and the contact form are handled server-side, so no JS is needed for either. |

Cache invalidation is automatic: the cache key includes a fingerprint of every
file under `app/` and `views/`, plus the current year. To clear it by hand:

```bash
rm -rf storage/cache/*
```

Set `CACHE_ENABLED` to `false` in `app/config.php` to turn it off.

---

## The demo request form

`/contact` posts to itself. Submissions are validated server-side, protected by
a signed-token CSRF check and a honeypot field, then:

1. appended to `storage/enquiries.log` (one JSON object per line), and
2. emailed to `MAIL_TO` in `app/config.php` via PHP's `mail()`.

The log is written **before** mail is attempted, so an enquiry is never lost if
the host's mail transport is unconfigured or down. Check `mailed:false` entries
in the log after go-live to confirm mail is actually working — and if the host
blocks `mail()`, swap the call in `app/enquiry.php` for SMTP.

---

## What was recovered, and what wasn't

### Recovered from the archive
- All homepage copy — hero, feature sections, three pillars, integrations,
  the full three-tier pricing tables, testimonials and all seven FAQs.
- Brand palette, taken from the original Elementor CSS and the logo artwork:
  deep green `#065845`, lime `#A9FF9B` / `#88E64A`, amber `#FFA012`,
  mint tint `#EBFEF6`, paper `#F4F7F2`, ink `#101010`.
- Typography: **Plus Jakarta Sans** (headings) and **DM Sans** (body).
- Contact details: `+91 93908 93024`, `care@clientcarex.com`.
- 22 image assets, in `assets/img/`.
- Navigation structure and the five-column footer.
- App links: login and register point at `clientcarex.com/ccx/authentication/*`.

### Not recoverable
Only the homepage was ever archived, so these were written fresh in the
original's voice: `features`, `pricing`, `how-it-works`, `challenge`, `blog`
(empty state), `contact`, and the three legal pages.

Four testimonial avatar images returned 404 from the archive, so reviewer
initials are shown in coloured circles instead — derived from the name, not
stored separately.

### Deliberate corrections to the original copy
- **"Zenix" appeared twice in the body copy** — the WordPress theme name had
  leaked into the placeholder text. Replaced with ClientcareX.
- **Duplicate Slack integration card** — the second is now correctly Zapier.
- Typos: `cusomter` → customer, `powerfull` → powerful,
  `Annoucement` → Announcement, `Link Shortly` → Link Shortener.

---

## Before this goes live

1. **Have the three legal pages reviewed.** They are drafts written against the
   original's voice, not recovered text — the guarantee and liability clauses
   in particular should be checked against your actual contracts.
2. **Confirm mail works.** Send a test through `/contact` and check the
   recipient inbox and `storage/enquiries.log`.
3. **Fill in the social links.** The four footer icons are `#` placeholders in
   `SOCIAL` in `app/config.php`.
4. **Confirm the two guarantee windows.** The homepage advertises a 30-day
   money back guarantee and a 90-day guarantee; make sure the refund policy
   matches what you actually offer.
5. **Check the client logos.** Eight are shown in the "Trusted by 100+ Teams"
   row — confirm you still have permission to use each one.
6. **Turn on the HTTPS redirect** in `.htaccess` once the certificate is live.

## Hosting

Any PHP 8.1+ host. Upload the folder, point the document root at it, make sure
`storage/` is writable by the web server:

```bash
chmod -R 775 storage
```

Apache picks up `.htaccess` as-is. On nginx, route unknown paths to the front
controller and deny the source directories:

```nginx
location / { try_files $uri $uri/ /index.php; }
location ~ ^/(app|views|storage)/ { deny all; }
```

Old `*.html` URLs 301-redirect to their clean equivalents, so existing links
and search results keep working.

## Recovery source

`https://web.archive.org/web/20250729025946/https://clientcarex.com/`
