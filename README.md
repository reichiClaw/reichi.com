# reichi.com – Relaunch

Personal website of Christian „reichi“ Reichinger, sound engineer and tour manager.
Rebuilt from the previous WordPress site as a self-contained PHP website:
semantic HTML, plain CSS, a few lines of vanilla JavaScript and PHP 8.1+ (tested on 8.3).

No CMS, database, framework, package manager, build step, external fonts, scripts,
analytics or CAPTCHA service. Everything the site needs is in this repository.

- `MIGRATION.md` – what was migrated from the old site, what changed, what is still open
- `ASSETS.md` – image manifest (source URL, local file, use, credit, concerns)

## Design direction (short)

Dark, atmospheric base with a fine film grain over the whole page (`body::after`, tile
`assets/images/grain.png` from `tools/build-grain.py`; delete that rule to go back to flat
colour), a warm off-white type colour and one restrained accent inspired by stage
lighting (`#ff4d3a`). Large system-font typography (`system-ui` stack for
copy, a monospace stack for labels, numbers and captions – a nod to patch lists and
stage plots). The existing photographs carry the composition: the hooded black-and-white
portrait opens the page, the red-lit stage photo sits full-bleed behind the biography,
the Prague/Max the Sax series forms the gallery, the studio portrait anchors the
booking section. References are set as typographic line-ups, not cards.

Motion is limited to opacity/transform (plus one small canvas) and runs only under
`prefers-reduced-motion: no-preference`: a staggered hero entrance and section reveals on
scroll, and – on devices with a real mouse only – two pointer reactions in the hero
(section 7 of `main.js`): the red stage glow (`.hero__spot`) pans slowly toward the pointer
like a follow spot while portrait and text shift a few pixels against each other, and the
thin stage-edge line beside the portrait becomes an oscilloscope trace (`.hero__scope`)
that ripples when the pointer moves near it and settles back to a straight line. Touch
devices, reduced motion and no-JS all get the unchanged static hero (CSS line, breathing
glow). Both effects stop computing as soon as they have settled or the hero leaves the
viewport. To drop them, delete section 7 of `main.js` (the `.hero__spot` element must stay –
it is the stage glow itself; the `.hero__scope` canvas can go).

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

## Search Console (SEO-08 – owner action, no code change)

The site itself is ready for indexing (canonicals, `robots.txt`, `sitemap.xml`, 404 pages,
structured data). What only the owner can do:

