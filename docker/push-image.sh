#!/usr/bin/env bash

set -o errexit
set -o pipefail
set -o nounset
# set -o xtrace

__dir="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
__root="$(cd "$(dirname "${__dir}")" && pwd)"

BRANCH=$(git rev-parse --abbrev-ref HEAD)
RELEASE="edge-$(echo $BRANCH | sed 's/[\/\.]//g')"
TAG="artefactual/atom:$RELEASE"

docker build --pull --tag ${TAG} --file ${__dir}/Dockerfile --build-arg "GIT_BRANCH=$BRANCH" ${__root}

docker push ${TAG}
