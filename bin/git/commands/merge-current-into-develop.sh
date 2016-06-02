#!/usr/bin/env bash

pushd "$(dirname "$0")/../../.." >/dev/null

branch=$(git branch | sed -n -e 's/^\* \(.*\)/\1/p')

git checkout develop
git pull
git merge --no-ff --no-edit "$branch"

popd >/dev/null

