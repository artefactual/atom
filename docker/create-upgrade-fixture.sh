#!/usr/bin/env bash

set -o errexit
set -o nounset
set -o pipefail

usage()
{
    echo "Usage: docker/create-upgrade-fixture.sh OUTPUT.sql"
    echo
    echo "Create a version 193 upgrade fixture from an installed demo database."
    echo "Version 193 is the v2.9.2 schema; migrations 194-197 are data-only."
}

if [[ "${1:-}" == "--help" ]]; then
    usage
    exit 0
fi

if (($# != 1)); then
    usage >&2
    exit 2
fi

output="$1"
partial_output="${output}.partial.$$"
compose_file="${ATOM_REHEARSAL_COMPOSE_FILE:-docker/docker-compose.dev.yml}"
mysql_service="${ATOM_REHEARSAL_MYSQL_SERVICE:-percona}"
mysql_root_password="${ATOM_REHEARSAL_MYSQL_ROOT_PASSWORD:-my-secret-pw}"
source_database="${ATOM_REHEARSAL_SOURCE_DATABASE:-atom}"
fixture_database="atom_sf7_fixture_$$"
compose=(docker compose -f "${compose_file}")

if [[ ! "${source_database}" =~ ^[A-Za-z0-9_]+$ ]]; then
    echo "Invalid source database name: ${source_database}" >&2
    exit 1
fi

if [[ -e "${output}" ]]; then
    echo "Refusing to overwrite existing fixture: ${output}" >&2
    exit 1
fi

if [[ ! -d "$(dirname "${output}")" ]]; then
    echo "Fixture output directory does not exist: $(dirname "${output}")" >&2
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
    if [[ "${fixture_database}" =~ ^atom_sf7_fixture_[0-9]+$ ]]; then
        mysql_root \
            --execute="DROP DATABASE IF EXISTS \`${fixture_database}\`;" \
            >/dev/null \
            || true
    fi

    rm -f -- "${partial_output}"
}

trap cleanup EXIT
trap 'exit 130' INT
trap 'exit 143' TERM

mysql_root \
    --execute="CREATE DATABASE \`${fixture_database}\`
        CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;"

mysqldump_root \
    --single-transaction \
    --routines \
    --triggers \
    "${source_database}" \
    | mysql_root --database="${fixture_database}"

mysql_root \
    --database="${fixture_database}" \
    --execute='
        CREATE TEMPORARY TABLE removed_setting (id INT PRIMARY KEY);
        INSERT INTO removed_setting
            SELECT id
            FROM setting
            WHERE name IN (
                "google_analytics",
                "header_background_colour",
                "accession",
                "accessioncount",
                "descriptioncount",
                "authorityrecordcount",
                "repositorycount"
            );
        DELETE FROM object
            WHERE id IN (SELECT id FROM removed_setting);
        DELETE FROM setting
            WHERE id IN (SELECT id FROM removed_setting);
        UPDATE setting_i18n
            JOIN setting USING (id)
            SET setting_i18n.value = "193"
            WHERE setting.name = "version";
    '

version="$(
    mysql_root \
        --batch \
        --skip-column-names \
        --database="${fixture_database}" \
        --execute='
            SELECT setting_i18n.value
            FROM setting
            JOIN setting_i18n USING (id)
            WHERE setting.name = "version"
            ORDER BY setting_i18n.culture = "en" DESC
            LIMIT 1;
        '
)"

if [[ "${version}" != "193" ]]; then
    echo "Expected fixture database version 193, got ${version}" >&2
    exit 1
fi

mysqldump_root \
    --single-transaction \
    --routines \
    --triggers \
    "${fixture_database}" \
    >"${partial_output}"

if grep -Eiq \
    '^[[:space:]]*(CREATE[[:space:]]+DATABASE|USE[[:space:]])' \
    "${partial_output}"
then
    echo "Generated fixture contains database-selection statements." >&2
    exit 1
fi

mv -- "${partial_output}" "${output}"

echo "Created version 193 upgrade fixture: ${output}"
