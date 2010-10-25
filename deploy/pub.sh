RUN_DIR=`pwd`
FILE_PATH=`dirname $0`
CUR_PATH=$RUN_DIR/$FILE_PATH/
cd $CUR_ROOT
./debian/deploy.sh make;
cp -rf ./debian/i386/*.deb   /apt_pkgs/dists/gx/main/binary-i386/ ; 
cp -rf ./debian/amd64/*.deb  /apt_pkgs/dists/gx/main/binary-amd64/ ; 
cd /apt_pkgs/
/home/z/zlabs_team/build_apt.sh   "/apt_pkgs/"
