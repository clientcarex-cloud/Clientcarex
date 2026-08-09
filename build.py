#!/usr/bin/env python3
"""
ClientcareX static site builder.

Wraps each body fragment in `src/pages/` with the shared head, header and
footer, then writes plain static HTML to the project root. No dependencies —
run `python3 build.py` after editing anything under `src/`.
"""

from __future__ import annotations

import re
from pathlib import Path

ROOT = Path(__file__).parent
PAGES_DIR = ROOT / "src" / "pages"
PARTIALS_DIR = ROOT / "src" / "partials"

# `{{> name }}` in a page fragment pulls in src/partials/name.html
INCLUDE_RE = re.compile(r"[ \t]*\{\{>\s*([\w./-]+)\s*\}\}")

SITE_URL = "https://clientcarex.com"
PHONE = "+91 93908 93024"
PHONE_HREF = "+919390893024"
EMAIL = "care@clientcarex.com"
APP_LOGIN = "https://clientcarex.com/ccx/authentication/login"
APP_REGISTER = "https://clientcarex.com/ccx/authentication/register"

# --------------------------------------------------------------------------
# Pages: slug -> metadata. `nav` marks which nav item is the current page.
# --------------------------------------------------------------------------
PAGES = [
    {
        "slug": "index",
        "nav": "home",
        "title": "ClientcareX — AI Driven ERP Software & Automation",
        "description": (
            "Automate your business and scale fast with ClientcareX — an AI and "
            "data driven ERP that unifies leads, sales, HR, support and billing "
            "in one place."
        ),
    },
    {
        "slug": "features",
        "nav": "features",
        "title": "Features — ClientcareX AI Driven ERP",
        "description": (
            "Every module in ClientcareX: leads and CRM, sales, HR and payroll, "
            "support ticketing, automation, accounting and AI reporting."
        ),
    },
    {
        "slug": "pricing",
        "nav": "pricing",
        "title": "Pricing — ClientcareX Plans from ₹499/user/month",
        "description": (
            "Professional, Business and Enterprise plans for ClientcareX. "
            "Transparent per-user pricing with a 30-day money back guarantee."
        ),
    },
    {
        "slug": "how-it-works",
        "nav": "how",
        "title": "How It Works — ClientcareX Implementation in 3 Phases",
        "description": (
            "Understanding and workflows, automation and customisation, then "
            "implementation and scaling — how ClientcareX transforms operations "
            "in 90 days."
        ),
    },
    {
        "slug": "challenge",
        "nav": "challenge",
        "title": "90-Day Business Transformation Challenge — ClientcareX",
        "description": (
            "Our bold promise: automate, streamline and transform your business "
            "operations with AI and ERP in 90 days — or your money back."
        ),
    },
    {
        "slug": "blog",
        "nav": "blog",
        "title": "Blog & News — ClientcareX",
        "description": "Automation playbooks, ERP guides and product news from the ClientcareX team.",
    },
    {
        "slug": "contact",
        "nav": "",
        "title": "Contact & Request a Demo — ClientcareX",
        "description": "Talk to the ClientcareX team. Book a demo, ask about pricing or get implementation support.",
    },
    {
        "slug": "privacy",
        "nav": "",
        "title": "Privacy Policy — ClientcareX",
        "description": "How ClientcareX Private Limited collects, uses and protects your data.",
    },
    {
        "slug": "terms",
        "nav": "",
        "title": "Terms & Conditions — ClientcareX",
        "description": "The terms governing your use of ClientcareX software and services.",
    },
    {
        "slug": "refund",
        "nav": "",
        "title": "Refund Policy — ClientcareX",
        "description": "ClientcareX refund terms, including the 30-day money back guarantee.",
    },
    {
        "slug": "404",
        "nav": "",
        "title": "Page not found — ClientcareX",
        "description": "The page you were looking for doesn't exist.",
        "noindex": True,
    },
]

NAV_ITEMS = [
    ("home", "Home", "index.html", False),
    ("features", "Features", "features.html", False),
    ("pricing", "Pricing", "pricing.html", False),
    ("how", "How It Works", "how-it-works.html", False),
    ("challenge", "🔥 90 Day Challenge", "challenge.html", True),
    ("blog", "Blog", "blog.html", False),
]


def nav_markup(current: str) -> str:
    out = []
    for key, label, href, hot in NAV_ITEMS:
        classes = "nav__link" + (" nav__link--hot" if hot else "")
        aria = ' aria-current="page"' if key == current else ""
        out.append(
            f'          <li><a class="{classes}" href="{href}"{aria}>{label}</a></li>'
        )
    return "\n".join(out)


