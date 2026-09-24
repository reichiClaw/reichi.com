# reichi.com – Relaunch

Personal website of Christian „reichi“ Reichinger, sound engineer and tour manager.
Rebuilt from the previous WordPress site as a self-contained PHP website:
semantic HTML, plain CSS, a few lines of vanilla JavaScript and PHP 8.1+ (tested on 8.3).

No CMS, database, framework, package manager, build step, external fonts, scripts,
analytics or CAPTCHA service. Everything the site needs is in this repository.

- `MIGRATION.md` – what was migrated from the old site, what changed, what is still open
- `ASSETS.md` – image manifest (source URL, local file, use, credit, concerns)

## Design direction (short)

Dark, atmospheric base with a warm off-white type colour and one restrained accent
inspired by stage lighting (`#ff4d3a`). Large system-font typography (`system-ui` stack for
copy, a monospace stack for labels, numbers and captions – a nod to patch lists and
stage plots). The existing photographs carry the composition: the hooded black-and-white
portrait opens the page, the red-lit stage photo sits full-bleed behind the biography,
the Prague/Max the Sax series forms the gallery, the studio portrait anchors the
booking section. References are set as typographic line-ups, not cards.

## Structure

```
htdocs/                  <- the web root: upload the CONTENTS of this folder via FTP
  index.php              home page (all sections, legacy anchors preserved)
  contact.php            POST handler (Post/Redirect/Get)
  impressum/index.php    legal notice (existing path kept)
  datenschutz/index.php  privacy statement (new)
  404.php                error page (wired via ErrorDocument in .htaccess)
  .htaccess              Apache rules: blocks app/ + storage/, caching, old WP redirects
  assets/css/style.css   the stylesheet (design tokens at the top)
  assets/js/main.js      nav toggle, header state, reveal, lightbox
  assets/images/         photos (jpg + webp, several widths), logos, icons
  robots.txt, sitemap.xml, site.webmanifest, favicon.ico
  app/                   PHP code – not reachable from the browser (.htaccess + PHP guard)
    bootstrap.php        shared entry (config, helpers, content, security headers)
    config.example.php   template for config.php
    config.php           your configuration (in the ZIP; not versioned in git)
    content.php          ALL editable copy, references, links, photo + logo metadata
    contact.php          form validation, rate limiting, mail()
    helpers.php          escaping, <picture> rendering, CSRF, session, icons
    templates/           header.php, footer.php, sections/ (hero, skills, about, …)
  storage/               writable runtime data – not reachable from the browser
    ratelimit/           one small JSON file per sender hash (auto-pruned)
    logs/mail.log        mail failures (timestamp + error only)
    secret.php           auto-generated secret for hashing IPs (created on first visit)
assets-src/originals/    untouched originals downloaded from the old site (do not upload)
tools/build-images.py    optional dev helper to regenerate image derivatives
tools/build-zip.sh       optional dev helper to build the upload ZIP
```

## Requirements

- Any ordinary shared web host with **PHP 8.1 or newer** (developed and tested with
  PHP 8.3). Needed extensions: `mbstring`, `filter`, `ctype`, `session`, `json` – all part
  of standard hosting builds. `gd` is **not** required; images are pre-generated.
  If the host lets you pick a PHP version in its control panel, pick 8.3 (or 8.2/8.1).
- Apache with `.htaccess` support (the default on shared hosting). Nothing else has
  to be configured on the server: no vhost, no nginx, no shell access.
- **The host's PHP `mail()` function must be allowed** for the contact form. On shared
  hosting this is normally the case. If not, the form says so honestly and shows the
  direct e-mail and phone links – nothing breaks silently. The site deliberately has no
  SMTP client.
