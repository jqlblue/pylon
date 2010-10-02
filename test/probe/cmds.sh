export PRJ_PATH=/home/zwj/devspace/psionic
python /home/zwj/devspace/psionic/probe/src/tplfile.py  -t /home/zwj/devspace/psionic/probe/test/props/conf/options/tpl_my_php.ini -d /home/zwj/devspace/psionic/probe/test/props/conf/builded/zwj_my_php.ini 
if test -L /home/zwj/devspace/psionic/probe/test/props/conf/used/my_php.ini; then sudo rm -f  /home/zwj/devspace/psionic/probe/test/props/conf/used/my_php.ini ; fi ;sudo ln -s /home/zwj/devspace/psionic/probe/test/props/conf/builded/zwj_my_php.ini /home/zwj/devspace/psionic/probe/test/props/conf/used/my_php.ini 
python /home/zwj/devspace/psionic/probe/src/tplfile.py  -t /home/zwj/devspace/psionic/probe/test/props/conf/options/tpl_you_php.ini -d /home/zwj/devspace/psionic/probe/test/props/conf/builded/zwj_you_php.ini 
if test -L /home/zwj/devspace/psionic/probe/test/props/conf/used/you_php.ini; then sudo rm -f  /home/zwj/devspace/psionic/probe/test/props/conf/used/you_php.ini ; fi ;sudo ln -s /home/zwj/devspace/psionic/probe/test/props/conf/builded/zwj_you_php.ini /home/zwj/devspace/psionic/probe/test/props/conf/used/you_php.ini 
python /home/zwj/devspace/psionic/probe/src/tplfile.py  -t /home/zwj/devspace/psionic/probe/test/props/conf/options/zenv_tpl.conf -d /home/zwj/devspace/psionic/probe/test/props/conf/builded/zwj_zenv.conf 
if test -L /home/zwj/devspace/psionic/probe/test/props/conf/used/zenv.conf; then sudo rm -f  /home/zwj/devspace/psionic/probe/test/props/conf/used/zenv.conf ; fi ;sudo ln -s /home/zwj/devspace/psionic/probe/test/props/conf/builded/zwj_zenv.conf /home/zwj/devspace/psionic/probe/test/props/conf/used/zenv.conf 
python /home/zwj/devspace/psionic/probe/src/tplfile.py  -t /home/zwj/devspace/psionic/probe/test/props/conf/options/php_tpl.ini -d /home/zwj/devspace/psionic/probe/test/props/conf/builded/zwj_php.ini 
if test -L /home/zwj/devspace/psionic/probe/test/props/conf/used/php.ini; then sudo rm -f  /home/zwj/devspace/psionic/probe/test/props/conf/used/php.ini ; fi ;sudo ln -s /home/zwj/devspace/psionic/probe/test/props/conf/builded/zwj_php.ini /home/zwj/devspace/psionic/probe/test/props/conf/used/php.ini 
sudo /usr/local/apache2/bin/apachectl restart
sudo python /home/zwj/devspace/psionic/minerals/src/confutls.py  -n zwj.test.com  -f /etc/hosts  -t '#' -c '127.0.0.1 zwj.test.com' 
/home/zwj/devspace/psionic/probe/test/props/echo.sh
