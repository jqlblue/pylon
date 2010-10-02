<?php
class DBconf 
{/*{{{*/
    public $host="localhost";
    public $name="db_dev_cube";
    public $user="u_dev_cube";
    public $password="123";
}/*}}}*/

class Conf
{
    const PRJ_ROOT='/home/yunyou/devspace/psionic/demo';
    const APP_NAME = '彩云管理后台';
    const JS_RELEASE=false;
    const CSS_RELEASE=false;
    const JS_VER=2;
    const CSS_VER=2;
    const DEBUG_ENABLE=true;
    const PSIONIC='/home/yunyou/devspace/psionic/src';
    const ZMARK_ROOT='/home/z/php/zmark/';

    const ADMIN_TPL_PATH='src/web_inf/tpls/admin/';
    const BASE_TPL_PATH ='src/web_inf/tpls/base/';
    const STAGE_TPL_PATH='src/web_inf/tpls/stage/';

    const IMG_STAGE_PATH='/images/stage/';
    const STAGE_IMG_PATH='/images/stage/';

    
    
    public function getDBConf()
    {
        return new DBconf();
    }
    public function getParkTags()
    {
        return array("mainpark","userpark","modulepark","paypark"); 
    }

}
?>