- HTTPS (Let's Encrypt via the hosting panel). The canonical address is
  `https://www.reichi.com`.
- PHP must be able to write into `storage/` (see step 5 below).

## Installation via FTP (shared hosting)

1. Unzip `reichi-website-<date>.zip` on your computer.
2. Open `htdocs/app/config.php` in a text editor and check the values – at minimum
   `mail_to` (where inquiries go) and `mail_from` (an address **on your own domain**, e.g.
   `website@reichi.com`; create it as a mailbox or alias in the hosting panel so the
   host accepts it as sender). Save as UTF-8.
3. Connect with your FTP program (FileZilla, Cyberduck, WinSCP …) and open the web
   root of the domain – usually called `public_html`, `htdocs`, `httpdocs`, `www` or
   `web`. Delete or move away the old WordPress files.
4. Upload **everything inside** `htdocs/` – the files (`index.php`, `.htaccess`, …) and
   the folders (`assets/`, `app/`, `storage/`, `impressum/`, `datenschutz/`). Not the
   `htdocs` folder itself, and not `assets-src/`, `README.md` etc.
   Make sure hidden files (`.htaccess`) are shown and transferred (FileZilla:
   *Server → Force showing hidden files*).
5. Permissions: PHP has to write into `storage/`, `storage/ratelimit/` and
   `storage/logs/`. On most hosts (PHP runs as your FTP user) this already works with
   the default `755`. If the form later reports a rate-limit/storage problem or
   `storage/secret.php` is never created, set those three folders to `775` (or as a
   last resort `777`) in the FTP client (right click → *File permissions*).
6. Open `https://www.reichi.com/`. On the first visit the site creates
   `storage/secret.php`. Then test:
   - `https://www.reichi.com/app/content.php` and `/storage/secret.php` must show an
     empty page or *404 / 403* – never text or code.
   - Send yourself a test inquiry via the form and check that it arrives (also look
     in spam; see *Mail* below).
7. Set up a redirect from `http://` to `https://` and from the alias domains. Most
   hosting panels have a switch for this ("force HTTPS" / domain forwarding). If yours
   does not, remove the `#` in front of the `mod_rewrite` block in `.htaccess`.

The whole site lives in the web root, so **no access to Apache/nginx configuration is
required**. `app/` and `storage/` are protected twice: by their own `.htaccess`
(`Require all denied`, plus a `RedirectMatch 404` rule in the root `.htaccess`) and, in
case `.htaccess` were ignored, by a PHP guard at the top of every file in `app/` that
answers 404 to direct requests. `storage/secret.php` is a PHP file returning a string,
so a direct request produces an empty page instead of the value.

### Testing in a subfolder next to the old site

You can upload the contents of `htdocs/` into a subfolder (e.g. `public_html/neu/`)
while the old WordPress site keeps running in the root. The site detects the prefix
automatically (`base_path()` in `app/helpers.php`, derived from the request path) and
builds every internal link, asset URL, form action and redirect with it – so
`https://www.reichi.com/neu/` works, including the contact form and legal pages.

Two things are different in a subfolder, both on purpose:

- every page carries `<meta name="robots" content="noindex">`, so the test copy never
  competes with the live site in search engines;
- `robots.txt` and `sitemap.xml` are static files meant for the domain root and are
  simply not used there.

Old WordPress installs catch unknown URLs with their own `index.php`; that is why an
unpatched copy showed WordPress errors inside `style.css` – the browser asked for
`/assets/css/style.css` in the root. With the automatic prefix this no longer happens.
If detection should ever fail on an unusual server, set `'base_path' => '/neu'` in
`app/config.php`.

When going live, delete the WordPress files from the root and move the contents of
the subfolder up (or upload again into the root). Remove `base_path` from `config.php`
if you had set it.

### Optional hardening: files above the web root

If your host lets you upload one level above the web root (many do), move `app/` and
`storage/` there so they are outside the document root entirely:

```
/                      (your FTP home)
  app/
  storage/
  public_html/         (everything else from htdocs/)
```

The entry files look for `app/` inside the web root first and one level above second,
and `storage_dir` in `config.php` defaults to the folder next to `app/`. Nothing has to
be edited.

### Own server (VPS, Plesk, nginx …)

Point the document root at `htdocs/`. For nginx replicate the three things the
`.htaccess` does: `location ~ ^/(app|storage)(/|$) { return 404; }`, `error_page 404
/404.php;`, and the old-WordPress-path redirects listed in `MIGRATION.md`.

### Redirects

- `http://` → `https://www.reichi.com` (301) and the alias domains `reichi.com`,
  `reichi.at`, `christianreichinger.at`, `christianreichinger.com` → `https://www.reichi.com`.
  Preferably via the hosting panel; otherwise the commented block in `.htaccess`.
  Confirm the alias domains are all still owned before configuring.
- Old WordPress paths (`/wp-content/…`, `/feed/`, `/xmlrpc.php`) → `/` (301): already in
  `.htaccess`.
- HSTS: enable `Strict-Transport-Security` (commented in `.htaccess`) only after HTTPS
  works reliably.

### Mail

PHP's `mail()` hands the message to whatever the host configured (usually a local
sendmail/Postfix). For reliable delivery to the inbox:

