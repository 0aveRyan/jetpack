#!/bin/bash

JETPACK_GIT_DIR=$(dirname "$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )" )
JETPACK_TMP_DIR="/tmp/jetpack"
JETPACK_TMP_DIR_2="/tmp/jetpack2"

cd $JETPACK_GIT_DIR

# Make sure we don't have uncommitted changes.
if [[ -n $( git status -s --porcelain ) ]]; then
 	echo "Uncommitted changes found."
 	echo "Please deal with them and try again clean."
 	exit 1
fi

read -p "You are about to deploy a new built to the jetpack-built repo. Are you sure? [y/N]" -n 1 -r
if [[ $REPLY != "y" && $REPLY != "Y" ]]
then
    exit 1
fi
echo ""

echo "Building Jetpack"
npm run build
echo "Done"

# Prep a home to drop our new files in. Just make it in /tmp so we can start fresh each time.
rm -rf $JETPACK_TMP_DIR
rm -rf $JETPACK_TMP_DIR_2

echo "Rsync'ing everything over from Git except for .git and npm stuffs."
rsync -r --exclude='*.git*' --exclude=node_modules $JETPACK_GIT_DIR/* $JETPACK_TMP_DIR_2
echo "Done!"

echo "Purging paths included in .svnignore"
# check .svnignore
for file in $( cat "$JETPACK_GIT_DIR/.svnignore" 2>/dev/null ); do
	rm -rf $JETPACK_TMP_DIR_2/$file
done
echo "Done!"

echo "Pulling latest from jetpack-built branch"
git clone -b jetpack-built --single-branch git@github.com:Automattic/jetpack.git $JETPACK_TMP_DIR
echo "Done!"

echo "Rsync'ing everything over remote version"
rsync -r $JETPACK_TMP_DIR_2/* $JETPACK_TMP_DIR
echo "Done!"

cd $JETPACK_TMP_DIR

echo "Finally, Committing and Pushing"
git add .
git commit -am 'New build'
git push origin jetpack-built
echo "Done! Branch jetpack-react has been updated."

echo "Cleaning up the mess"
cd $JETPACK_GIT_DIR
rm -rf $JETPACK_TMP_DIR
rm -rf $JETPACK_TMP_DIR_2
echo "All clean!"
