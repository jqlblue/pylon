export RIGGER=$PYLON_HOME/src/rigger
python   $RIGGER/run.py  -p $PYLON_HOME/demo/_conf -f $PYLON_HOME/demo/._pylon.dat -e dev -s all  $*

