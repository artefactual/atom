#!/usr/bin/env bash

set -o errexit
set -o nounset
set -o pipefail

if (($# < 2 || $# > 3)); then
    echo "Usage: docker/verify-runtime.sh IMAGE NETWORK [ENV_FILE]" >&2
    exit 2
fi

image="$1"
network="$2"
environment_file="${3:-docker/etc/environment}"
web_container="atom-runtime-web-$$"
worker_container="atom-runtime-worker-$$"
diagnostics_dir="$(mktemp -d "${TMPDIR:-/tmp}/atom-runtime.XXXXXX")"

cleanup()
{
    docker rm --force \
        "${worker_container}" \
        "${web_container}" \
        >/dev/null 2>&1 \
        || true
    rm -rf "${diagnostics_dir}"
}

trap cleanup EXIT
trap 'exit 130' INT
trap 'exit 143' TERM

docker image inspect "${image}" >/dev/null
docker network inspect "${network}" >/dev/null

runtime_options=(
    --network "${network}"
    --env-file "${environment_file}"
    --env ATOM_DEVELOPMENT_MODE=off
    --env NODE_ENV=production
)

docker run --detach \
    --name "${web_container}" \
    "${runtime_options[@]}" \
    "${image}" \
    >/dev/null

docker run --detach \
    --name "${worker_container}" \
    "${runtime_options[@]}" \
    "${image}" \
    worker \
    >/dev/null

for attempt in {1..30}; do
    web_status="$(
        docker inspect \
            --format '{{if .State.Health}}{{.State.Health.Status}}{{end}}' \
            "${web_container}"
    )"
    worker_status="$(
        docker inspect \
            --format '{{if .State.Health}}{{.State.Health.Status}}{{end}}' \
            "${worker_container}"
    )"

    if [[ "${web_status}" == "healthy" && "${worker_status}" == "healthy" ]]; then
        break
    fi

    if [[ "${web_status}" == "unhealthy" || "${worker_status}" == "unhealthy" ]]; then
        break
    fi

    sleep 2
done

if [[ "${web_status}" != "healthy" || "${worker_status}" != "healthy" ]]; then
    echo \
        "Runtime health failed: web=${web_status}, worker=${worker_status}" \
        >&2
    docker logs "${web_container}" >&2 || true
    docker logs "${worker_container}" >&2 || true
    exit 1
fi

if [[ "$(docker exec "${web_container}" id -u)" != "1000" ]]; then
    echo "Production web process is not running as uid 1000." >&2
    exit 1
fi

if [[ "$(docker exec "${worker_container}" id -u)" != "1000" ]]; then
    echo "Production worker process is not running as uid 1000." >&2
    exit 1
fi

docker exec "${web_container}" php docker/healthcheck.php ready \
    | grep -q '"status":"ok"'
docker exec "${worker_container}" php docker/healthcheck.php ready \
    | grep -q '"status":"ok"'

docker exec \
    --env SCRIPT_FILENAME=/atom/src/index.php \
    --env SCRIPT_NAME=/index.php \
    --env REQUEST_METHOD=GET \
    --env REQUEST_URI=/ \
    --env QUERY_STRING= \
    --env SERVER_NAME=localhost \
    --env SERVER_PORT=80 \
    --env SERVER_PROTOCOL=HTTP/1.1 \
    --env HTTP_HOST=localhost \
    --env REMOTE_ADDR=127.0.0.1 \
    "${web_container}" \
    cgi-fcgi -bind -connect 127.0.0.1:9000 \
    >"${diagnostics_dir}/response.html"

grep -q '^Content-Type: text/html' "${diagnostics_dir}/response.html"
grep -q '<title>.*AtoM</title>' "${diagnostics_dir}/response.html"
grep -q '<html' "${diagnostics_dir}/response.html"

docker logs "${web_container}" >"${diagnostics_dir}/web.log" 2>&1
docker logs "${worker_container}" >"${diagnostics_dir}/worker.log" 2>&1

grep -q 'Running worker' "${diagnostics_dir}/worker.log"
test/check-runtime-logs.sh \
    "${diagnostics_dir}/web.log" \
    "${diagnostics_dir}/worker.log"

echo "Production runtime verification passed"
