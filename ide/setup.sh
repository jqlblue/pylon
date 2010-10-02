DEST_PATH=`dirname $0`
CUR_PATH=`pwd`
cd $DEST_PATH
ABS_DEST_PATH=`pwd`
cd $CUR_PATH
cd $HOME
echo $HOME
rm -r $HOME/.vim
rm -f $HOME/.vimrc
ln -s $ABS_DEST_PATH/vim/runtime  .vim
ln -s ./.vim/_vimrc  .vimrc
mkdir -p $HOME/backup/vim
chmod -R 777 $HOME/backup
echo  "--------------- ide setup ok ---------------" 
