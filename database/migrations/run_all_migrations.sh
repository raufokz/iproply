#!/usr/bin/env bash
# Run forward SaaS SQL migrations in order, once per file (tracked in schema_migrations).
#
# Usage (on server or via CI over SSH):
#   export MYSQL_HOST=127.0.0.1 MYSQL_USER=... MYSQL_PASSWORD=... MYSQL_DATABASE=...
#   bash database/migrations/run_all_migrations.sh
#
# Alternative (credentials only on server — recommended):
#   export MYSQL_CNF=/home/you/.mysql/iproply.cnf   # chmod 600, contains [client] host,user,password,database
#   bash database/migrations/run_all_migrations.sh
#
# Optional:
#   MIGRATIONS_DIR — override migrations directory (default: this script's directory)
#   MYSQL_CLI      — mysql binary (default: mysql)
#   SKIP_OPTIONAL  — set to 1 to skip 007_soft_delete_columns.sql

set -euo pipefail

MYSQL_CLI="${MYSQL_CLI:-mysql}"
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
MIGRATIONS_DIR="${MIGRATIONS_DIR:-$SCRIPT_DIR}"

mysql_exec() {
  if [[ -n "${MYSQL_CNF:-}" ]]; then
    "$MYSQL_CLI" --defaults-extra-file="$MYSQL_CNF" --batch "$@"
  else
    "$MYSQL_CLI" -h"$MYSQL_HOST" -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" "$MYSQL_DATABASE" --batch "$@"
  fi
}

mysql_file() {
  if [[ -n "${MYSQL_CNF:-}" ]]; then
    "$MYSQL_CLI" --defaults-extra-file="$MYSQL_CNF" < "$1"
  else
    "$MYSQL_CLI" -h"$MYSQL_HOST" -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" "$MYSQL_DATABASE" < "$1"
  fi
}

if [[ -n "${MYSQL_CNF:-}" ]]; then
  test -f "$MYSQL_CNF" || { echo "MYSQL_CNF not found: $MYSQL_CNF" >&2; exit 1; }
else
  for v in MYSQL_HOST MYSQL_USER MYSQL_PASSWORD MYSQL_DATABASE; do
    if [[ -z "${!v:-}" ]]; then
      echo "ERROR: $v is not set. Set all MYSQL_* vars or use MYSQL_CNF." >&2
      exit 1
    fi
  done
fi

echo "==> Ensuring schema_migrations table exists..."
mysql_exec -e "
CREATE TABLE IF NOT EXISTS schema_migrations (
  version VARCHAR(191) NOT NULL PRIMARY KEY,
  applied_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
"

is_applied() {
  local name="$1"
  local c
  c="$(mysql_exec -N -e "SELECT COUNT(*) FROM schema_migrations WHERE version='${name//\'/\\\'}';")"
  [[ "$c" == "1" ]]
}

apply_sql() {
  local file="$1"
  local name
  name="$(basename "$file")"
  echo "==> Applying: $name"
  mysql_file "$file"
  mysql_exec -e "INSERT INTO schema_migrations (version) VALUES ('${name//\'/\\\'}');"
  echo "    Recorded: $name"
}

shopt -s nullglob
candidates=()
for f in "$MIGRATIONS_DIR"/[0-9][0-9][0-9]_*.sql; do
  [[ -f "$f" ]] || continue
  candidates+=( "$(basename "$f")" )
done

if [[ ${#candidates[@]} -eq 0 ]]; then
  echo "No migration files matching [0-9][0-9][0-9]_*.sql in $MIGRATIONS_DIR"
  exit 0
fi

mapfile -t sorted < <(printf '%s\n' "${candidates[@]}" | sort)

for base in "${sorted[@]}"; do
  if [[ "${SKIP_OPTIONAL:-0}" == "1" && "$base" == 007_soft_delete_columns.sql ]]; then
    echo "==> Skipping optional: $base (SKIP_OPTIONAL=1)"
    continue
  fi
  f="$MIGRATIONS_DIR/$base"
  if is_applied "$base"; then
    echo "==> Already applied, skip: $base"
    continue
  fi
  apply_sql "$f"
done

echo "==> Migrations complete."
