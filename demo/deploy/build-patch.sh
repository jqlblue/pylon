#!/bin/sh
echo "pls write the old num"
read oldnum
echo "pls write the new num"
read newnum
SVN_SVR=http://sagitar.googlecode.com/svn/
PRJ_NAME=apollo
TAG_NAME=apollo
old_file=$SVN_SVR/tags/$TAG_NAME-$oldnum/src/
new_file=$SVN_SVR/tags/$TAG_NAME-$newnum/src/
svn diff $old_file $new_file > $TAG_NAME-$newnum.patch
