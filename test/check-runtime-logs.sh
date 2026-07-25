#!/usr/bin/env bash

set -euo pipefail

if [[ 0 -eq "$#" ]]; then
  echo "Usage: $0 LOG_FILE [...]" >&2
  exit 2
fi

pattern='(PHP message: )?(\[(error|critical)\] )?(Warning:|Deprecated:|Notice:|Uncaught PHP)'
ignored='NotFoundHttpException'
failed=0

for log_file in "$@"; do
  if [[ ! -f "${log_file}" ]]; then
    echo "Runtime log not found: ${log_file}" >&2
    failed=1
    continue
  fi

  if matches="$(grep -En "${pattern}" "${log_file}" | grep -Ev "${ignored}")"; then
    echo "Runtime diagnostics found in ${log_file}:" >&2
    echo "${matches}" >&2
    failed=1
  fi
done

exit "${failed}"