TOPBAR = f"""    <div class="topbar">
      <div class="container topbar__inner">
        <p class="topbar__promo">
          Go Big on Automation! 🔥
          <a href="challenge.html">Join the 90-Day Business Transformation Challenge</a> 🚀
        </p>
        <ul class="topbar__contact">
          <li>
            <a href="tel:{PHONE_HREF}">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.9.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
              Call: {PHONE}
            </a>
          </li>
          <li>
            <a href="mailto:{EMAIL}">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-10 6L2 7"/></svg>
              {EMAIL}
            </a>
          </li>
        </ul>
      </div>
    </div>"""


def header_markup(current: str) -> str:
    return f"""    <header class="site-header">
      <div class="container site-header__inner">
        <a class="brand" href="index.html" aria-label="ClientcareX home">
          <img src="assets/img/ClientcareX-Logo.png" alt="ClientcareX" width="500" height="100">
        </a>

        <button class="nav-toggle" type="button" aria-expanded="false"
                aria-controls="primary-nav" aria-label="Toggle navigation">
          <span class="nav-toggle__bar"></span>
        </button>

        <nav class="nav" id="primary-nav" data-open="false" aria-label="Primary">
          <ul class="nav__list">
{nav_markup(current)}
          </ul>
          <div class="nav__actions">
            <a class="btn btn--ghost btn--sm" href="{APP_LOGIN}">Login</a>
            <a class="btn btn--sm" href="{APP_REGISTER}">Sign Up</a>
          </div>
        </nav>

        <div class="header-actions">
          <a class="btn btn--ghost btn--sm" href="{APP_LOGIN}">Login</a>
          <a class="btn btn--sm" href="{APP_REGISTER}">Sign Up</a>
        </div>
      </div>
    </header>"""


FOOTER = f"""    <footer class="site-footer">
      <div class="container">
        <div class="footer__top">
          <div class="footer__brand">
            <img src="assets/img/ClientcareX-Logo.png" alt="ClientcareX" width="500" height="100">
            <p>
              AI driven ERP and business automation for teams that want to grow
              without adding headcount. From leads to customer satisfaction,
              every workflow in one place.
            </p>
            <div class="social">
              <a href="#" aria-label="ClientcareX on LinkedIn">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M4.98 3.5a2.5 2.5 0 1 1 0 5 2.5 2.5 0 0 1 0-5zM3 9h4v12H3zM9 9h3.8v1.7h.05c.53-.95 1.83-1.95 3.77-1.95C20.4 8.75 21 11 21 14.1V21h-4v-6.1c0-1.45-.03-3.32-2.02-3.32-2.02 0-2.33 1.58-2.33 3.21V21H9z"/></svg>
              </a>
              <a href="#" aria-label="ClientcareX on X">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M18.9 2H22l-7.1 8.1L23.2 22h-6.6l-5.2-6.8L5.5 22H2.4l7.6-8.7L1.2 2h6.8l4.7 6.2zm-1.1 18h1.7L7.3 3.8H5.5z"/></svg>
              </a>
              <a href="#" aria-label="ClientcareX on Facebook">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M13.5 21v-8h2.7l.4-3.1h-3.1V7.9c0-.9.25-1.5 1.55-1.5H16.7V3.6c-.3-.04-1.3-.13-2.47-.13-2.45 0-4.13 1.5-4.13 4.24V9.9H7.4V13h2.7v8z"/></svg>
              </a>
              <a href="#" aria-label="ClientcareX on Instagram">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="3.6"/><circle cx="17.2" cy="6.8" r="1.1" fill="currentColor" stroke="none"/></svg>
              </a>
            </div>
          </div>

          <div class="footer__col">
            <h2>Company</h2>
            <ul>
              <li><a href="contact.html">About Us</a></li>
              <li><a href="contact.html">Our Team</a></li>
              <li><a href="challenge.html">Our Program</a></li>
              <li><a href="contact.html">Work With Us</a></li>
            </ul>
          </div>

          <div class="footer__col">
            <h2>Help &amp; Support</h2>
            <ul>
              <li><a href="contact.html">Help center</a></li>
              <li><a href="contact.html">Expert team</a></li>
              <li><a href="contact.html">Contact Us</a></li>
              <li><a href="mailto:{EMAIL}">Report Abuse</a></li>
            </ul>
          </div>

          <div class="footer__col">
            <h2>Information</h2>
            <ul>
              <li><a href="index.html#reviews">Testimonials</a></li>
              <li><a href="pricing.html">Pricing Plans</a></li>
              <li><a href="contact.html">Referral Program</a></li>
              <li><a href="features.html">Payment Gateway</a></li>
            </ul>
          </div>

          <div class="footer__col">
            <h2>Useful Links</h2>
            <ul>
              <li><a href="blog.html">Blog &amp; News</a></li>
              <li><a href="how-it-works.html">How It Works</a></li>
              <li><a href="features.html">Our Features</a></li>
              <li><a href="pricing.html">Compare Plans</a></li>
            </ul>
          </div>
        </div>

        <div class="footer__bottom">
          <p>Copyright © <span data-year>2025</span> Clientcarex Private Limited. All Rights Reserved.</p>
          <ul class="footer__legal">
            <li><a href="privacy.html">Privacy Policy</a></li>
            <li><a href="terms.html">Terms &amp; Conditions</a></li>
            <li><a href="refund.html">Refund policy</a></li>
          </ul>
        </div>
      </div>
    </footer>"""


