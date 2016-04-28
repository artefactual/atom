#!/usr/bin/env bash

set -o errexit
set -o pipefail
set -o nounset
# set -o xtrace

__dir="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
__file="${__dir}/$(basename "${BASH_SOURCE[0]}")"
__root="$(cd "$(dirname "${__dir}")" && pwd)"

TAG="artefactual/atom:edge"

docker build --pull --tag ${TAG} --file ${__dir}/Dockerfile ${__root}

docker push ${TAG}
