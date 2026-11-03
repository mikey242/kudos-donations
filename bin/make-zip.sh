#!/usr/bin/env bash
#
# Build the distributable plugin zip from the current working tree.
#
# Copies the plugin into kudos-donations/ (excluding everything listed in
# .distignore, which uses .gitignore-style patterns) and zips it.
# Run composer install --no-dev and npm run build first.
#
# Usage: bin/make-zip.sh [output.zip]   (default: ./kudos-donations.zip)

set -euo pipefail

slug="kudos-donations"
root="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
out="$(realpath -m "${1:-$root/$slug.zip}")"

for cmd in rsync zip; do
	command -v "$cmd" >/dev/null || { echo "make-zip: '$cmd' is required" >&2; exit 1; }
done

tmp="$(mktemp -d)"
trap 'rm -rf "$tmp"' EXIT

rsync -a \
	--exclude-from="$root/.distignore" \
	--exclude="/$slug.zip" \
	--exclude='/dist/' \
	"$root/" "$tmp/$slug/"

rm -f "$out"
(cd "$tmp" && zip -qr -X "$out" "$slug")

echo "Created $out ($(du -h "$out" | cut -f1), $(zipinfo -1 "$out" | grep -vc '/$') files)"
