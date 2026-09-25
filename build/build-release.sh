#!/usr/bin/env bash
#
# Build a SEO Panel release zip, automating everything in
# https://github.com/seopanel/Seo-Panel/wiki/Seo-Panel-Release---steps
# except the final manual install-test (deploy the built folder to a scratch
# htdocs and run the web installer by hand before shipping it).
#
# Usage:
#   build/build-release.sh <version> [output-base-dir]
#
#   <version>          e.g. 7.0.0 - must already match install/data/seopanel.sql's
#                      SP_VERSION_NUMBER row (this project bumps that at the
#                      START of a dev cycle, not at release time - this script
#                      only asserts it matches, it never writes it).
#   [output-base-dir]  defaults to seopanel_code/ (sibling of this repo, matching
#                      every past release's location) - override for a dry run
#                      so a test build never touches the real release history.
#
# What it does, in order:
#   1. Preflight checks (clean working tree, version format, version match)
#   2. Sync themes/classic/views -> themes/simple/views (only these two themes
#      are git-tracked; business/odbox are gitignored, separate commercial
#      products, never part of this release)
#   3. Append the version to install/install.class.php's $spVersionList
#   4. Report (not auto-merge) anything in upgrade.sql that looks unreflected
#      in seopanel.sql, for manual review
#   5. git archive (respects .gitattributes export-ignore) into version.X.Y.Z/seopanel/
#   6. Clone SeoDiary + QuickWebProxy fresh into plugins/ (they're separate
#      repos and, like every other plugin except MetaTagGenerator/TestPlugin,
#      gitignored here - git archive never includes them at all, so this step
#      adds them rather than overwriting anything the archive produced)
#   7. Zip the result as seopanel.v.X.Y.Z.zip, matching every past release's
#      top-level folder name and layout

set -euo pipefail

# ---------------------------------------------------------------------------
# Args & paths
# ---------------------------------------------------------------------------

VERSION="${1:-}"
if [[ -z "$VERSION" ]]; then
    echo "Usage: $0 <version> [output-base-dir]" >&2
    exit 1
fi
if [[ ! "$VERSION" =~ ^[0-9]+\.[0-9]+\.[0-9]+$ ]]; then
    echo "Version must look like X.Y.Z (got: $VERSION)" >&2
    exit 1
fi

REPO_ROOT="$(git -C "$(dirname "${BASH_SOURCE[0]}")" rev-parse --show-toplevel)"
cd "$REPO_ROOT"

OUTPUT_BASE="${2:-$REPO_ROOT/../seopanel_code}"
RELEASE_DIR="$OUTPUT_BASE/version.$VERSION"
SEOPANEL_DIR="$RELEASE_DIR/seopanel"
ZIP_PATH="$RELEASE_DIR/seopanel.v.$VERSION.zip"

echo "== Building SEO Panel $VERSION =="
echo "   repo:   $REPO_ROOT"
echo "   output: $RELEASE_DIR"
echo

# ---------------------------------------------------------------------------
# 1. Preflight
# ---------------------------------------------------------------------------

# config/sp-config.php (local DB creds, never committed) and scripts/ (an
# untracked personal working directory) are permanent, expected local state
# in this dev environment - not something a release build should block on.
DIRTY="$(git status --porcelain | grep -v -E '^ M config/sp-config\.php$|^\?\? scripts/$' || true)"
if [[ -n "$DIRTY" ]]; then
    echo "ERROR: working tree is not clean. Commit or stash before building a release:" >&2
    echo "$DIRTY" >&2
    exit 1
fi

SHIPPED_VERSION="$(grep -oP "'Seo Panel version',\s*'SP_VERSION_NUMBER',\s*'\K[0-9.]+" install/data/seopanel.sql || true)"
if [[ "$SHIPPED_VERSION" != "$VERSION" ]]; then
    echo "ERROR: install/data/seopanel.sql's SP_VERSION_NUMBER is '$SHIPPED_VERSION', not '$VERSION'." >&2
    echo "       This project bumps that at the start of a dev cycle, not at release time -" >&2
    echo "       fix the mismatch (in the app, or by re-checking the version argument) before building." >&2
    exit 1
fi
echo "[1/7] Preflight OK (clean tree, version matches seopanel.sql)"

if [[ -e "$RELEASE_DIR" ]]; then
    echo "ERROR: $RELEASE_DIR already exists - remove it first if you want to rebuild." >&2
    exit 1
fi
mkdir -p "$RELEASE_DIR"

# ---------------------------------------------------------------------------
# 2. Theme sync: classic is the source of truth for views (standing project
#    rule). Only `simple` here - `themes/business` (and `themes/odbox*`) are
#    gitignored, separate commercial products, not part of the open-source
#    release at all, so git archive could never include them regardless of
#    what this script does to a local checkout's filesystem copy.
# ---------------------------------------------------------------------------

