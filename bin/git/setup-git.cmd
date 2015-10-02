@ECHO OFF
pushd "%~dp0\..\.."

rem disable auto crlf conversion
git config core.autocrlf false

rem install hooks to recompile grunt after merge or rebase
git config merge.dummize.driver "bin/git/dummize.sh %%A"
copy bin\git\post-merge.hook .git\hooks\post-merge
copy bin\git\post-rewrite.hook .git\hooks\post-rewrite

popd
