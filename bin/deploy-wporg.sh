#!/usr/bin/env bash
#
# Deploy a release zip to the WordPress.org plugin SVN repository.
#
# Replaces trunk with the zip's contents, commits it, then copies trunk to
# tags/<version> on the server. Plugin assets (assets/) are not touched.
#
# Usage: bin/deploy-wporg.sh <plugin.zip> <version>
#
# Environment:
#   SVN_USERNAME, SVN_PASSWORD  WordPress.org SVN credentials (not needed for DRY_RUN=1)
#   DRY_RUN=1                   Do everything except commit and tag
#   WPORG_SLUG                  Plugin slug (default: kudos-donations)

set -euo pipefail

zip="${1:?Usage: $0 <plugin.zip> <version>}"
version="${2:?Usage: $0 <plugin.zip> <version>}"
version="${version#v}"
slug="${WPORG_SLUG:-kudos-donations}"
svn_url="https://plugins.svn.wordpress.org/$slug"
dry_run="${DRY_RUN:-0}"

fail() { echo "deploy-wporg: $*" >&2; exit 1; }

[[ "$version" =~ ^[0-9]+\.[0-9]+(\.[0-9]+)?$ ]] \
	|| fail "'$version' is not a stable version number; pre-releases are not deployed."
[ -f "$zip" ] || fail "zip not found: $zip"
if [ "$dry_run" != 1 ]; then
	[ -n "${SVN_USERNAME:-}" ] && [ -n "${SVN_PASSWORD:-}" ] || fail "SVN_USERNAME and SVN_PASSWORD must be set."
fi

work="$(mktemp -d)"
trap 'rm -rf "$work"' EXIT

echo "Unpacking $zip..."
unzip -q "$zip" -d "$work/build"
src="$work/build/$slug"
[ -d "$src" ] || fail "zip does not contain a top-level $slug/ folder."

# WordPress.org serves whichever tag readme.txt points at, so a mismatch means
# the tag gets created but nobody is offered the update.
header_version=$(grep -iE '^[[:space:]*]*Version:' "$src/$slug.php" | head -1 | sed -E 's/.*[Vv]ersion:[[:space:]]*//' | tr -d '[:space:]')
stable_tag=$(grep -iE '^[[:space:]]*Stable tag:' "$src/readme.txt" | head -1 | sed -E 's/.*[Ss]table tag:[[:space:]]*//' | tr -d '[:space:]')
[ "$header_version" = "$version" ] || fail "plugin header has Version: $header_version, but the release is $version."
[ "$stable_tag" = "$version" ] || fail "readme.txt has Stable tag: $stable_tag, but the release is $version."

svn_args=(--non-interactive --no-auth-cache)

if svn ls "${svn_args[@]}" "$svn_url/tags/$version" >/dev/null 2>&1; then
	fail "$svn_url/tags/$version already exists. Tags are never overwritten."
fi

echo "Checking out $svn_url/trunk..."
svn checkout "${svn_args[@]}" --quiet "$svn_url/trunk" "$work/trunk"

echo "Replacing trunk with $version..."
rsync -a --delete --exclude='.svn' "$src/" "$work/trunk/"

cd "$work/trunk"
# Deleted files: svn status '!' lines. The trailing @ stops svn from reading an
# '@' in a filename as a peg revision.
svn status | sed -n 's/^!       //p' | while IFS= read -r path; do svn delete --quiet "$path@"; done
svn add --quiet --force --depth infinity .

echo ""
echo "Changes to trunk:"
svn status | cut -c1 | sort | uniq -c | sed 's/^/  /'
echo "  (A = added, D = deleted, M = modified)"

if [ "$dry_run" = 1 ]; then
	echo ""
	echo "DRY_RUN=1: not committing trunk or creating tags/$version."
	exit 0
fi

echo ""
echo "Committing trunk..."
printf '%s' "$SVN_PASSWORD" | svn commit "${svn_args[@]}" \
	--username "$SVN_USERNAME" --password-from-stdin \
	-m "Update trunk to $version"

echo "Tagging $version..."
printf '%s' "$SVN_PASSWORD" | svn copy "${svn_args[@]}" \
	--username "$SVN_USERNAME" --password-from-stdin \
	"$svn_url/trunk" "$svn_url/tags/$version" \
	-m "Tagging version $version"

echo ""
echo "Deployed $version to $svn_url"
