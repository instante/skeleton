#!/usr/bin/env bash

pushd "$(dirname "$0")/../../.." >/dev/null

branch=$(git rev-parse --abbrev-ref HEAD)

git checkout develop
git pull
git checkout "$branch"
git rebase develop

popd >/dev/null
