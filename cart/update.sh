#!/bin/sh
if git config remote.lumen-template.url > /dev/null; then
    git pull lumen-template master
else
    CURRENT=$(git config --get remote.origin.url)
    if [ $CURRENT != 'ssh://git@stash.greensmoke.com:7999/mic/lumen.git' ]; then
        git remote add lumen-template ssh://git@stash.greensmoke.com:7999/mic/lumen.git
        git config remote.lumen-template.pushurl 'Access Denied'
        git pull lumen-template master
    fi
fi