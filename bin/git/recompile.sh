#!/bin/sh
#
# post-merge hook to run after "dummize.sh" merge driver

if [[ -f .dummized ]]; then
    # find files that should be recompiled
    # the .? in the regex is there to consume a newline character
    grep -r www/* -lze "^instante-dummized-merge.\?$" > .dummized

    # run grunt compilation
    cd frontend
    grunt dist
    cd ..

    # add merged files to git and ammend the merge commit
    xargs git add < .dummized
    git commit --amend --no-edit
    rm .dummized
fi
