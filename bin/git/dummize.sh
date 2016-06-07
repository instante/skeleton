#!/usr/bin/env bash


# marks conflicted files that are configured to be solved using this merge driver
# by setting their content to "instante-dummized-merge"
pushd "$(dirname "$0")/../.."

echo "." > .dummized
echo "instante-dummized-merge" > $1

popd