- `mail_from` must be an address on the site's domain; create it in the hosting panel.
- Check the host's SPF record includes their mail servers (usually done for you).
- If the host forbids the `-f` envelope parameter (the log then shows a sendmail
  error), set `'mail_envelope_from' => null` in `config.php`.
- If `mail()` is disabled altogether, set `'mail_enabled' => false`; the form then shows
  the failure message with direct contacts instead of pretending.

## Configuration (`app/config.php`)

| key | meaning |
|---|---|
| `base_url` | canonical origin, used for `<link rel=canonical>`, Open Graph, structured data |
| `mail_to` | recipient of inquiries (default `reichi@reichi.com`) |
| `mail_from`, `mail_from_name` | sender; **must be an address on the site's domain** so SPF/DMARC pass. The visitor's address is only used in `Reply-To`. |
| `mail_envelope_from` | passed as `-f` to sendmail; set to `null` if the hoster forbids it |
| `mail_enabled` | `true` by default. `false` → the form reports „could not send“ and shows direct contacts (use locally or while the host has no mail) |
| `secret` | random string (≥ 32 chars) used to hash IP addresses for rate limiting. Leave empty to have it generated automatically into `storage/secret.php` on the first visit |
| `rate_limit` | `max_per_window` (5) per `window_seconds` (3600), `min_interval` (20 s), `retention_seconds` (86400) |
| `storage_dir` | writable directory; default `storage/` next to `app/` (blocked by `.htaccess`) |
| `base_path` | `null` = detect automatically. Set e.g. `'/neu'` only if the site runs in a subfolder and detection fails |
| `log_mail_failures` | write `storage/logs/mail.log` (timestamp + error only, never form content) |
| `force_secure_cookie` | set `true` when served exclusively over HTTPS behind a proxy that hides `HTTPS` |

`app/config.php` is git-ignored in this repository; the upload ZIP ships it as a copy
of `config.example.php`. If it is missing the site falls back to the example values.

## Editing content

Everything editable lives in **`app/content.php`**, one PHP array with comments:

- **Texts**: `hero`, `skills`, `about`, `hire`, `projects` – plain strings, escaped on output
  (do not put HTML in them).
- **References**: `portfolio.groups[*].items` – three separate lists (FOH mixed for /
  worked together with / festivals & venues). Add or remove names; counts update
  automatically.
- **Client logos**: `clients.items` – name, URL (or `null`), file in `assets/images/logos/`,
  intrinsic width/height, alt text, `light_bg => true` for logos that ship with a white
  background (they are placed on a light plate).
- **Contact details**: `contact` – address, e-mail, phone (`phone_display` for humans,
  `phone_href` for the `tel:` link), UID, alias domains.
