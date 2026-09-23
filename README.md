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
app/
  bootstrap.php          shared entry (config, helpers, content, security headers)
  config.example.php     example configuration – copy to config.php
  config.php             your real configuration (not versioned)
  content.php            ALL editable copy, references, links, photo + logo metadata
  contact.php            form validation, rate limiting, mail()
  helpers.php            escaping, <picture> rendering, CSRF, session, icons
  templates/
    header.php, footer.php
    sections/            hero, skills, about, portfolio, gallery, projects, hire
public/                  the web root (document root)
  index.php              home page (all sections, legacy anchors preserved)
  contact.php            POST handler (Post/Redirect/Get)
  impressum/index.php    legal notice (existing path kept)
  datenschutz/index.php  privacy statement (new)
  404.php                error page (wired via .htaccess / nginx error_page)
  assets/css/style.css   the stylesheet (design tokens at the top)
  assets/js/main.js      nav toggle, header state, reveal, lightbox
  assets/images/         photos (jpg + webp, several widths), logos, icons
  robots.txt, sitemap.xml, site.webmanifest, favicon.ico, .htaccess (optional)
storage/                 writable, outside the web root: ratelimit/ and logs/
assets-src/originals/    untouched originals downloaded from the old site
tools/build-images.py    optional dev helper to regenerate image derivatives
tools/build-zip.sh       optional dev helper to build the upload ZIP
```

## Requirements

- PHP **8.1 or newer** (developed and tested with PHP 8.3) with the `mbstring`,
  `filter`, `ctype`, `session` and `json` extensions (all default in standard builds).
  `gd` is **not** required at runtime; images are pre-generated.
- A web server (Apache, nginx, LiteSpeed, Caddy, …) with PHP.
- **A working mail transport on the server** for the contact form: PHP's `mail()`
  hands the message to the local `sendmail` binary (Postfix, Exim, msmtp …) or to whatever
  the hoster configured in `sendmail_path`. Without this no e-mail can be delivered –
  the form then reports failure honestly and shows the direct e-mail and phone links.
  The application does not include an SMTP client on purpose.
- HTTPS. The canonical address is `https://www.reichi.com`; redirects from `http://`
  and from the alias domains must be configured on the server (see below).
- A writable `storage/` directory (or the fallback `sys_get_temp_dir()/reichi-storage`).

## Installation

### A. Configurable document root (VPS, managed server, Plesk, Caddy …)

1. Upload the whole project, e.g. to `/var/www/reichi/`.
2. Point the document root of the vhost to `/var/www/reichi/public`.
3. `cp app/config.example.php app/config.php` and edit (see *Configuration*).
4. Make `storage/` writable by the PHP user: `chmod 750 storage storage/ratelimit storage/logs`
   and `chown` to the web user, or `chmod 770` if the web user is in your group.
5. Open the site, submit a test inquiry, check that it arrives.

### B. Shared hosting with `public_html` (or `htdocs`, `httpdocs`, `www`)

Typical layout on the server:

```
/home/USER/
  app/                 <- upload the app/ folder here (outside the web root)
  storage/             <- upload storage/ here, make it writable
  public_html/         <- upload the CONTENTS of public/ here
```

`public/index.php` etc. reference the application with `dirname(__DIR__) . '/app'`,
i.e. one directory above the web root – so `app/` and `storage/` simply need to be
siblings of `public_html/`. Nothing else has to be adjusted.

If your hoster does not allow files above the web root, place `app/` and `storage/`
inside `public_html/` **and** protect them (Apache: put a `.htaccess` with
`Require all denied` into each; nginx: `location ~ ^/(app|storage)/ { deny all; }`).
Then adjust the bootstrap path in the five entry files, because `app/` is now one level
lower than expected:

| file | change `require` to |
|---|---|
| `index.php`, `contact.php`, `404.php` | `__DIR__ . '/app/bootstrap.php'` |
| `impressum/index.php`, `datenschutz/index.php` | `dirname(__DIR__) . '/app/bootstrap.php'` |

and set `'storage_dir' => __DIR__ . '/../storage'` in `app/config.php` if needed.
This is the fallback, not the recommended setup.

### nginx snippet

```nginx
server {
    server_name www.reichi.com;
    root /var/www/reichi/public;
    index index.php;

    location / { try_files $uri $uri/ =404; }
    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }
    error_page 404 /404.php;
    location ~ /\. { deny all; }           # dotfiles such as .htaccess
    location ~* \.(jpg|webp|png|svg|ico|css|js)$ { expires 1y; add_header Cache-Control "public, immutable"; }
    gzip on; gzip_types text/html text/css application/javascript image/svg+xml application/manifest+json;
}
```

