#!/usr/bin/env bash

set -o errexit
set -o nounset
set -o pipefail

usage()
{
    echo "Usage: docker/rehearse-upgrade.sh BACKUP.sql [IMAGE]"
    echo
    echo "Restore an older AtoM logical backup into an isolated database,"
    echo "upgrade it with the production image, validate it, and prove rollback."
    echo
    echo "The backup must omit CREATE DATABASE and USE statements. Override local"
    echo "Docker settings with ATOM_REHEARSAL_COMPOSE_FILE,"
    echo "ATOM_REHEARSAL_ENV_FILE, ATOM_REHEARSAL_MYSQL_SERVICE,"
    echo "ATOM_REHEARSAL_MYSQL_HOST, ATOM_REHEARSAL_MYSQL_ROOT_PASSWORD,"
    echo "ATOM_REHEARSAL_NETWORK, and ATOM_REHEARSAL_EXPECTED_VERSION."
}

if [[ "${1:-}" == "--help" ]]; then
    usage
    exit 0
fi

if (($# < 1 || $# > 2)); then
    usage >&2
    exit 1
fi

backup="$1"
image="${2:-atom-sf7:production}"

compose_file="${ATOM_REHEARSAL_COMPOSE_FILE:-docker/docker-compose.dev.yml}"
environment_file="${ATOM_REHEARSAL_ENV_FILE:-docker/etc/environment}"
mysql_service="${ATOM_REHEARSAL_MYSQL_SERVICE:-percona}"
mysql_host="${ATOM_REHEARSAL_MYSQL_HOST:-percona}"
mysql_root_password="${ATOM_REHEARSAL_MYSQL_ROOT_PASSWORD:-my-secret-pw}"
network="${ATOM_REHEARSAL_NETWORK:-docker_default}"
database="atom_sf7_rehearsal_$$"
domain_tables=(
    accession
    actor
    digital_object
    information_object
    repository
    user
)
compose=(docker compose -f "${compose_file}")

if command -v sha256sum >/dev/null; then
    hash_command=(sha256sum)
elif command -v shasum >/dev/null; then
    hash_command=(shasum -a 256)
else
    echo "sha256sum or shasum is required." >&2
    exit 1
fi

if [[ ! -r "${backup}" ]]; then
    echo "Backup is not readable: ${backup}" >&2
    exit 1
fi

if grep -Eiq \
    '^[[:space:]]*(CREATE[[:space:]]+DATABASE|USE[[:space:]])' \
    "${backup}"
then
    echo "Backup must not contain CREATE DATABASE or USE statements." >&2
    exit 1
fi

docker image inspect "${image}" >/dev/null

latest_migration="$(
    find lib/task/migrate/migrations \
        -maxdepth 1 \
        -type f \
        -name 'arMigration*.class.php' \
        -print \
        | sort \
        | tail -n 1
)"
expected_version="${ATOM_REHEARSAL_EXPECTED_VERSION:-$(
    basename "${latest_migration}" \
        | sed -E 's/^arMigration0*([0-9]+)\.class\.php$/\1/'
)}"

if [[ ! "${expected_version}" =~ ^[0-9]+$ ]]; then
    echo "Could not determine the expected database version." >&2
    exit 1
fi

mysql_root()
{
    "${compose[@]}" exec -T \
        -e "MYSQL_PWD=${mysql_root_password}" \
        "${mysql_service}" \
        mysql --user=root "$@"
}

mysqldump_root()
{
    "${compose[@]}" exec -T \
        -e "MYSQL_PWD=${mysql_root_password}" \
        "${mysql_service}" \
        mysqldump --user=root "$@"
}

cleanup()
{
    if [[ "${database}" =~ ^atom_sf7_rehearsal_[0-9]+$ ]]; then
        mysql_root \
            --execute="DROP DATABASE IF EXISTS \`${database}\`;" \
            >/dev/null \
            || true
    fi
}

