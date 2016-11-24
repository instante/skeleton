#!/usr/bin/env sh

dir=${1-instante-app}
if [ ! -d "$dir" ]; then
    mkdir "$dir"
fi
git --git-dir "$(dirname "$0")/../.git" --work-tree "$(dirname "$0")/../" add -A
git --git-dir "$(dirname "$0")/../.git" --work-tree "$dir" checkout-index -a -f

ABS_VENDOR=`cd "$(dirname "$0")/../vendor"; pwd`
rm -rf "$dir/vendor"
ln -s "$ABS_VENDOR" "$dir"
