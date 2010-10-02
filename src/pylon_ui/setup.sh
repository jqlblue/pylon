if test $# -ne 4  ; then
    echo "useage : $0 <JSDET> <CSSDEST> <IMGDEST> <STYLE> "
    exit;
fi

FILE_PATH=`dirname $0`
CUR_PATH=`pwd`
cd $FILE_PATH
ABS_FILE_PATH=`pwd`
cd $CUR_PATH 

JSDEST=$1
CSSDEST=$2
IMGDEST=$3
STYLE=$4
if test -L  $JSDEST/pui ; then 
    rm $JSDEST/pui 
fi
ln -s  $ABS_FILE_PATH/js $JSDEST/pui

if test -L  $CSSDEST/pui ; then 
    rm $CSSDEST/pui 
fi
ln -s  $ABS_FILE_PATH/style/$STYLE/css   $CSSDEST/pui 


if test -L  $IMGDEST/pui ; then 
    rm $IMGDEST/pui 
fi
ln -s  $ABS_FILE_PATH/style/$STYLE/images $IMGDEST/pui 
