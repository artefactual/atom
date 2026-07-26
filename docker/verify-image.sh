#!/usr/bin/env bash

set -o errexit
set -o nounset
set -o pipefail

image="${1:?Usage: docker/verify-image.sh IMAGE [REVISION]}"
expected_revision="${2:-}"
script_directory="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

actual_user="$(docker image inspect --format '{{.Config.User}}' "${image}")"
healthcheck="$(
    docker image inspect --format '{{json .Config.Healthcheck.Test}}' "${image}"
)"

if [[ "${actual_user}" != "atom" ]]; then
    echo "Expected image user atom, got ${actual_user}" >&2
    exit 1
fi

if [[ "${healthcheck}" != '["CMD","php","docker/healthcheck.php","ready"]' ]]; then
    echo "Expected the production readiness health check" >&2
    exit 1
fi

if [[ -n "${expected_revision}" ]]; then
    actual_revision="$(
        docker image inspect \
            --format '{{index .Config.Labels "org.opencontainers.image.revision"}}' \
            "${image}"
    )"

    if [[ "${actual_revision}" != "${expected_revision}" ]]; then
        echo \
            "Expected image revision ${expected_revision}, got ${actual_revision}" \
            >&2
        exit 1
    fi
fi

docker run --rm --entrypoint sh "${image}" -ec '
    test "$(id -u)" = 1000
    test "$(id -g)" = 1000

    test ! -e /usr/local/bin/node
    test ! -e /usr/local/bin/npm
    test ! -d /atom/src/node_modules
    test ! -d /atom/src/output
    test ! -d /atom/src/playwright
    test ! -d /atom/src/cypress
    test ! -d /atom/src/tmp.ignored
    test ! -e /atom/src/playwright.config.js
    test ! -e /atom/src/build.xml
    test ! -e /atom/src/vendor/bin/phpunit
    test ! -e /atom/src/docker/create-upgrade-fixture.sh
    test ! -e /atom/src/docker/rehearse-upgrade.sh
    test ! -e /atom/src/docker/verify-runtime.sh

    test ! -d /atom/src/vendor/symfony
    test ! -d /atom/src/vendor/symfony2
    test ! -d /atom/src/vendor/propel1/generator
    test ! -d /atom/src/vendor/composer/phing
    test ! -d /atom/src/vendor/FluentDOM/examples
    test ! -d /atom/src/vendor/FluentDOM/tests
    test ! -d /atom/src/vendor/FluentDOM/tutorials
    test ! -d /atom/src/vendor/net_gearman/examples
    test ! -d /atom/src/vendor/net_gearman/tests
    test ! -d /atom/src/plugins/sfSkosPlugin/test
    test ! -d /atom/src/plugins/sfThumbnailPlugin/test
    test ! -d /atom/src/plugins/sfWebBrowserPlugin/test
    test -e /atom/src/vendor/propel1/runtime/propel/Propel.php

    test -e /atom/src/apps/qubit/modules/user/actions/editAction.class.php
    test -e /atom/src/apps/qubit/modules/user/templates/editSuccess.php
    find /atom/src/dist/js -type f -name "*.js" -print -quit | grep -q .
    find /atom/src/dist/css -type f -name "*.css" -print -quit | grep -q .

    php -r "
        require \"/atom/src/vendor/composer/autoload.php\";
        exit(class_exists(\"Atom\\\\Kernel\") ? 0 : 1);
    "
    php -m | grep -Eq "^(intl|memcache)$"
    ! php -m | grep -Eq "^(pcov|xdebug)$"
'

docker run --rm --entrypoint php "${image}" \
    docker/healthcheck.php live \
    | grep -q '"status":"ok"'

docker run --rm --interactive --entrypoint php "${image}" \
    < "${script_directory}/verify-autoload.php"

echo "Production image verification passed"
