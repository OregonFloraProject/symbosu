On your local machine, in the "symbosu" repository:
1. Run "git checkout dev". Then "git pull --rebase" to get the newest version of the codebase.
2. Copy the map images to images/maps/. Allow overwriting files.
3. Run "git add -A", "git commit -m 'update maps'", then "git push". This is to commit the change to the repository and push the new maps to the "dev" branch of the codebase first.
4. Run "git checkout master", then "git pull --rebase" to get the newest version of the codebase in "master" branch.
5. Run "git merge dev" to merge the difference between "dev" and "master" into "master" branch. Then "git push" to push the difference up to Github.
6. Run "git checkout dev" to make sure no change can occur in "master" by accident.
