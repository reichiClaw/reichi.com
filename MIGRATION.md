# Migration report – reichi.com

Source: `https://www.reichi.com/` and `https://www.reichi.com/impressum/`, fetched on
2026-09-23 (WordPress 6.0.1, WPBakery, Slider Revolution 5.4.8.1, Jetpack image CDN).
Both pages were retrieved completely (HTTP 200, 121 KB / 77 KB of HTML), the rendered
HTML was parsed for text, links, images, `srcset`, `data-lazyload` slider sources and
CSS `background-image` declarations. Nothing from the old installation is used at runtime.

## 1. Audit inventory (old site)

### Pages, paths, anchors

| old | new | note |
|---|---|---|
| `/` | `/` | single page, same anchors |
| `/impressum/` | `/impressum/` | kept |
| `#skills` `#portfolio` `#contact` `#hire` `#bleedingstar` `#rstream` `#clients` | all kept | plus new `#about` `#fotos` `#projekte` |
| `/wp-content/…`, `/feed/`, `/xmlrpc.php`, `/wp-json/…` | 301 → `/` | rule set in `public/.htaccess` (Apache) – must be replicated for nginx |
| – | `/datenschutz/` | new, privacy statement split out of the imprint |
| – | `/404.php` | new error page |

### Content found on the home page (in order)

1. Header: logo icons (black/white PNG, 100 px), nav: Skills, Portfolio, Contact, Hire Me!, BleedingStar, R-Stream, Instagram.
2. Hero (full-height background image `ChristianReichingerFrontpage.jpg`): „CHRISTIAN REICHINGER“, „Sound Engineer | Tour Manager | Instagramer“, Instagram icon link. Duplicated in the HTML for desktop/mobile columns.
3. Skills: „Skills“ / „Knowledge and experience is the key!“ / five items with icon-font icons and empty descriptions: Live Sound Mixing, Live Sound Recording, Travel Organisation, Managing, Publishing / „Focused on mixing and touring with Bands and Artists!“
4. Slider 1 (three stage photos) + „I'm in the Music Industry with more than 10 years of experience!“ / „This is my personal resume“ / German paragraph („Wie würde sich Musik und Reisen besser verbinden lassen …“) / HIRE ME!
5. Clients: „Clients“ / „I rocked the crowd with these cool clients!“ / logo carousel with 12 logos, 10 of them linked.
6. „Sound Engineer, Roadie, Driver and many more...“ / „This is my professional background“ / German paragraph („Angefangen hat alles 2007 …“) / HIRE ME! / Slider 2 (three photos, `dummy.png` placeholders with `data-lazyload`) / caption „FOH für Max the Sax am Metronome Festival Prag“.
7. Portfolio: „Portfolio“ / „Things I've done in the past!“ / three lists: „I mixed FOH for:“ (18 names), „I worked together with:“ (9), „I worked at:“ (20).
8. Contact: address, e-mail, phone, role list, region line, „Worldwide available for jobs.“, square portrait with caption „reichi | Christian Reichinger“.
9. Hire: „Hire me for your next Tour or Concert!“ / „Du braucht einen Tontechniker oder Tourmanager?“ / German paragraph / Contact Form 7 (name*, e-mail*, phone*, subject, message) with reCAPTCHA.
10. BleedingStar: logo, „Label, Distribution, Rental and Services.“, link. R-Stream: logo, „Live streaming.“, link.
11. „Touring is my passion.“ / „I'm in the Music Industry with more than 10 years of experience!“
12. Footer: Instagram icon, Impressum link, scroll-to-top.

External destinations: supervision-music.at, maxthesax.at, soul-sanity.com, summa-rock.de, sonic-vt.at, scorpiosmykonos.com, ituepferider.at, stadlmusi.at, hoamspue.at, facebook.com/sandusky.live, bleedingstar.at, rstream.at, instagram.com/the_reichi. Third-party resources loaded by the old site: Google Analytics (UA-17783211-1), Google Fonts (Raleway, Roboto Slab), Jetpack/i0.wp.com image CDN, reCAPTCHA.

### Imprint page

Business details (name, address, phone `+43 664 4385462`, e-mail, UID `ATU67362668`), four alias domains, general disclaimer paragraphs (liability, links, copyright), a long legacy privacy text covering Google Analytics, Google Maps, Facebook, Google+, Twitter, YouTube, Pinterest, LinkedIn, XING, Instagram plugin, Amazon affiliate, Google AdSense, right of access, and image credits: „Christian Reichinger / Portrait Fotos: Martin Mühlbacher – mdot.at / MW Design – MichaelaWiesinger.at“.

