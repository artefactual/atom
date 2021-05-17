#!/usr/bin/env bash

set -o errexit
set -o pipefail
set -o nounset
# set -o xtrace

__dir="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

# Clean-ups
rm -rf /usr/local/etc/php-fpm.d/*
rm -rf ${__dir}/../cache/*

# Populate configuration files
php ${__dir}/bootstrap.php $@
status=$?
if [ $status -ne 0 ]; then
    echo "bootstrap.php failed!"
    exit $status
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
        exec php-fpm --allow-to-run-as-root
        ;;
esac

exec "${@}"
