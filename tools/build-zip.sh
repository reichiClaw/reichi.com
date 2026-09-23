#!/usr/bin/env sh
# Development helper: builds the upload-ready ZIP in dist/.
# Not needed on the server. Requires `zip`.
set -eu

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

STAMP="$(date +%Y-%m-%d)"
OUT="dist/reichi-website-$STAMP.zip"
mkdir -p dist
rm -f "$OUT"

# Sanity check before packaging
for f in $(find app public -name '*.php'); do php -l "$f" >/dev/null; done

zip -r -q -X "$OUT" \
  app public storage assets-src README.md MIGRATION.md ASSETS.md \
  -x 'app/config.php' \
  -x 'storage/ratelimit/*.json' -x 'storage/logs/*.log' \
  -x '*/.DS_Store' -x '*/Thumbs.db'

echo "Created $OUT ($(du -h "$OUT" | cut -f1))"
unzip -l "$OUT" | tail -1