- **Navigation**: `nav` and `nav_cta`.
- **Photos**: `photos` – see below.
- **Legal texts**: directly in `impressum/index.php` and `datenschutz/index.php`
  (they are prose, not data). Marked `[BESTÄTIGEN]` passages need owner/hoster input.

### Replacing or adding a photograph

1. Put the original into `assets-src/originals/`.
2. Add an entry to `PHOTOS` in `tools/build-images.py` (name, source file, widths; optional
   portrait crop) and run `python3 tools/build-images.py` (needs Python 3 + Pillow –
   development machine only, never on the server). It writes `name-<width>.jpg` and `.webp`
   into `htdocs/assets/images/photos/` without ever upscaling.
3. Add the photo to `photos` in `content.php` with `file`, `widths`, `width`/`height` of the
   largest variant, `alt`, `caption`, `credit`, and optionally `portrait` for the mobile crop.
4. Reference it from `hero.photo`, `about.photo`, `hire.photo` or `gallery.items`.

Without Python you can also export the sizes from any image editor as long as the file
names follow the `name-<width>.jpg` / `.webp` pattern listed in `widths`.

### Design tokens

Colours, type scale, spacing and the header height are CSS custom properties at the
top of `assets/css/style.css` (`:root`). Fonts are system stacks only.

## Contact form behaviour

- Fields: name*, e-mail*, phone (optional), subject, message*. Server-side validation with
  length limits, e-mail syntax check, control-character stripping; field-level error
  messages with `aria-describedby`/`aria-invalid`; inputs are preserved after errors.
- Post/Redirect/Get: `contact.php` always answers with `303 → /#hire`; status, errors and
  values travel once through the session (flash). Reloading never re-submits.
- Protection: CSRF token (session), invisible honeypot field, 3-second time trap,
  per-sender rate limit stored as `storage/ratelimit/<hmac(ip)>.json` with `flock()`,
  automatic pruning after `retention_seconds`. No IP addresses are stored in clear text.
- Mail: `mail()` with fixed `From`, `Reply-To` only from the validated visitor address,
  UTF-8 subject encoding, all header values stripped of CR/LF. If `mail()` returns `false`
  the visitor sees an explicit failure message with e-mail/phone alternatives; a line is
  written to `storage/logs/mail.log`. A „sent“ message means the transport **accepted**
  the mail, not that it was delivered – the UI wording says so.
- Cookie: one session cookie `reichi_session` (HttpOnly, SameSite=Lax, Secure on HTTPS),
  set on the home page and the form handler only. No other cookies.

## Security headers / CSP

Sent from PHP for every page (works on any web server): `Content-Security-Policy`
(`default-src 'none'`, `script-src 'self'`, `style-src 'self'`, `img-src 'self' data:`,
`form-action 'self'`, `frame-ancestors 'none'`, …), `X-Content-Type-Options`,
`X-Frame-Options`, `Referrer-Policy`, `Permissions-Policy`, `Cross-Origin-Opener-Policy`.
There are no inline scripts or style attributes anywhere, so no `unsafe-inline`.
The JSON-LD block is a data block and not affected by `script-src`.

## Local development

```
php -S 127.0.0.1:8080 -t htdocs
# with a fake sendmail for testing the success path:
php -S 127.0.0.1:8080 -t htdocs -d sendmail_path=/path/to/fakesendmail.sh
```

Note: PHP's built-in server serves unknown extension-less paths through `index.php`
and does not honour `.htaccess`; Apache will return 404 / use `404.php`. The PHP guards
in `app/` work everywhere, so `/app/…` answers 404 locally too.

## Building the upload ZIP

`tools/build-zip.sh` creates `dist/reichi-website-<date>.zip` with one top-level folder
`reichi-website/` containing `htdocs/` (upload its contents), the three docs and
`assets-src/originals/`. It lints all PHP files first, ships `app/config.php` as a copy of
the example (no secrets), and excludes `.git`, runtime data, `tools/` and `dist/`.
