@ECHO OFF
SET __origPath=%cd%
cd "%~dp0\..\.."

# disable auto crlf conversion
git config core.autocrlf false

# install hooks to recompile grunt after merge or rebase
git config merge.dummize.driver "bin/dummize.sh %%A"
cp bin/post-merge.hook .git/hooks/post-merge
cp bin/post-rewrite.hook .git/hooks/post-rewrite

cd %__origPath%
