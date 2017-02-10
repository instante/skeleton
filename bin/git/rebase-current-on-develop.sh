#!/usr/bin/env sh

cd "$(dirname "$0")/../.." >/dev/null

branch=$(git rev-parse --abbrev-ref HEAD)

git checkout develop
git pull
git checkout "$branch"
git rebase develop
