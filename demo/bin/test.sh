#!/bin/bash
echo "****************************************************"
echo "****************This is Test Shell Scrite***********"
echo "----------------Show Env Var ------------------------"
echo $PRJ_ROOT
echo $TEST_VAR
echo $_UID


case $1 in
    config)
        echo " Exec Config "
        exit;
        ;;
    data)
        echo " Exec Data "
        exit;
        ;;
    start)
        echo " Exec start "
        exit;
        ;;
    stop)
        echo " Exec stop "
        exit;
        ;;
esac

echo "*****************End***********"

