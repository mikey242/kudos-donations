#!/usr/bin/env bash
#
# Deploy the WordPress.org listing assets to the plugin SVN repository.
#
# Makes SVN assets/ an exact copy of .wordpress-org/ (banners, icons,
# screenshots and the Live Preview blueprint): new files are added, changed
# files updated and files no longer in .wordpress-org/ deleted. trunk/ and
# tags/ are not touched; the readme is deployed with each release.
#
# Usage: bin/deploy-wporg-assets.sh
#
# Environment:
#   SVN_USERNAME, SVN_PASSWORD  WordPress.org SVN credentials (not needed for DRY_RUN=1)
#   DRY_RUN=1                   Do everything except commit
#   WPORG_SLUG                  Plugin slug (default: kudos-donations)

set -euo pipefail

root="$(cd "$(dirname "$0")/.." && pwd)"
src="$root/.wordpress-org"
slug="${WPORG_SLUG:-kudos-donations}"
svn_url="https://plugins.svn.wordpress.org/$slug"
dry_run="${DRY_RUN:-0}"

fail() { echo "deploy-wporg-assets: $*" >&2; exit 1; }

[ -d "$src" ] || fail "folder not found: $src"
# An empty source would delete every listing asset on WordPress.org.
[ -n "$(ls -A "$src")" ] || fail "$src is empty; refusing to delete all assets."
if [ "$dry_run" != 1 ]; then
	[ -n "${SVN_USERNAME:-}" ] && [ -n "${SVN_PASSWORD:-}" ] || fail "SVN_USERNAME and SVN_PASSWORD must be set."
fi

work="$(mktemp -d)"
trap 'rm -rf "$work"' EXIT

svn_args=(--non-interactive --no-auth-cache --config-option servers:global:http-timeout=3600)

echo "Checking out $svn_url/assets..."
svn checkout "${svn_args[@]}" --quiet "$svn_url/assets" "$work/assets"

echo "Copying .wordpress-org/ to assets/..."
rsync -a --delete --exclude='.svn' "$src/" "$work/assets/"

cd "$work/assets"
# Deleted files: svn status '!' lines. The trailing @ stops svn from reading an
# '@' in a filename as a peg revision.
svn status | sed -n 's/^!       //p' | while IFS= read -r path; do svn delete --quiet "$path@"; done
svn add --quiet --force --depth infinity .

# Images need the right MIME type, or browsers download them instead of showing them.
svn status | sed -n 's/^A       //p' | while IFS= read -r path; do
	case "$path" in
		*.png) svn propset --quiet svn:mime-type image/png "$path@" ;;
		*.jpg | *.jpeg) svn propset --quiet svn:mime-type image/jpeg "$path@" ;;
		*.gif) svn propset --quiet svn:mime-type image/gif "$path@" ;;
		*.svg) svn propset --quiet svn:mime-type image/svg+xml "$path@" ;;
	esac
done

changes="$(svn status)"
if [ -z "$changes" ]; then
	echo "No changes: SVN assets/ already matches .wordpress-org/."
	exit 0
fi

echo ""
echo "Changes to assets/:"
echo "$changes" | sed 's/^/  /'
echo "  (A = added, D = deleted, M = modified)"

if [ "$dry_run" = 1 ]; then
	echo ""
	echo "DRY_RUN=1: not committing."
	exit 0
fi

echo ""
echo "Committing assets..."
printf '%s' "$SVN_PASSWORD" | svn commit "${svn_args[@]}" \
	--username "$SVN_USERNAME" --password-from-stdin \
	-m "Update plugin assets"

echo ""
echo "Deployed .wordpress-org/ to $svn_url/assets"