LAYOUT = """<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{title}</title>
<meta name="description" content="{description}">{robots}
<link rel="canonical" href="{site_url}/{canonical}">

<meta property="og:type" content="website">
<meta property="og:site_name" content="ClientcareX">
<meta property="og:title" content="{title}">
<meta property="og:description" content="{description}">
<meta property="og:url" content="{site_url}/{canonical}">
<meta property="og:image" content="{site_url}/assets/img/ClientcareX-Logo.png">
<meta name="twitter:card" content="summary_large_image">

<link rel="icon" href="assets/img/favicon-32x32.png" sizes="32x32">
<link rel="apple-touch-icon" href="assets/img/favicon-32x32.png">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet"
      href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,400;9..40,500;9..40,700&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap">
<link rel="stylesheet" href="assets/css/style.css">
<script>document.documentElement.classList.add("js");</script>
</head>
<body>
<a class="skip-link" href="#content">Skip to content</a>

{topbar}

{header}

<main id="content">
{body}
</main>

{footer}

<script src="assets/js/main.js" defer></script>
</body>
</html>
"""


def expand_includes(text: str, depth: int = 0) -> str:
    """Replace `{{> name }}` with src/partials/name.html, recursively."""
    if depth > 5:
        raise SystemExit("Include nesting too deep — check for a cycle.")

    def swap(match: re.Match) -> str:
        partial = PARTIALS_DIR / f"{match.group(1)}.html"
        if not partial.exists():
            raise SystemExit(f"Missing partial: {partial}")
        return expand_includes(partial.read_text(encoding="utf-8").rstrip(), depth + 1)

    return INCLUDE_RE.sub(swap, text)


def build() -> None:
    written = []
    for page in PAGES:
        src = PAGES_DIR / f"{page['slug']}.html"
        if not src.exists():
            raise SystemExit(f"Missing source fragment: {src}")

        canonical = "" if page["slug"] == "index" else f"{page['slug']}.html"
        html = LAYOUT.format(
            title=page["title"],
            description=page["description"],
            robots='\n<meta name="robots" content="noindex">' if page.get("noindex") else "",
            site_url=SITE_URL,
            canonical=canonical,
            topbar=TOPBAR,
            header=header_markup(page["nav"]),
            footer=FOOTER,
            body=expand_includes(src.read_text(encoding="utf-8").rstrip()),
        )
        out = ROOT / f"{page['slug']}.html"
        out.write_text(html, encoding="utf-8")
        written.append(out.name)

    write_sitemap()
    print(f"Built {len(written)} pages: {', '.join(written)}")
    print("Wrote sitemap.xml and robots.txt")


def write_sitemap() -> None:
    urls = []
    for page in PAGES:
        if page.get("noindex"):
            continue
        loc = SITE_URL + ("/" if page["slug"] == "index" else f"/{page['slug']}.html")
        priority = "1.0" if page["slug"] == "index" else "0.7"
        urls.append(f"  <url>\n    <loc>{loc}</loc>\n    <priority>{priority}</priority>\n  </url>")

    (ROOT / "sitemap.xml").write_text(
        '<?xml version="1.0" encoding="UTF-8"?>\n'
        '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">\n'
        + "\n".join(urls)
        + "\n</urlset>\n",
        encoding="utf-8",
    )

    (ROOT / "robots.txt").write_text(
        f"User-agent: *\nAllow: /\n\nSitemap: {SITE_URL}/sitemap.xml\n",
        encoding="utf-8",
    )


if __name__ == "__main__":
    build()
