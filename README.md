# ClientcareX — website

Marketing site for **ClientcareX**, a performance marketing and business
automation agency.

It is plain PHP 8 — no framework, no database, no Composer, no build step. It
runs on any host with PHP and serves pages from a full-page cache, so a warm
request costs under a millisecond of PHP time.

---

## The business model the site sells

Everything on the site exists to explain two commercial models. If you change
one of them, change it in `app/config.php` / `app/content.php` — not in a
template.

### 1. Performance marketing — revenue share

The client pays **20–30% of the revenue we generate**, and nothing else.

- No setup fee, no onboarding fee, no retainer, no minimum spend.
- **We fund every campaign expense**: ad spend, media buyers and analysts,
  creative production, landing pages and CRO, software subscriptions, licensed
  assets, and tracking/reporting infrastructure.
- The client covers only their own product and fulfilment, their own gateway
  fees, account access, and the revenue share itself.
- Revenue is *attributed* — tracked by an agreed method, netted of refunds and
  cancellations, and reconciled against the client's books every month before
  an invoice is raised.

The `LEDGER_OURS` / `LEDGER_YOURS` arrays in `app/content.php` are the canonical
statement of who pays for what. The `ledger` partial renders them on the
homepage, the performance-marketing page and the pricing page — so the promise
is written once and cannot drift between pages.

The band ends are `SHARE_MIN` / `SHARE_MAX` in `app/config.php`, composed into
`SHARE_RANGE`. Change those two constants and every headline, meta description,
FAQ and legal clause follows.

### 2. Business automation — scoped

Priced on **client work scope and implementation**: free discovery → written
scope document → one fixed quote against it. Not per user, not per month, no
recurring licence. Out-of-scope work is re-quoted, never slipped onto an
invoice.

### 3. Growth Partner

Both engines under one engagement — revenue share on the marketing side, scoped
fee on the automation side. It is a bundling of the two models above, not a
third price.

---

## How it is put together

```
index.php              front controller — routes, redirects, renders, caches
.htaccess              clean URLs, cache headers, compression, security headers

app/
  config.php           site constants, share band, page registry, nav, redirects
  content.php          the models, ledgers, service grids, steps, FAQs, stats
  icons.php            every SVG, defined once
  helpers.php          e() url() asset() view() part() minify_html()
  response.php         page cache, gzip, ETag / 304
  enquiry.php          growth-audit form validation, CSRF, mail, enquiry log

views/
  layout.php           the one HTML skeleton
  partials/            header, footer, models, ledger, steps, service-grid …
  pages/               one file per page, body only
  feeds/               sitemap.xml and robots.txt templates

assets/                css, js, images (served straight from disk)
storage/               cache, enquiry log, signing key — not web-accessible
```

Nothing is generated and committed. Edit a file, reload the page.

### The two rules that keep it small

1. **Anything repeated is data, not markup.** The three model cards are rows in
   `MODELS`; the FAQ accordion is `FAQS`; the cost ledger, service grids, steps
   and stats work the same way. Adding a service line is one line in
   `app/content.php`.
2. **Anything shared is a partial.** The check mark is `icon('check')`. The
   hero, section heading, CTA band, cost ledger, contact cards and button rows
   are each defined once in `views/partials/`.

### Pages

| Route | What it does |
|---|---|
| `/` | Both engines, the cost ledger, fit criteria, pricing, FAQs |
| `/performance-marketing` | The revenue share in full: ledger, what sets the rate, attribution, scope of work, timeline, fit |
| `/business-automation` | Scoping and fixed quoting, what we build, integrations, build timeline |
| `/how-it-works` | The four-step engagement, then each engine's own timeline |
| `/pricing` | Both models side by side, what the share covers, what moves an automation quote |
| `/blog` | Empty state — see below |
| `/contact` | Free growth audit form |
| `/privacy` `/terms` `/refund` | Legal — **drafts, see the go-live checklist** |

### Adding a page

1. Add a route to `PAGES` in `app/config.php` (title, description, nav key).
2. Create `views/pages/<view>.php` with just the body.
3. Add it to `NAV` if it belongs in the header.

The sitemap and robots.txt pick it up automatically.

> The header fits five nav items plus the CTA down to 1120px, below which it
> collapses to the drawer. Adding a sixth long label will need that breakpoint
> raised in `assets/css/style.css` (§6).

### Adding a blog post

`/blog` is an empty state. The card markup is already styled — drop this inside
the container in `views/pages/blog.php`:

```php
<article class="post">
  <div class="post__thumb">Post title</div>
  <div class="post__body">
    <div class="post__meta"><span>Category</span><span>5 min read</span></div>
    <h3>Post title</h3>
    <p>Standfirst.</p>
    <?php part('link-arrow', ['label' => 'Read more', 'href' => 'blog']) ?>
  </div>
</article>
```

Wrap several in `<div class="grid grid--3">`.

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
| No render-blocking JS | One small vanilla JS file, deferred. The copyright year is server-side; the enquiry form works without JS and is only enhanced by it. |

Cache invalidation is automatic: the cache key includes a fingerprint of every
file under `app/` and `views/`, plus the current year. To clear it by hand:

```bash
rm -rf storage/cache/*
```

Set `CACHE_ENABLED` to `false` in `app/config.php` to turn it off.

---

## The growth-audit form

`/contact` posts to itself. Submissions are validated server-side, protected by
a signed-token CSRF check and a honeypot field, then:

1. appended to `storage/enquiries.log` (one JSON object per line), and
2. emailed to `MAIL_TO` in `app/config.php` — over SMTP when
   `storage/mail.php` exists, otherwise via PHP's `mail()`.

