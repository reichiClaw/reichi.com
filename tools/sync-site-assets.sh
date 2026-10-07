#!/usr/bin/env sh
# Development helper: copies the shared front-end files of reichi.com into the web roots of the
# department sites, htdocs/it/assets/ (reichi.it) and htdocs/rstream/assets/ (rstream.at).
#
# Why copies: each domain points at its own folder as document root, so the browser cannot
# load ../assets/… from the parent folder. PHP code is shared via ../app/, static files have
# to exist inside each folder. Run after changing style.css, main.js, grain.png or a logo:
#
#   sh tools/sync-site-assets.sh          # copy
#   sh tools/sync-site-assets.sh --check  # exit 1 if a copy is out of date (used by build-zip.sh)
set -eu

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
SRC="$ROOT/htdocs/assets"

SHARED="css/style.css js/main.js images/grain.png"
# Logo files are the "Verbundene Projekte" cards of each site.
FILES_it="$SHARED images/logos/bleedingstar.png images/logos/rstream-paper.png"
FILES_rstream="$SHARED images/logos/bleedingstar.png images/logos/reichi-it.png"

status=0
for site in it rstream; do
  DST="$ROOT/htdocs/$site/assets"
  eval "files=\$FILES_$site"
  for f in $files; do
    if [ "${1:-}" = "--check" ]; then
      if ! cmp -s "$SRC/$f" "$DST/$f"; then
        echo "out of date: htdocs/$site/assets/$f (run tools/sync-site-assets.sh)"
        status=1
      fi
    else
      mkdir -p "$(dirname "$DST/$f")"
      cp "$SRC/$f" "$DST/$f"
      echo "copied $f -> htdocs/$site/assets/"
    fi
  done
done
exit $status
