<?php

class DBconf
{
    public $host='127.0.0.1';
    public $name='pylon_${OWNER}';
    public $user='${OWNER}_pylon';
    public $password='pylon';
}
class Conf
{

//    const SGT_LIB='{prj_path}/sgtlib/';
//    const SGTJS_LIB='{prj_path}/sgtjs/';
//    const JS_LIB = '{owner}.code.book.net';
//    const LOGS_ROOT='{prj_path}/logs/';
//    const PRJ_ROOT='{prj_path}';
//    const APP_ROOT='{prj_path}/bookstore';
//    const FRONT_SYS='apps/storefront/';
//    const APPS_COMMON='apps/common/';
    public function getDBConf()
    {
        return new DBconf();
    }

}
?>
