CUR_PATH=`readlink /proc/$$/fd/255 | xargs dirname`
. $CUR_PATH/../env_def.sh

root=$PRJ_HOME
proot=/home/z/psionic
cd $root
rm -rf .prj/cscope.*
csfile=/tmp/cscope.files
find $root/ -name "*.php" >  $csfile
find $root/ -name "*.html" >> $csfile
find $root/ -name "*.htm" >>  $csfile
find $root/ -name "*.sh" >>   $csfile
find $root/ -name "*.inc" >>  $csfile
find $root/ -name "*.sql" >>  $csfile
find $root/ -name "*.js" >>   $csfile
find $root/ -name "*.css" >>  $csfile
find $root/ -name "*.conf" >> $csfile
find $root/ -name "*.tpl" >>  $csfile
find /home/z/psionic/ -name "*.php" >> $csfile
find $proot/pylon_ui/ -name "*.php" >> $csfile 
cd .prj
cscope -b  -i $csfile
