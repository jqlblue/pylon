#!/bin/bash
export PATH=/sbin:/usr/sbin:/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin
CALCU_SVR="219.232.245.69"
PORT=22
SRC=/home/z/imgs/yunyou_yunyou/
DEST=/home/z/imgs/yunyou_yunyou/
sudo mkdir -p $DEST
sudo chmod -R a+w $DEST
RSYNC_CMD="rsync -avrzpu --delete --progress "
$RSYNC_CMD  -e "ssh -p$PORT"  push@$CALCU_SVR:$SRC $DEST


SRC_FILE=/home/z/files/yunyou_yunyou/
DEST_FILE=/home/z/files/yunyou_yunyou/
sudo mkdir -p $DEST_FILE
sudo chmod -R a+w $DEST_FILE
$RSYNC_CMD  -e "ssh -p$PORT"  push@$CALCU_SVR:$SRC_FILE $DEST_FILE
