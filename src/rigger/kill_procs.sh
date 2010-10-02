#!/bin/bash
export path=.:/usr/bin:/usr/sbin:/bin:/sbin
CMD=$1
FILTER=$2
ps aux|grep "$CMD" | grep "$FILTER" | grep -v "grep"   | grep -v "kill_procs.sh"
PROCS=`ps aux|grep "$CMD" | grep "$FILTER" | grep -v "grep" | grep -v "kill_procs.sh" | awk '{print $2}'`
#echo $PROCS
for PID in ${PROCS[@]}
do
 echo "kill process: $PID "
 sudo kill -9 $PID
done


