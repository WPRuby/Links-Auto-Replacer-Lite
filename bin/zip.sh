#!/usr/bin/env bash
#
# Build the release zip: links-auto-replacer.zip, with the plugin in a
# `links-auto-replacer/` folder, the WordPress.org slug (another folder name
# would install the update next to the old plugin). It is also what goes to SVN trunk.
#
# The files listed in .distignore are left out. Run `npm run zip`, which builds
# build/ first.

set -euo pipefail

SLUG=links-auto-replacer
ROOT="$(cd "$(dirname "$0")/.." && pwd)"

if [ ! -f "$ROOT/build/admin.js" ]; then
	echo "build/ is missing, run npm run build first." >&2
	exit 1
fi

STAGE="$(mktemp -d)"
trap 'rm -rf "$STAGE"' EXIT

rsync -a --exclude-from="$ROOT/.distignore" "$ROOT/" "$STAGE/$SLUG/"

rm -f "$ROOT/$SLUG.zip"
(cd "$STAGE" && zip -qr -X "$ROOT/$SLUG.zip" "$SLUG")

VERSION="$(sed -n "s/^ \* Version: *//p" "$ROOT/links-auto-replacer.php" | tr -d '[:space:]')"
echo "Built $SLUG.zip (version $VERSION, $(du -h "$ROOT/$SLUG.zip" | cut -f1 | tr -d ' '))."