### Redirects (server-level, documented separately from the app)

- `http://` → `https://www.reichi.com` (301)
- `reichi.com`, `reichi.at`, `christianreichinger.at`, `christianreichinger.com` →
  `https://www.reichi.com` (301). The old imprint lists these alias domains; confirm they
  are all still owned before configuring.
- Old WordPress paths (`/wp-content/…`, `/feed/`, `/xmlrpc.php`) → `/` (301);
  a ready-made rule set is in `public/.htaccess`. The canonical https/www redirect is
  included there as a commented block – enable it once the certificate works.
- HSTS: enable `Strict-Transport-Security` only after HTTPS works reliably.

## Configuration (`app/config.php`)

| key | meaning |
|---|---|
| `base_url` | canonical origin, used for `<link rel=canonical>`, Open Graph, structured data |
| `mail_to` | recipient of inquiries (default `reichi@reichi.com`) |
| `mail_from`, `mail_from_name` | sender; **must be an address on the site's domain** so SPF/DMARC pass. The visitor's address is only used in `Reply-To`. |
| `mail_envelope_from` | passed as `-f` to sendmail; set to `null` if the hoster forbids it |
| `mail_enabled` | `false` in the example config → the form reports „could not send“ and shows direct contacts. **Set to `true` for production.** |
| `secret` | random string (≥ 32 chars, e.g. `openssl rand -hex 32`) used to hash IP addresses for rate limiting |
| `rate_limit` | `max_per_window` (5) per `window_seconds` (3600), `min_interval` (20 s), `retention_seconds` (86400) |
| `storage_dir` | writable directory outside the web root |
| `log_mail_failures` | write `storage/logs/mail.log` (timestamp + error only, never form content) |
| `force_secure_cookie` | set `true` when served exclusively over HTTPS behind a proxy that hides `HTTPS` |

`app/config.php` is git-ignored. If it is missing the site falls back to the example
config with mail disabled.

## Editing content

Everything editable lives in **`app/content.php`**, one PHP array with comments:

- **Texts**: `hero`, `skills`, `about`, `hire`, `projects` – plain strings, escaped on output
  (do not put HTML in them).
- **References**: `portfolio.groups[*].items` – three separate lists (FOH mixed for /
  worked together with / festivals & venues). Add or remove names; counts update
  automatically.
- **Client logos**: `clients.items` – name, URL (or `null`), file in `public/assets/images/logos/`,
  intrinsic width/height, alt text, `light_bg => true` for logos that ship with a white
  background (they are placed on a light plate).
- **Contact details**: `contact` – address, e-mail, phone (`phone_display` for humans,
  `phone_href` for the `tel:` link), UID, alias domains.
- **Navigation**: `nav` and `nav_cta`.
- **Photos**: `photos` – see below.
- **Legal texts**: directly in `public/impressum/index.php` and `public/datenschutz/index.php`
  (they are prose, not data). Marked `[BESTÄTIGEN]` passages need owner/hoster input.

### Replacing or adding a photograph

1. Put the original into `assets-src/originals/`.
2. Add an entry to `PHOTOS` in `tools/build-images.py` (name, source file, widths; optional
   portrait crop) and run `python3 tools/build-images.py` (needs Python 3 + Pillow –
   development machine only, never on the server). It writes `name-<width>.jpg` and `.webp`
   into `public/assets/images/photos/` without ever upscaling.
3. Add the photo to `photos` in `content.php` with `file`, `widths`, `width`/`height` of the
   largest variant, `alt`, `caption`, `credit`, and optionally `portrait` for the mobile crop.
4. Reference it from `hero.photo`, `about.photo`, `hire.photo` or `gallery.items`.

Without Python you can also export the sizes from any image editor as long as the file
names follow the `name-<width>.jpg` / `.webp` pattern listed in `widths`.

### Design tokens

Colours, type scale, spacing and the header height are CSS custom properties at the
top of `public/assets/css/style.css` (`:root`). Fonts are system stacks only.

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
php -S 127.0.0.1:8080 -t public
# with a fake sendmail for testing the success path:
php -S 127.0.0.1:8080 -t public -d sendmail_path=/path/to/fakesendmail.sh
```

Note: PHP's built-in server serves unknown extension-less paths through `index.php`
(and does not honour `.htaccess`); Apache/nginx will return 404 / use `404.php`.

## Building the upload ZIP

`tools/build-zip.sh` creates `dist/reichi-website-<date>.zip` containing `app/`, `public/`,
`storage/` (empty, with `.gitkeep`), the docs and `assets-src/originals/`. Excluded:
`.git`, `app/config.php`, runtime data, `tools/`, `dist/`.
