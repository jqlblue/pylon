ROOT=$PSIONIC_HOME/pylon/test/
find  $ROOT/ -name "*.php" | xargs cat | grep -E "extends UnitTestCase" | sed -e 's/\s*class\s*/$tcs[]="/g' -e 's/\s*extends UnitTestCase/";/g'  > $ROOT/tc_list.txt 
php  -c $ROOT/config/php.ini   -f  $ROOT/alltest.php all > /tmp/out.txt;   cat /tmp/out.txt

