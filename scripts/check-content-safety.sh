#!/bin/sh
# Prevent accidental seed-template rebuilds from replacing WP Admin content.
#
# Local:
#   scripts/check-content-safety.sh
#
# CI:
#   scripts/check-content-safety.sh <base-ref> <head-ref>
set -eu

ROOT=$(git rev-parse --show-toplevel 2>/dev/null) || {
  echo "[content-safety] ERROR: run from inside the git repo."
  exit 1
}

cd "$ROOT"

MARKER="[seed-content]"
DUPLICATE_DB="wordpress/_build/lsc-db.sql"

fail() {
  echo "[content-safety] ERROR: $1"
  exit 1
}

if [ -e "$DUPLICATE_DB" ]; then
  fail "$DUPLICATE_DB must not exist. Use wordpress/_build/db/lsc-db.sql only."
fi

if git ls-files --error-unmatch "$DUPLICATE_DB" >/dev/null 2>&1 &&
  ! git status --short -- "$DUPLICATE_DB" | grep -Eq '^( D|D )'; then
  fail "$DUPLICATE_DB is tracked. Remove it; it is an obsolete duplicate."
fi

if command -v rg >/dev/null 2>&1; then
  OLD_DB_REFS=$(rg -n '(^|[^[:alnum:]_/.-])(_build/lsc-db\.sql|wordpress/_build/lsc-db\.sql)($|[^[:alnum:]_/.-])' \
    --glob '!scripts/check-content-safety.sh' \
    --glob '!wordpress/_build/db/lsc-db.sql' \
    --glob '!*.sql' . || true)
else
  OLD_DB_REFS=$(grep -RInE '(^|[^[:alnum:]_/.-])(_build/lsc-db\.sql|wordpress/_build/lsc-db\.sql)($|[^[:alnum:]_/.-])' . \
    --exclude='check-content-safety.sh' \
    --exclude='*.sql' \
    --exclude-dir='.git' || true)
fi

if [ -n "$OLD_DB_REFS" ]; then
  echo "[content-safety] References to obsolete wordpress/_build/lsc-db.sql:"
  printf '%s\n' "$OLD_DB_REFS"
  fail "remove obsolete DB snapshot references."
fi

if [ "${LSC_ALLOW_SEED_CONTENT:-}" = "1" ]; then
  echo "[content-safety] Seed-content guard bypassed by LSC_ALLOW_SEED_CONTENT=1."
  exit 0
fi

BASE="${1:-}"
HEAD="${2:-}"

if [ -n "$BASE" ] && [ -n "$HEAD" ] && git rev-parse --verify "$BASE^{commit}" >/dev/null 2>&1; then
  CHANGED=$(git diff --name-only "$BASE" "$HEAD" --)
  MESSAGE=$(git log --format=%B "$BASE..$HEAD")
elif [ -n "$HEAD" ] && git rev-parse --verify "$HEAD^" >/dev/null 2>&1; then
  CHANGED=$(git diff --name-only "$HEAD^" "$HEAD" --)
  MESSAGE=$(git log --format=%B -1 "$HEAD")
else
  CHANGED=$(git diff --name-only HEAD --)
  STAGED=$(git diff --cached --name-only)
  CHANGED=$(printf '%s\n%s\n' "$CHANGED" "$STAGED" | sed '/^$/d' | sort -u)
  MESSAGE="${COMMIT_MESSAGE:-}"
fi

SEED_CHANGED=$(printf '%s\n' "$CHANGED" | grep -E '^(wordpress/_build/pages/|wordpress/_build/footer\.html$|wordpress/_build/build\.sh$)' || true)

if [ -n "$SEED_CHANGED" ] && ! printf '%s\n' "$MESSAGE" | grep -F "$MARKER" >/dev/null 2>&1; then
  echo "[content-safety] Seed content files changed:"
  printf '%s\n' "$SEED_CHANGED"
  fail "seed-template changes require $MARKER in the commit message or LSC_ALLOW_SEED_CONTENT=1."
fi

echo "[content-safety] OK"