1. Open [Google Search Console](https://search.google.com/search-console) and add a
   **Domain property** `reichi.com` (covers http/https, www/non-www and the alias domains
   are separate). Verify via the DNS TXT record Google shows you – enter it at the domain
   provider / hosting panel under DNS. No file or meta tag on the website is needed.
2. *Sitemaps* → submit `https://www.reichi.com/sitemap.xml`.
3. *URL inspection* → check `https://www.reichi.com/` and request indexing once.
4. After a few weeks, look at: *Performance* (which queries bring impressions – brand
   vs. service terms, click-through rate of the home page), *Pages* (old WordPress URLs
   should show as redirected, nothing "Not found" that matters), *Core Web Vitals* (real
   user data, which no lab test replaces), *Mobile usability*.
5. Optional: Bing Webmaster Tools can import the verified Search Console property.

Never paste verification codes or account credentials into chats or the repository.

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
| `spam` | built-in content filter, see [Spam protection](#spam-protection): `min_seconds` (5), `max_links` (1, only with JavaScript; 0 without), `reject_scripts` (Unicode scripts that mark a message as spam when they make up more than 40 % of the letters), `blocked_terms` (case-insensitive substrings), `log` (write `storage/logs/spam.log`) |
| `turnstile` | optional Cloudflare Turnstile: `site_key`, `secret_key` (both empty = off), `appearance` (`'always'` or `'interaction-only'`) |

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
- Protection: CSRF token (session), two invisible honeypot fields, time trap, content
  filter, per-sender rate limit stored as `storage/ratelimit/<hmac(ip)>.json` with
  `flock()`, automatic pruning after `retention_seconds`, optional Cloudflare Turnstile.
  Details in [Spam protection](#spam-protection). No IP addresses are stored in clear text.
- Mail: `mail()` with fixed `From`, `Reply-To` only from the validated visitor address,
  UTF-8 subject encoding, all header values stripped of CR/LF. If `mail()` returns `false`
  the visitor sees an explicit failure message with e-mail/phone alternatives; a line is
  written to `storage/logs/mail.log`. A „sent“ message means the transport **accepted**
  the mail, not that it was delivered – the UI wording says so.
- Cookie: one session cookie `reichi_session` (HttpOnly, SameSite=Lax, Secure on HTTPS),
  set on the home page and the form handler only. No other cookies.

## Spam protection

The handler `contact.php` runs these checks in order. Everything up to and including the
rate limit works without any external service or account.

1. **Honeypots** – two hidden fields (`website`, `email_confirm`) that humans never see.
   Filled → the bot gets a fake „sent“ page, nothing is mailed.
2. **Time trap** – the session remembers when the form was rendered; submissions faster
   than `spam.min_seconds` (default 5 s) are treated like honeypot hits.
3. **Validation + CSRF** as before.
4. **Content filter** (`contact_spam_check()` in `app/contact.php`) – rejects a message
   with the visible status „Die Nachricht wurde als Werbung eingestuft“ and keeps the
   entered values, so a wrongly caught human can rephrase. Reasons:
   - `markup` – HTML anchors, `<script>`, BBCode `[url]`/`[link]`;
   - `links` – more than `spam.max_links` URLs in subject + message. Browsers with
     JavaScript send a `js_token` (derived from the CSRF token); without it the
     allowance is **0** links, because almost all form bots do not run JavaScript;
   - `name` – name contains a URL or no letter at all;
   - `term` – one of `spam.blocked_terms` occurs (case-insensitive substring, so keep the
     list short and specific; „seo“ also matches „Seoul“);
   - `script` – at least 10 letters and more than 40 % of them in one of
     `spam.reject_scripts` (default Cyrillic, Han, Hangul, Hiragana, Katakana, Thai).
     Remove a script from the list if you expect real inquiries in that language.
5. **Rate limit** as described above.
6. **Cloudflare Turnstile** (only when configured, see below).

Every rejection is appended to `storage/logs/spam.log` as
`[date] reason <12-char hash of the hashed IP>` – no form content, no clear IP. The file
rotates to `spam.log.1` at 512 KB. Set `spam.log` to `false` to disable. Reading the log
after a few weeks tells you which rule catches what and whether a rule should be tuned.

### Cloudflare Turnstile (optional)

Turnstile is Cloudflare's CAPTCHA replacement: usually an invisible check, sometimes a
one-click „Ich bin ein Mensch“ box, never picture puzzles. It is free and **does not require
the domain to be hosted or proxied by Cloudflare** – only a free account.

1. Sign up / log in at <https://dash.cloudflare.com/>, open **Turnstile**, „Add widget“.
2. Widget name e.g. `reichi.com Kontaktformular`; hostnames `reichi.com` and `www.reichi.com`
   (add the test subfolder host if you test elsewhere); widget mode **Managed**.
3. Copy the **Site Key** and **Secret Key** into `app/config.php`:

```php
'turnstile' => [
    'site_key'   => '0x4AAAAAAA…',
    'secret_key' => '0x4AAAAAAA…',
    'appearance' => 'always',        // or 'interaction-only'
],
```

What changes as soon as both keys are set:

- The form gets a placeholder for the widget. The Cloudflare script
  `https://challenges.cloudflare.com/turnstile/v0/api.js` is loaded **only when the
  visitor focuses or touches the form** – plain page views make no external request.
- The submit button waits for the Turnstile token („Sicherheitsprüfung läuft …“) and then
  submits automatically.
- `contact.php` verifies the token server-side against
  `https://challenges.cloudflare.com/turnstile/v0/siteverify` (cURL or stream wrapper,
  8 s timeout). It **fails closed**: missing/invalid token, wrong hostname/action, or an
  unreachable Cloudflare API → status „Die Sicherheitsprüfung wurde nicht bestätigt“, the
  message is not sent, the reason is written to `spam.log` (`turnstile:<error-codes>`).
- The CSP is widened for exactly two directives: `script-src` and `frame-src` gain
  `https://challenges.cloudflare.com`. Everything else stays `'self'`.
- The privacy page shows an additional „Cloudflare Turnstile“ paragraph and the form's
  privacy note mentions Turnstile. Both disappear again when the keys are removed.
- Without JavaScript the form can no longer be submitted (a `<noscript>` note says so and
  points to e-mail/phone). Without Turnstile the no-JS path keeps working.

`appearance => 'interaction-only'` hides the widget unless Cloudflare needs a click;
`'always'` shows the small „Erfolg“ box, which makes the wait state easier to understand.

Testing without a real account: Cloudflare's documented test keys
`1x00000000000000000000AA` (site) / `1x0000000000000000000000000000000AA` (secret) always
pass, `2x0000000000000000000000000000000AA` (secret) always fails. Never leave them in a
live configuration – the always-pass secret accepts every token.

## Security headers / CSP

Sent from PHP for every page (works on any web server): `Content-Security-Policy`
(`default-src 'none'`, `script-src 'self'`, `style-src 'self'`, `img-src 'self' data:`,
`form-action 'self'`, `frame-ancestors 'none'`, …), `X-Content-Type-Options`,
`X-Frame-Options`, `Referrer-Policy`, `Permissions-Policy`, `Cross-Origin-Opener-Policy`.
There are no inline scripts or style attributes anywhere, so no `unsafe-inline`.
The JSON-LD block is a data block and not affected by `script-src`. With Turnstile
configured, `script-src` and `frame-src` additionally allow `https://challenges.cloudflare.com`.

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
