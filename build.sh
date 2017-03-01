#!/bin/sh
PRODUCT_NAME="movie"
APP_NAME="queue"
dir="dragon index.php lib config conf modules process task var model bin"
rm -rf output
mkdir output
cp -r  $dir output
cd output
find ./ -name .git -exec rm -rf {} \;
tar cvzf queue.tar.gz  *
