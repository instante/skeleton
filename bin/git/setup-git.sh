#!/usr/bin/env bash
pushd "$(dirname "$0")/../.."

# disable auto crlf conversion
git config core.autocrlf false

# install hooks to recompile grunt after merge or rebase if needed
git config merge.dummize.driver "bin/git/dummize.sh %%A"
cp bin/git/post-merge.hook .git/hooks/post-merge
cp bin/git/post-rewrite.hook .git/hooks/post-rewrite

popd
