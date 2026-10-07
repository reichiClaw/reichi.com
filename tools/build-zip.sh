#!/usr/bin/env sh
# Development helper: builds the upload-ready ZIP in dist/.
# Not needed on the server. Requires `zip` and `php`.
#
# ZIP layout:
#   reichi-website/
#     htdocs/        -> upload the CONTENTS of this folder into public_html (FTP)
#     htdocs/it/     -> web root of reichi.it (domain mapped to this folder in the panel)
#     htdocs/rstream/ -> web root of rstream.at (domain mapped to this folder in the panel)
#     README.md, MIGRATION.md, ASSETS.md
#     assets-src/    -> untouched original photos/logos (do not upload)
set -eu

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

STAMP="$(date +%Y-%m-%d)"
OUT="$ROOT/dist/reichi-website-$STAMP.zip"
STAGE="$(mktemp -d)"
PKG="$STAGE/reichi-website"
mkdir -p dist "$PKG"
rm -f "$OUT"

# Sanity checks before packaging: PHP syntax, and the copies of the shared
# front-end files inside htdocs/it/ and htdocs/rstream/ (web roots of reichi.it
# and rstream.at) must be current.
find htdocs -name '*.php' -print0 | xargs -0 -n1 php -l >/dev/null
sh tools/sync-site-assets.sh --check

cp -R htdocs "$PKG/htdocs"
cp README.md MIGRATION.md ASSETS.md "$PKG/"
cp -R assets-src "$PKG/assets-src"

# Runtime data and the local dev config never ship; the shipped config.php is a
# ready-to-edit copy of the example (it contains no secrets).
rm -f "$PKG/htdocs/app/config.php"
cp htdocs/app/config.example.php "$PKG/htdocs/app/config.php"
find "$PKG/htdocs/storage" -type f ! -name '.htaccess' ! -name '.gitkeep' -delete
find "$PKG" \( -name '.DS_Store' -o -name 'Thumbs.db' \) -delete

(cd "$STAGE" && zip -r -q -X "$OUT" reichi-website)
rm -rf "$STAGE"

echo "Created $OUT ($(du -h "$OUT" | cut -f1))"
unzip -l "$OUT" | tail -1
