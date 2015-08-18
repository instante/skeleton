#!/bin/sh

# marks conflicted files that are configured to be solved using this merge driver
# by setting their content to "instante-dummized-merge"

echo "." > .dummized
echo "instante-dummized-merge" > $1