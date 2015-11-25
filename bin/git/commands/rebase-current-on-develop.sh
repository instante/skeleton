#!/usr/bin/env bash

pushd "$(dirname "$0")/../../.." >/dev/null

branch=$(git branch | sed -n -e 's/^\* \(.*\)/\1/p')

git checkout develop
git pull
git checkout $branch
git rebase develop

popd >/dev/null
