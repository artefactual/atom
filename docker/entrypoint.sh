#!/usr/bin/env bash

set -o errexit
set -o pipefail
set -o nounset
# set -o xtrace

__dir="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
__file="${__dir}/$(basename "${BASH_SOURCE[0]}")"
__atom_root="/atom/src"


# Clean-ups
rm -rf /usr/local/etc/php-fpm.d/*
rm -rf ${__atom_root}/cache/*

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
        php ${__dir}/../symfony jobs:worker
        exit 0
        ;;
    'fpm')
        trap 'kill -INT $PID' TERM INT
        php-fpm --allow-to-run-as-root &
        PID=$!
        wait $PID
        trap - TERM INT
        wait $PID
        exit $?
        ;;
esac

exec "${@}"