## 2. What was kept, consolidated or changed

### Kept verbatim or near-verbatim

- All 18 + 9 + 20 reference names, in the original three categories, in the original order.
- All 12 client logos with their links (two had no link on the old site: Freundlich & Kompetent, Prague Metronome Festival – still unlinked).
- BleedingStar and R-Stream with their one-line descriptions and links.
- Contact block: address, e-mail, phone, role list, region line, „Worldwide available for jobs.“
- Source headlines used as-is where they carry personality: „Knowledge and experience is the key!“, „Focused on mixing and touring with Bands and Artists!“, „Things I've done in the past!“, „I rocked the crowd with these cool clients!“, „Hire me for your next Tour or Concert!“, „Contact me!“, „Touring is my passion.“, „Sound Engineer, Roadie, Driver – and many more …“.
- Role line „Sound Engineer | Tour Manager | Instagramer“ (hero).
- Photo caption „FOH für Max the Sax am Metronome Festival Prag“ (applied to the three slider images that carried it).
- Imprint business details, alias domains, disclaimer/liability/copyright paragraphs, image credits.

### Consolidated / edited

- The two biography blocks („personal resume“ and „professional background“) were merged into one story under *Über mich*, keeping every fact: start in 2007 as manager/roadie/driver, growing events, sliding into the sound-engineer role, festivals to living rooms, international touring today.
- Grammar/spelling fixes in the German copy („Du braucht“ → „Du brauchst“, „auf anhieb“ → „auf Anhieb“, „Zehn Jahre Später“ …), punctuation normalised.
- Time-bound statements replaced with timeless ones: „Seit über Zehn Jahren“ / „more than 10 years of experience“ → „Seit 2007“; „Zehn Jahre später bin ich international unterwegs“ → „heute international mit Künstlern unterwegs“. No new numbers were introduced.
- Accidental duplication removed: hero text was in the HTML twice (desktop/mobile columns), the slider placeholder `dummy.png` appeared six times, „I'm in the Music Industry …“ appeared twice.
- Skills: each of the five items got a one-sentence explanation derived only from facts on the old site (FOH for bands/artists on festivals and in clubs; live recording; travel planning from years as roadie/driver; tour management; publishing via the BleedingStar label). The icon-font icons were replaced with numbering.
- „Portfolio“ became the eyebrow, „Referenzen“ the section title; list headings translated to German with the exact category meaning preserved (`FOH gemischt für`, `Zusammengearbeitet mit`, `Festivals & Venues`).
- Phone displayed in international format `+43 664 4385462` (as on the imprint) instead of `06644385462`.
- Impressum: „wir“ → „ich“ (sole proprietor), otherwise unchanged. The privacy portion moved to `/datenschutz/` and was **rewritten** – see below.
- Footer: nav, imprint, privacy, Instagram, project links, photo credits, roles.

### Intentionally dropped

- Google Analytics, Google Fonts, reCAPTCHA, Jetpack image CDN, WordPress feeds/oEmbed/xmlrpc links, WPBakery/Slider Revolution markup, „Scroll“ to-top link, icon fonts.
- Legacy privacy sections about Google Analytics, Google Maps, Facebook/Google+/Twitter/YouTube/Pinterest/LinkedIn/XING/Instagram plugins, Amazon affiliate and Google AdSense – none of these technologies exist on the new site. Keeping the text would describe processing that does not happen.
- The old form required a telephone number; it is optional now (not necessary to process an inquiry).
- Automatic slideshows; the same photos are shown as static compositions and a gallery.

### Language

German is the primary language for descriptive copy, interface, form and legal pages.
English source headlines and industry terms were kept where they are part of the
personal tone. No language switch was added (the old site did not have full English
copy either).

## 3. Images

See `ASSETS.md` for the full manifest. Summary: 8 photographs, 14 logos and the favicon
set were downloaded from the original `wp-content/uploads` paths (one via the i0.wp.com
proxy because the direct URL timed out). `dummy.png` slider placeholders were not
migrated; their real sources were resolved through `data-lazyload`. All delivery
files are local JPEG + WebP variants with explicit dimensions and `srcset`/`sizes`;
portrait crops exist for the two images that need a different mobile composition.

