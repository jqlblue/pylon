<?php
class DBconf
{
    public $host='localhost';
    public $name='apollo_online';
    public $user='online';
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
    const LOGS_ROOT='home/logs/';
    const APP_ROOT='home/src';
    const DATA_PATH='/home/z/data/apollo/';
    const STAT_SVC ='online.stat.zlabs.cn';
    const SHOW_DOMAIN ='online.cd.zlabs.cn';
    const SHOW_SVC_PLUGIN_PATH='home/src/apps/show_svc/plugin';
    const SHOW_FILE_DOMAIN  = 'http://img.union.zlabs.cn';
    const ADMIN_FILE_DOMAIN = 'http://master.img.union.zlabs.cn';
    const SHOW_FILE_PATH='/home/z/imgs/apollo_master';///home/z/imgs/apollo_slave
    const ID_CARDS_PATH='/home/z/imgs/idcards';
    const EVENT_ENV="test";
    const EVENT_MSG_SVC="msg.e.lab.ods.org";
    const EVENT_SVC="event.lm.17k.com";
    const UC_COOKIE_NAME='17KUN_AUTH'; // cookie 名称
    const EMAIL_TPL='home/src/apps/common/email';
    const EMAIL_ADDR='home/data/emailaddr';
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
    const DOMIN_NAME='online.union.zlabs.cn';
    const ADMIN_DOMAIN_NAME='online.admin.union.zlabs.cn';
    const EVENT_NAME='event.union.zlabs.cn';
}
?>
