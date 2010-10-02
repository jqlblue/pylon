#!/bin/bash
CUR_ROOT=`readlink /proc/$$/fd/255 | xargs dirname`
cd $CUR_ROOT
./debian/deploy.sh make;
cp -rf ./debian/i386/*.deb   /apt_pkgs/dists/gx/main/binary-i386/ ; 
cp -rf ./debian/amd64/*.deb  /apt_pkgs/dists/gx/main/binary-amd64/ ; 
cd /apt_pkgs/
/home/z/zlabs_team/build_apt.sh   "/apt_pkgs/"