## 4. Technical changes

| topic | old | new |
|---|---|---|
| stack | WordPress + 3 plugins + jQuery | PHP templates, plain CSS/JS |
| fonts | Google Fonts | system font stacks |
| images | i0.wp.com proxy, lazy loader | local `<picture>` with WebP/JPEG |
| form | Contact Form 7 + reCAPTCHA | own PHP handler, honeypot + time trap + rate limit |
| analytics | Google Analytics | none |
| cookies | GA + WP | one session cookie for the form |
| headers | – | CSP and security headers from PHP |
| language attr | `en-US` | `de` |
| structured data | – | `Person` JSON-LD from visible facts only |

## 5. Checks performed (local, PHP 8.3.6 built-in server, Chrome headless)

- `php -l` on every PHP file – no syntax errors.
- Pages `/`, `/impressum/`, `/datenschutz/`, `/404.php` render; no PHP warnings.
- Screenshots at 390, 768 and 1440 px width, plus no-JS and reduced-motion runs:
  no horizontal overflow, no console errors, no failed requests, **zero external requests**.
- Mobile navigation: toggle works, focus moves into the menu, Escape closes and returns focus.
- Skip link appears on first Tab; focus ring visible on interactive elements.
- All legacy anchors land below the sticky header (desktop and mobile).
- Lightbox: opens via click, keyboard arrows, Escape closes, focus returns to the trigger; images remain plain links without JS.
- Contact form: empty/invalid submission → field errors + preserved input; wrong CSRF token → explicit message; honeypot → silent fake success without mail; mail disabled → honest failure message; mail enabled with a fake `sendmail` → message accepted with correct `From`/`Reply-To`/UTF-8 subject; header-injection payloads in name/subject collapsed to spaces, injected e-mail rejected; rate limit → 4th submission within the window blocked, file contains only timestamps under an HMAC key; `GET /contact.php` → 303 to `/#hire`.
- Lighthouse 12 (local server, no network latency): home page mobile and desktop
  Performance 100 / Accessibility 100 / Best Practices 100 / SEO 100; imprint and privacy
  pages Accessibility/Best Practices/SEO 100. Production numbers will differ slightly
  (real latency, compression settings of the host).

### Not checked / not possible here

- Real e-mail delivery (no MTA in the sandbox) – must be tested on the hosting.
- Apache `.htaccess` behaviour (built-in PHP server ignores it) – rules follow standard
  mod_rewrite/mod_expires/mod_headers syntax; verify once on the server.
- Behaviour on the hoster's exact PHP version and `sendmail_path`.
- Safari/Firefox rendering (only Chromium was available); the CSS uses widely supported
  features (`aspect-ratio`, `clamp()`, `svh`, `<dialog>`, `text-wrap` with graceful fallback).

## 6. Open items requiring owner or hoster confirmation

1. **Business data**: name, address (Maria Aich 3, 4971 Aurolzmünster), phone, e-mail, UID `ATU67362668`, and the four alias domains were taken from the old imprint – confirm they are current.
2. **Hosting details** for the privacy statement (`[BESTÄTIGEN]` marks in `public/datenschutz/index.php`): provider name and seat, log retention, whether the mail server is the hoster's or an external mail provider, retention period for inquiries.
3. **Mail setup**: `mail_from` must exist / be allowed on the domain (SPF, DKIM, DMARC); set `mail_enabled => true` and test.
4. **Photo credits**: the hooded and studio portraits and the club photo carry EXIF `media.dot`/Martin Mühlbacher; the Prague series carries an MW Design watermark; the blue open-air photo carries `StageShots.at | Christian Reichinger`. The large red-lit stage photo (`11059538_…_o-1.jpg`, a Facebook export) has **no embedded credit** – the photographer and usage rights should be confirmed. Publication on the old site is not proof of a licence for the new one.
5. **Logo usage**: 12 client/festival logos and the two project logos are reused as on the old site. The R-Stream logo (black on transparent) is displayed inverted (white) via CSS – confirm this is acceptable.
6. **Legal review**: the imprint and privacy texts describe the implementation accurately, but no legal compliance is guaranteed; have them checked if desired.
7. **Redirects/HTTPS**: enable the canonical redirect block in `.htaccess` (or the nginx equivalent) once the certificate is active; configure alias domains.
8. **Description texts of the five skills** are short interpretations of the old one-word items – adjust wording in `app/content.php` if anything reads too broad.
