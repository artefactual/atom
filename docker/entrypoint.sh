#!/usr/bin/env bash

set -o errexit
set -o pipefail
set -o nounset
# set -o xtrace

__dir="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

# Clean-ups
rm -rf /usr/local/etc/php-fpm.d/*

# The FPM and worker containers share this directory and start concurrently.
# Letting both remove it races with Symfony while it warms its cache.
if [ "${1:-}" = "fpm" ]; then
    rm -rf "${__dir}/../cache/"*
fi

# Populate configuration files
php ${__dir}/bootstrap.php $@
status=$?
if [ $status -ne 0 ]; then
    echo "bootstrap.php failed!"
    exit $status
fi

if [ "${1:-}" = "fpm" ]; then
    php "${__dir}/warm-cache.php" prod
fi

case $1 in
    '')
        echo "Usage: (convenience shortcuts)"
        echo "  ./entrypoint.sh worker      Execute worker."
        echo "  ./entrypoint.sh fpm         Execute php-fpm."
        echo ""
        echo "You can also pass other commands:"
        echo "  ./entrypoint.sh bash"
        echo "  ./entrypoint.sh uptime"
        echo "  ./entrypoint.sh ls -l /"
        exit 0
        ;;
    'worker')
        # Give some extra time to MySQL and Gearman to start
        # and add some interval in between restarts.
        sleep 10
        exec php ${__dir}/../symfony jobs:worker
        ;;
    'fpm')
        if [[ ${EUID} -eq 0 ]]; then
            exec php-fpm --allow-to-run-as-root
        fi

        exec php-fpm
        ;;
esac

exec "${@}"
