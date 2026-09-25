# Asset manifest – reichi.com

All files were downloaded on 2026-09-23 from the original WordPress upload paths of
`https://www.reichi.com/wp-content/uploads/…` (direct, not via the i0.wp.com proxy, except
where noted). Untouched originals are kept in `assets-src/originals/`; delivery versions
live in `htdocs/assets/images/` and were generated with `tools/build-images.py`
(Pillow, JPEG q80 progressive, WebP q78, never upscaled).

Credits come from three sources: the „Bildnachweise“ line on the old imprint, EXIF/IPTC
metadata embedded in the files, and visible watermarks. Where none of these exist the
credit is marked *unknown*. Public availability on the old site is treated as a hint, not
as a licence – see „Concerns“.

## Photographs

| local name (`htdocs/assets/images/photos/`) | source file | original size | intended use | widths generated | credit | concerns |
|---|---|---|---|---|---|---|
| `portrait-hood` | `2019/12/DSC6295_square.jpg` | 1335×1335 | **Hero** portrait (LCP image, eager, `fetchpriority=high`) | 480, 600, 800, 1200 | EXIF: „media.dot _ martin mühlbacher“, Artist „martin mühlbacher“ → **Martin Mühlbacher – mdot.at** | Largest delivery 1200 px because the original is 1335 px; fine up to ~600 CSS px at 2×. |
| `portrait-studio` | `2018/11/ChristianReichingerFrontpage.jpg` | 2406×1606 | Booking/contact section portrait; 4:5 crop for ≥48em (focus 67 % from left) | 800, 1200, 1600 + portrait crop 480, 800, 1000 | EXIF Copyright „media.dot“ → **Martin Mühlbacher – mdot.at** | Was the old hero background. Light grey background is intentional contrast on the dark page. |
| `stage-red` | `2019/01/11059538_883376491737597_7864039594443423760_o-1.jpg` | 2048×1365 | Full-bleed image above *Über mich*; 4:5 crop for mobile; also `og:image` (1600) | 640, 1024, 1600, 2048 + portrait 540, 900 | **unknown** – no EXIF/IPTC author, filename indicates a Facebook export | Photographer and licence must be confirmed by the owner before launch. Band on stage is not identified (no caption on the old site) – alt text describes the scene only. |
| `crowd-club` | `2019/01/DSC8135.jpg` | 900×601 | Gallery | 480, 900 | EXIF Copyright „media.dot“ → **Martin Mühlbacher – mdot.at** | Only 900 px available; displayed at ≤ 450 CSS px on desktop, full width on mobile (900 px is enough for 1× mobile, soft at 2×). |
| `stage-blue` | `2019/01/MG_6693.jpg` | 900×600 | Gallery | 480, 900 | EXIF Copyright „StageShots.at \| Christian Reichinger“ | Same size limitation as above. |
| `max-the-sax-prag-1` | `2018/11/mtsprag1.jpg` | 1200×800 | Gallery; caption „FOH für Max the Sax am Metronome Festival Prag“ (from the old slider) | 480, 800, 1200 | Watermark „MW Design – michaela wiesinger.at“ → **MW Design – MichaelaWiesinger.at** | Slider used `dummy.png` placeholders; real source resolved via `data-lazyload`. Watermark remains visible. |
| `max-the-sax-prag-2` | `2018/11/mtsprag0.jpg` | 1200×800 | Gallery (wide slot), same caption | 480, 800, 1200 | Watermark → **MW Design** | as above |
| `max-the-sax-prag-3` | `2018/11/maxthesax.jpg` | 1200×800 | Gallery, same caption | 480, 800, 1200 | Watermark → **MW Design** | as above |

Not migrated: `wp-content/plugins/revslider/admin/assets/images/dummy.png` (slider
placeholder, appeared 6× in the HTML – not a photograph).

## Logos (`htdocs/assets/images/logos/`)

Format kept (PNG with alpha / JPEG). Large logos were downscaled to 400 px (1×) and
800 px (2×); small originals are served at their native size. `light_bg` = logo ships
with its own white background and is placed on a light plate in the design.

