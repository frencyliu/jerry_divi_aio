#/bin/bash

git add .
git commit -m 'Contact form 7 integrate'

git push origin DEV

git checkout master

git merge DEV

git push origin master

git checkout DEV
