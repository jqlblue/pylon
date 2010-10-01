#!/bin/bash
CUR_ROOT=`readlink /proc/$$/fd/255 | xargs dirname`
cd $CUR_ROOT
source /home/z/zlabs_team/dpkg_utls.sh

mkdir -p $CUR_ROOT/i386
mkdir -p $CUR_ROOT/amd64
VER=` cat $CUR_ROOT/../../ide/version.txt`
NAME="zlabs-ps-ide"
rm -rf tmp_ide_amd64
rm -rf tmp_ide_i686
$CUR_ROOT/mktree_ide.sh 'tmp_ide_amd64'
$CUR_ROOT/mktree_ide.sh 'tmp_ide_i686'
makePkg $CUR_ROOT/tmp_ide_i686/  "i386"  $NAME $VER ""    $CUR_ROOT/deploy_action_ide.sh  
makePkg $CUR_ROOT/tmp_ide_amd64/ "amd64" $NAME $VER ""    $CUR_ROOT/deploy_action_ide.sh  



cd $CUR_ROOT

mkdir -p $CUR_ROOT/i386
mkdir -p $CUR_ROOT/amd64
VER=` cat $CUR_ROOT/../../src/version.txt`
NAME="zlabs-pylon"

rm -rf tmp_pylon_amd64
rm -rf tmp_pylon_i686
$CUR_ROOT/mktree_ps.sh 'tmp_pylon_amd64'
$CUR_ROOT/mktree_ps.sh 'tmp_pylon_i686'
makePkg $CUR_ROOT/tmp_pylon_i686/  "i386"  $NAME $VER ""    $CUR_ROOT/deploy_action_lib.sh  
makePkg $CUR_ROOT/tmp_pylon_amd64/ "amd64" $NAME $VER ""    $CUR_ROOT/deploy_action_lib.sh  
