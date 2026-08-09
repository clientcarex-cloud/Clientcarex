# ClientcareX — recovered website

A rebuild of `clientcarex.com`, reconstructed from the Wayback Machine capture
of **29 July 2025** after the original site was lost.

The original was WordPress 6.8.2 + Elementor 3.30.3 running the *Zenix* theme.
This rebuild is a **dependency-free static site** — no WordPress, no PHP, no
database, no plugins. It is faster, cheaper to host, and cannot be lost the same
way again as long as this folder is in version control.

---

## What was recovered, and what wasn't

### Recovered from the archive
- All homepage copy — hero, feature sections, three pillars, integrations,
  the full three-tier pricing tables, testimonials and all seven FAQs.
- Brand palette, taken from the original Elementor CSS and the logo artwork:
  deep green `#065845`, lime `#A9FF9B` / `#88E64A`, amber `#FFA012`,
  mint tint `#EBFEF6`, paper `#F4F7F2`, ink `#101010`.
- Typography: **Plus Jakarta Sans** (headings) and **DM Sans** (body) — the same
  two families the original loaded.
- Contact details: `+91 93908 93024`, `care@clientcarex.com` (the email was
  obfuscated by Cloudflare in the archive and had to be decoded).
- 22 image assets, in `assets/img/` — logo, favicon, client logos, illustrations
  and integration icons.
- Navigation structure and the five-column footer.
- App links: login and register still point at `clientcarex.com/ccx/authentication/*`.

### Not recoverable
Only the homepage was ever archived, so these had **no source to restore from**
and were written fresh in the original's voice and style:

| Page | Status |
|---|---|
| `features.html` | Written from the recovered module and pricing lists |
| `pricing.html` | Wraps the recovered pricing tables + FAQ |
| `how-it-works.html` | Built from the recovered 3-phase plan in the FAQ |
| `challenge.html` | Built from the recovered 90-day challenge copy |
| `blog.html` | Empty state — no posts existed in the archive |
| `contact.html` | New (the original had no contact page) |
| `privacy.html`, `terms.html`, `refund.html` | **Templates — need legal review** |

Four testimonial avatar images (`3.png`, `4.png`, `5.png`, `team.png`) returned
404 from the archive, so reviewer initials are shown in coloured circles instead.

### Deliberate corrections to the original copy
The archived page had errors that were fixed rather than reproduced:

- **"Zenix" appeared twice in the body copy** — the WordPress theme name had
  leaked into the placeholder text ("Discover how Zenix can enhance…",
  "Zenix is the best"). Replaced with ClientcareX.
- **Duplicate Slack integration card** — two identical Slack cards were shown,
  the second using the Zapier icon. The second is now correctly Zapier.
- Typos: `cusomter` → customer, `powerfull` → powerful,
  `Annoucement` → Announcement, `Link Shortly` → Link Shortener.

---

## Working on the site

Pages are assembled from fragments so the header, footer and repeated blocks
live in one place.

```
src/pages/*.html      page bodies (the part inside <main>)
src/partials/*.html   reusable blocks: plans, faq
build.py              head + header + footer, and the page list
assets/               css, js, images
*.html                GENERATED — do not edit directly
```

Edit anything under `src/`, `assets/` or `build.py`, then rebuild:

```bash
python3 build.py
```

That regenerates all 11 pages plus `sitemap.xml` and `robots.txt`.

> **Editing a root `.html` file directly will be overwritten on the next build.**
> Change the matching file in `src/pages/` instead.

### Preview locally

```bash
python3 -m http.server 4173
```

Then open <http://localhost:4173>.

### Add a page
Add an entry to `PAGES` in `build.py` (slug, nav key, title, description) and
create `src/pages/<slug>.html` with just the body content. Add it to `NAV_ITEMS`
too if it belongs in the header.

### Include a shared block
`{{> plans }}` in any page fragment pulls in `src/partials/plans.html`.

### Add a blog post
Drop a card into `src/pages/blog.html` inside a `<div class="grid grid--3">`:

```html
<article class="post">
  <div class="post__thumb">Post title</div>
  <div class="post__body">
    <div class="post__meta"><span>Automation</span><span>5 min read</span></div>
    <h3>How we cut follow-up time by 70%</h3>
    <p>Short summary of the post.</p>
    <a class="link-arrow" href="posts/slug.html">Read more</a>
  </div>
</article>
```

---

## Before this goes live

1. **Have the three legal pages reviewed.** They are drafts, and each carries a
   visible warning banner that must be removed once the real text is in.
2. **Wire up the contact form.** `contact.html` has no back end — it currently
   tells the visitor to email or call instead of silently dropping the enquiry.
   Point it at Formspree, your own endpoint, or the ClientcareX ticket API, then
   delete the `data-form` handler in `assets/js/main.js`.
3. **Fill in the social links.** The four footer icons are `href="#"` placeholders.
4. **Confirm the two guarantee windows.** The homepage advertises both a 30-day
   money back guarantee and a 90-day guarantee; make sure the refund policy
   matches what you actually offer.
5. **Check the client logos.** Eight are shown in the "Trusted by 100+ Teams"
   row — confirm you still have permission to use each one.

## Hosting

Any static host works — Netlify, Vercel, Cloudflare Pages, GitHub Pages, S3, or
plain nginx/Apache. Upload the folder; there is nothing to install or configure.
Point `404.html` at your host's not-found handler.

## Recovery source

`https://web.archive.org/web/20250729025946/https://clientcarex.com/`
