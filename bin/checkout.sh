#!/usr/bin/env bash

dir=${1-instante-app}
mkdir "$dir"
git --git-dir "$(dirname "$0")/../.git" --work-tree "$(dirname "$0")/../" add -A
git --git-dir "$(dirname "$0")/../.git" --work-tree "$dir" checkout-index -a -f
