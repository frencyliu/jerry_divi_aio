#/bin/bash

git add .
git commit -m 'add JWC, split function of ONESHOP'

git push origin DEV

git checkout master

git merge DEV

git push origin master

git checkout DEV
