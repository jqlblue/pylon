<?php
class DBconf
{
    public $host='localhost';
    public $name='apollo_{owner}';
    public $user='{owner}';
    public $password='apollo';
}
class Conf
{
    const JS_RELEASE=false;
    const CSS_RELEASE=false;
    const JS_VER=2;
    const CSS_VER=2;
    const DEBUG_ENABLE=true;
    const SGT_LIB='/home/z/php/sgtlib/';
    const SGTJS_LIB='/home/z/js/sgtjs/';
    const JS_LIB='intg.code.igger.net';
    const LOGS_ROOT='{prj_path}/logs/';
    const APP_ROOT='{prj_path}/src';
    const DATA_PATH='/home/z/data/apollo/';
    const STAT_SVC ='{owner}.stat.zlabs.cn';
    const SHOW_DOMAIN ='{owner}.cd.zlabs.cn';
    const SHOW_SVC_PLUGIN_PATH='{prj_path}/src/apps/show_svc/plugin';
    const SHOW_FILE_DOMAIN  = 'http://img.union.zlabs.cn';
    const ADMIN_FILE_DOMAIN = 'http://master.img.union.zlabs.cn';
    const SHOW_FILE_PATH='/home/z/imgs/apollo_master';///home/z/imgs/apollo_slave
    const ID_CARDS_PATH='/home/z/imgs/idcards';
    const EVENT_ENV="test";
    const EVENT_MSG_SVC="msg.e.lab.ods.org";
    const EVENT_SVC="event.lm.17k.com";
    const UC_COOKIE_NAME='17KUN_AUTH'; // cookie 名称
    const EMAIL_TPL='{prj_path}/src/apps/common/email';
    const EMAIL_ADDR='{prj_path}/data/emailaddr';
    const IMG_SOWNER_PATH='/images/17k/';
    const ADMIN_TPL_PATH='apps/admin_sys/views/17k';
    const SOWNER_TPL_PATH='apps/sowner_sys/views/17k';
    const HELPER_SVC_DOMAIN='helper.sgtlib.cn';
    const HELPER_PRODUCT='apollo';
    const HELPER_KEYT='ollopa';
    public function getDBConf()
    {
        return new DBconf();
    }
    public function getStatMonitorSvrs()
    {
        return  array('127.0.0.1');
    }

}

class APPConf
{
    const DOMIN_NAME='{owner}.union.zlabs.cn';
    const ADMIN_DOMAIN_NAME='{owner}.admin.union.zlabs.cn';
    const EVENT_NAME='event.union.zlabs.cn';
}
?>
