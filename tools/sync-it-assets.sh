#!/usr/bin/env sh
# Development helper: copies the shared front-end files of reichi.com into htdocs/it/assets/.
#
# Why copies: the domain reichi.it points at htdocs/it/ as its own web root, so the browser
# cannot load ../assets/… from the parent folder. PHP code is shared via ../app/, static
# files have to exist inside it/. Run after changing style.css, main.js or grain.png:
#
#   sh tools/sync-it-assets.sh          # copy
#   sh tools/sync-it-assets.sh --check  # exit 1 if the copies are out of date (used by build-zip.sh)
set -eu

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
SRC="$ROOT/htdocs/assets"
DST="$ROOT/htdocs/it/assets"

FILES="css/style.css js/main.js images/grain.png"

status=0
for f in $FILES; do
  if [ "${1:-}" = "--check" ]; then
    if ! cmp -s "$SRC/$f" "$DST/$f"; then
      echo "out of date: htdocs/it/assets/$f (run tools/sync-it-assets.sh)"
      status=1
    fi
  else
    mkdir -p "$(dirname "$DST/$f")"
    cp "$SRC/$f" "$DST/$f"
    echo "copied $f"
  fi
done
exit $status