echo "[2/7] Syncing classic views -> simple"
cp -r themes/classic/views/. themes/simple/views/

# ---------------------------------------------------------------------------
# 3. Register the new version in the upgrade-path walker
# ---------------------------------------------------------------------------

echo "[3/7] Registering version $VERSION in install/install.class.php"
if grep -q "'$VERSION'," install/install.class.php; then
    echo "       already present, skipping"
else
    # Insert right before the $spVersionList array's closing ");" - matches
    # the existing entries' exact indentation/quote style so the diff stays clean.
    perl -0pi -e "s/(\\\$spVersionList\\s*=\\s*array\\s*\\([^)]*'6\\.0\\.0',\\n)(\\s*\\);)/\${1}\t\t    '$VERSION',\n\${2}/s" install/install.class.php
    if ! grep -q "'$VERSION'," install/install.class.php; then
        echo "ERROR: could not find the expected \$spVersionList pattern to insert after - check install/install.class.php manually." >&2
        exit 1
    fi
fi

# ---------------------------------------------------------------------------
# 4. upgrade.sql -> seopanel.sql reconciliation report (not an auto-merge -
#    too risky to get a semantic SQL merge right generically). This project's
#    practice has been keeping them in sync as each feature landed, so this
#    should normally report clean; treat it as a safety net, not the primary
#    mechanism.
# ---------------------------------------------------------------------------

echo "[4/7] Checking upgrade.sql settings for anything missing from seopanel.sql"
MISSING=0
while IFS= read -r SET_NAME; do
    if ! grep -q "'$SET_NAME'" install/data/seopanel.sql; then
        echo "       MISSING from seopanel.sql: setting $SET_NAME"
        MISSING=1
    fi
done < <(grep -oP "(?<=', ')[A-Z][A-Z0-9_]+(?=',)" install/data/upgrade.sql | sort -u)

if [[ "$MISSING" -eq 0 ]]; then
    echo "       clean - every upgrade.sql setting also appears in seopanel.sql"
else
    echo "       Review the above before shipping - seopanel.sql is what fresh installs get." >&2
fi

# ---------------------------------------------------------------------------
# 5. Archive (git archive respects .gitattributes export-ignore, so this
#    single step replaces the wiki's "clone, then manually rm a list of
#    dev-only files/.git*" dance)
# ---------------------------------------------------------------------------

echo "[5/7] Archiving via git archive (export-ignore excludes CLAUDE.md, data/test.php, build/)"
ARCHIVE_ZIP="$RELEASE_DIR/.archive.zip"
git archive --format=zip --prefix=seopanel/ -o "$ARCHIVE_ZIP" HEAD
mkdir -p "$SEOPANEL_DIR"
(cd "$RELEASE_DIR" && unzip -q ".archive.zip")
rm "$ARCHIVE_ZIP"

# ---------------------------------------------------------------------------
# 6. Plugin injection: SeoDiary / QuickWebProxy are separate repos and (like
#    every plugin except MetaTagGenerator/TestPlugin) gitignored in this repo,
#    so git archive's output never contained them at all - this step adds
#    them fresh rather than overwriting anything. Every other locally-present
#    plugin (ArticleSubmitter, CaptchaBypass, etc.) is intentionally left out
#    of the release - confirmed with the user, not just inferred from the
#    wiki's silence on them.
# ---------------------------------------------------------------------------

echo "[6/7] Cloning SeoDiary + QuickWebProxy plugins fresh"
for PLUGIN in SeoDiary QuickWebProxy; do
    rm -rf "$SEOPANEL_DIR/plugins/$PLUGIN"
    git clone --quiet "https://github.com/seopanel/$PLUGIN.git" "$SEOPANEL_DIR/plugins/$PLUGIN"
    rm -rf "$SEOPANEL_DIR/plugins/$PLUGIN/.git"
done

# ---------------------------------------------------------------------------
# 7. Zip the final result, matching every past release's layout
#    (top-level `seopanel/` folder, e.g. seopanel_code/version.6.0.0/seopanel.v.6.0.0.zip)
# ---------------------------------------------------------------------------

echo "[7/7] Zipping $ZIP_PATH"
(cd "$RELEASE_DIR" && zip -rq "$(basename "$ZIP_PATH")" seopanel)

echo
echo "== Done =="
echo "Built: $ZIP_PATH"
echo
echo "Still manual (deliberately not automated - see the plan's Explicitly out of scope):"
echo "  - Deploy $SEOPANEL_DIR to a scratch /opt/lampp/htdocs/ install and run the web installer end to end before shipping."
