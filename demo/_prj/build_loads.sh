CUR_PATH=`readlink /proc/$$/fd/255 | xargs dirname`
. $CUR_PATH/../env_def.sh
../rigger -c config -d dev -s all 
