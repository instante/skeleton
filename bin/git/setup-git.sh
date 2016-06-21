#!/usr/bin/env bash
pushd "$(dirname "$0")/../.."

# disable auto crlf conversion
git config core.autocrlf false

popd