trap cleanup EXIT
trap 'exit 130' INT
trap 'exit 143' TERM

read_version()
{
    mysql_root \
        --batch \
        --skip-column-names \
        --database="${database}" \
        --execute='
            SELECT setting_i18n.value
            FROM setting
            JOIN setting_i18n USING (id)
            WHERE setting.name = "version"
            ORDER BY setting_i18n.culture = "en" DESC
            LIMIT 1;
        '
}

domain_fingerprint()
{
    mysqldump_root \
        --skip-comments \
        --compact \
        --skip-extended-insert \
        --order-by-primary \
        --hex-blob \
        "${database}" \
        "${domain_tables[@]}" \
        | "${hash_command[@]}" \
        | awk '{print $1}'
}

full_fingerprint()
{
    mysqldump_root \
        --skip-comments \
        --compact \
        --skip-extended-insert \
        --order-by-primary \
        --hex-blob \
        "${database}" \
        | "${hash_command[@]}" \
        | awk '{print $1}'
}

restore_backup()
{
    mysql_root --execute="DROP DATABASE IF EXISTS \`${database}\`;"
    mysql_root \
        --execute="CREATE DATABASE \`${database}\`
            CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;"
    mysql_root --database="${database}" <"${backup}"
}

run_upgrade()
{
    docker run --rm \
        --network "${network}" \
        --env-file "${environment_file}" \
        --env "ATOM_MYSQL_DSN=mysql:host=${mysql_host};port=3306;dbname=${database};charset=utf8mb4" \
        --env ATOM_MYSQL_USERNAME=root \
        --env "ATOM_MYSQL_PASSWORD=${mysql_root_password}" \
        "${image}" \
        php symfony tools:upgrade-sql \
        --no-confirmation \
        --verbose
}

restore_backup

initial_version="$(read_version)"

if [[ ! "${initial_version}" =~ ^[0-9]+$ ]]; then
    echo "Backup does not contain a numeric AtoM database version." >&2
    exit 1
fi

if ((initial_version >= expected_version)); then
    echo "Backup version ${initial_version} does not exercise an upgrade" >&2
    exit 1
fi

initial_domain_fingerprint="$(domain_fingerprint)"
initial_full_fingerprint="$(full_fingerprint)"

run_upgrade

upgraded_version="$(read_version)"

if [[ "${upgraded_version}" != "${expected_version}" ]]; then
    echo "Expected database version ${expected_version}, got ${upgraded_version}" \
        >&2
    exit 1
fi

if [[ "$(domain_fingerprint)" != "${initial_domain_fingerprint}" ]]; then
    echo "Upgrade changed protected domain records." >&2
    exit 1
fi

mysql_root \
    --database="${database}" \
    --execute='CHECK TABLE
        accession,
        actor,
        digital_object,
        information_object,
        repository,
        setting,
        setting_i18n,
        user;'

docker run --rm \
    --network "${network}" \
    --env-file "${environment_file}" \
    --env "ATOM_MYSQL_DSN=mysql:host=${mysql_host};port=3306;dbname=${database};charset=utf8mb4" \
    --env ATOM_MYSQL_USERNAME=root \
    --env "ATOM_MYSQL_PASSWORD=${mysql_root_password}" \
    "${image}" \
    php docker/healthcheck.php ready

run_upgrade

if [[ "$(domain_fingerprint)" != "${initial_domain_fingerprint}" ]]; then
    echo "Repeated upgrade changed protected domain records." >&2
    exit 1
fi

restore_backup

if [[ "$(read_version)" != "${initial_version}" ]]; then
    echo "Rollback did not restore database version ${initial_version}." >&2
    exit 1
fi

if [[ "$(full_fingerprint)" != "${initial_full_fingerprint}" ]]; then
    echo "Rollback did not restore the original database exactly." >&2
    exit 1
fi

echo "Upgrade rehearsal passed: v${initial_version} -> "\
"v${upgraded_version} -> v${initial_version} rollback"
