#/bin/bash

git add .
git commit -m 'basic css update'

git push origin DEV

git checkout master

git merge DEV

git push origin master

git checkout DEV
