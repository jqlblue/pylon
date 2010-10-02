export PATH=".:/bin:/usr/bin:/usr/sbin:/usr/local/bin"
CUR_ROOT=`readlink /proc/$$/fd/255 | xargs dirname`
echo $CUR_ROOT
PRJ_ROOT=$CUR_ROOT/../..
echo $PRJ_ROOT
TMP_DIR=$1
mkdir $TMP_DIR
cd $TMP_DIR

############## deploy ###########
mkdir -p home/z/shell/ps-ide
cp -Rf $PRJ_ROOT/ide/* home/z/shell/ps-ide/
