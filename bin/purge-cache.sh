#!/usr/bin/env bash
pushd "$(dirname "$0")/.."

rm -rf temp/cache/*
rm -rf temp/proxies/*
rm -f temp/btfj.dat

popd