| local file(s) | source file | original size | linked to | alt text | light_bg | note |
|---|---|---|---|---|---|---|
| `supervision-400/800.png` | `2018/11/supervision_front.png` | 962×169 | supervision-music.at | Supervision | no | white wordmark |
| `max-the-sax-400/800.png` | `2018/11/Max_logo_positiv_BG.png` | 2500×735 | maxthesax.at | Max the Sax | **yes** | black on white |
| `soulsanity.png` | `2018/11/SoulSanity-Logo.png` | 131×100 | soul-sanity.com | SoulSanity | no | small original, shown ≤ 52 px high |
| `summarock-400/800.jpg` | `2018/11/summarock.jpg` | 887×200 | summa-rock.de | SummArock Festival | no | black background blends with page |
| `sonic-vt.png` | `2018/11/sonic-vt.png` | 287×88 | sonic-vt.at | Sonic Veranstaltungstechnik Pröll | no | |
| `scorpios.jpg` | `2019/02/17634736_…_n-2.jpg` | 181×173 | scorpiosmykonos.com | Scorpios Mykonos | no | Facebook export, black background |
| `i-tuepfe-rider-400/800.png` | `2018/11/ituepferider.png` | 1740×340 | ituepferider.at | i Tüpfe Rider | no | white wordmark |
| `stadlmusi.jpg` | `2019/01/Stadlmusi.jpg` | 600×400 | stadlmusi.at | Stadlmusi – Heavy Blasmusik | **yes** | downscaled to 300×200 |
| `hoamspue-400/800.jpg` | `2019/01/HoamspueLogo.jpg` | 900×319 | hoamspue.at | Hoamspü – Austropop mit Gfühl | **yes** | |
| `freundlich-kompetent.png` | `2019/02/fk-2.png` | 145×135 | – (no link on old site) | Bar Freundlich & Kompetent, Hamburg | no | **downloaded via i0.wp.com proxy** (direct URL timed out); pixel-identical PNG |
| `his-name-is-sandusky.jpg` | `2019/01/50446397_…_n.jpg` | 277×101 | facebook.com/sandusky.live | His Name is Sandusky | no | Facebook export |
| `prague-metronome-festival.png` | `2019/02/Prague-Metronome-Festival.png` | 442×161 | – (no link on old site) | Prague Metronome Festival | no | |
| `bleedingstar.png` | `2019/01/BS-Logo-Pfade-weiss.png` | 508×148 | bleedingstar.at | BleedingStar | no | white version |
| `rstream.png` | `2020/04/rstream.png` | 744×182 | rstream.at | R-Stream | no | **black on transparent** – displayed inverted (white) via CSS `filter: invert(1)`; confirm with owner or supply a white version |

All logos belong to their respective owners; they were already published on the old
site in the same context („Clients“). Their reuse is assumed but not verified.

## Icons (`htdocs/assets/images/icons/`, `htdocs/favicon.ico`)

| file | source | note |
|---|---|---|
| `favicon.ico`, `favicon-16x16.png`, `favicon-32x32.png`, `android-chrome-96x96.png` | `wp-content/uploads/fbrfg/…` | unchanged |
| `safari-pinned-tab.svg` | `wp-content/uploads/fbrfg/safari-pinned-tab.svg` | vector „R“ hexagon; also inlined as the header/footer mark (`logo_mark()` in `helpers.php`) with `currentColor` |
| `apple-touch-icon.png` (180), `icon-192.png`, `icon-512.png` | rendered from the SVG above | new opaque versions (white mark on `#0d0d10`); the old 76 px apple-touch-icon and the 96 px android icon were too small for current requirements |
| `ChristianReichinger-Icon-Black.png` / `-White.png` | `2019/01/…` | 100×100 raster logos of the old header – kept in `assets-src/originals/` only, superseded by the SVG |

Files referenced by the old `site.webmanifest` but returning 404 on the old server:
`android-chrome-192x192.png`, `android-chrome-512x512.png` (not migrated; replaced by the
rendered icons). `mstile-150x150.png`/`browserconfig.xml` were fetched but dropped (legacy IE tiles).

## Texture (`htdocs/assets/images/grain.png`)

| file | source | note |
|---|---|---|
| `grain.png` | generated, `tools/build-grain.py` (seeded random noise, no photograph) | 160×160 greyscale+alpha film-grain tile, ~16 KB, laid over the whole page by `body::after` in `style.css`. Pixels are randomly light or dark at low opacity, so it adds texture without shifting the average brightness of the surfaces underneath (measured on `--bg`: σ ≈ 7 luminance steps; the owner's reference sample had ≈ 9 and was toned down on request). On displays ≥ 1.5 dppx the tile is shown at 80 CSS px so one speck equals one device pixel. Regenerate with a different `STRENGTH` for coarser/finer grain. |

## Responsive delivery summary

- Photos: `<picture>` with WebP `<source>` + JPEG fallback, `srcset`/`sizes`, explicit
  `width`/`height`, `loading="lazy"` everywhere except the hero; hero additionally preloaded.
- Two images have an alternate 4:5 crop selected by media query (`stage-red` on
  narrow screens, `portrait-studio` on wide screens).
- Object-fit crops in CSS are used only for framing (gallery 3:2 / 2:1, about 21:9 on desktop).
- Total delivery weight: ~3.8 MB photos (all variants, both formats), ~360 KB logos,
  ~60 KB icons. A typical desktop first view loads about 400 KB of images, mobile about 150 KB.

## Concerns to resolve before launch

1. `stage-red` – unknown photographer, no embedded credit.
2. Confirm that the MW Design and Martin Mühlbacher photographs may be used on the
   relaunched site (they were published on the old site with credit).
3. Confirm inverted display of the R-Stream logo or provide a white/negative version.
4. `crowd-club` and `stage-blue` only exist at 900 px – ask for larger originals if available.