The log is written **before** mail is attempted, so an enquiry is never lost if
the host's mail transport is unconfigured or down. When delivery fails the
visitor is told so (and pointed at the phone number and email address) rather
than shown a false "thanks", and the log row carries `mailed:false` plus a
`mail_error` explaining why.

### What the visitor sees

`assets/js/main.js` validates each field as it is left (name, email, phone,
message length — the same rules as the server) and submits with `fetch`,
so the outcome appears inside the form: a green "thanks", an amber "recorded
but not emailed", or a red error naming the field or the network problem.
Without JavaScript the same endpoint re-renders the page with the errors, or
redirects to `/contact?sent=1#enquiry`.

### Connecting the mailbox (no file editing needed)

Open **`/homepage/mail-setup`** on the live site, enter the mailbox that
should send the email and its password, choose where enquiries are delivered,
and press the button. The page logs in to Hostinger's SMTP, sends a test
email, and only then writes `storage/mail.php`. A wrong password is explained
on screen and never saved; failed attempts are rate-limited. Note the address
stays under `/homepage/` because the root rewrite only knows the public pages.

Hostinger's hosting machines answer for `smtp.hostinger.com` with their own
certificate, so the saved settings skip certificate verification on that hop
(`'insecure' => true`), as Hostinger's own PHPMailer guidance does.

### Or by hand

Create `storage/mail.php` — it is git-ignored and not web-accessible:

```php
<?php return [
    'host' => 'smtp.hostinger.com',
    'port' => 465,                       // 465 = TLS from the start, 587 = STARTTLS
    'user' => 'care@clientcarex.com',
    'pass' => '…',                       // the mailbox password
    'from' => 'care@clientcarex.com',    // a mailbox the login is allowed to send as
    'to'   => 'care@clientcarex.com',    // optional: where enquiries go (default MAIL_TO)
    'insecure' => true,                  // needed on Hostinger hosting, see above
];
```

Send a test through `/contact` and check `storage/enquiries.log`: the newest
row should say `mailed:true`. If not, `mail_error` holds the SMTP server's
reply (a `535` is a wrong user/password; a connect error means the host
blocks outbound SMTP on that port — try the other one).

Fields captured: name, company, email, phone, `interest` (from `INTERESTS`),
`revenue` (from `REVENUE_BANDS`) and the free-text message. The last two are
what let you triage which model fits before the call.

---

## Retired routes

The site was previously an AI-ERP product site. Those routes 301 to their
successors via `REDIRECTS` in `app/config.php`:

| Old | New |
|---|---|
| `/features` | `/business-automation` |
| `/challenge` | `/performance-marketing` |

Old `*.html` URLs redirect too, so existing links and search results keep
working. Deleted along with the pivot: the per-user pricing tiers, the ERP
module catalogue, and the 90-Day Business Transformation Challenge.

---

## Before this goes live

1. **Replace the placeholder testimonials.** `REVIEWS` and `QUOTE` in
   `app/content.php` are marked placeholders — the originals were Elementor
   filler text and were never attributable to a real person. Replace every row
   with a quote you have written permission to publish, or delete the constants
   and the two sections that render them (the review grid on `home.php`, and
   the `promise` partial's quote card). **Do not ship the placeholders.**
2. **Have the three legal pages reviewed.** `privacy`, `terms` and `refund` are
   drafts written against the published commercial model, not against your
   signed contracts. The revenue-share definition, attribution method, tail
   period after termination, notice period and clawback mechanics in particular
   must match what you actually sign. Both `terms` and `refund` carry a visible
   "draft for review" notice — remove it once reviewed.
3. **Confirm the numbers in the copy.** `SHARE_MIN` / `SHARE_MAX` (20%/30%), the
   30-day notice period, the 5-working-day reconciliation window and the
   payment terms all appear in the legal pages and the FAQs. Change them in
   `app/config.php` and `app/content.php`, not page by page.
4. **Confirm mail works.** Open `/homepage/mail-setup`, connect the mailbox
   (see "The growth-audit form"), then send a test through `/contact`.
5. **Fill in the social links.** The four footer icons are `#` placeholders in
   `SOCIAL` in `app/config.php`.
6. **Check the client logos.** Eight are shown in the "Brands that trusted us to
   run their growth" row — confirm each one is a real client of the *agency*
   and that you still have permission to use the mark.
7. **Confirm the client dashboard link.** `APP_LOGIN` still points at
   `clientcarex.com/ccx/authentication/login`. It appears in the header, the
   mobile nav and the footer — repoint or remove it if that dashboard is not
   what live clients log into.
8. **Turn on the HTTPS redirect** in `.htaccess` once the certificate is live.

---

## Hosting

Any PHP 8.1+ host. Upload the folder, point the document root at it, make sure
`storage/` is writable by the web server:

```bash
chmod -R 775 storage
```

### Running from a sub-folder beside the CRM

In production the site lives in `/homepage` inside the CRM's web root, and
`CLEAN_URLS` in `app/config.php` keeps the folder name out of page URLs
(`/contact`, not `/homepage/contact`). The CRM root `.htaccess` (section 2,
"Marketing site") already rewrites the clean URLs into `homepage/index.php` —
its route list must stay in step with `PAGES` / `REDIRECTS` when a page is
added. Old `/homepage/...` links are 301'd to their clean form by `index.php`.
Assets keep loading from `/homepage/assets/`.

Apache picks up `.htaccess` as-is. On nginx, route unknown paths to the front
controller and deny the source directories:

```nginx
location / { try_files $uri $uri/ /index.php; }
location ~ ^/(app|views|storage)/ { deny all; }
```
