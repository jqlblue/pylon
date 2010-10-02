<?php
$prj_path  = "/home/dev/devspace/pylon/src/:/home/dev/devspace/pylon/demo/_src:/home/z/php:";
set_include_path($prj_path.get_include_path() );
class DBconf 
{/*{{{*/
    public $host="127.0.0.1";
    public $name="demo_db";
    public $user="demo_user";
    public $password="123";
}/*}}}*/

class Conf
{
    const PRJ_ROOT='/home/dev/devspace/pylon/demo';
    const APP_NAME = 'PYLON管理后台';
    const JS_RELEASE=false;
    const CSS_RELEASE=false;
    const JS_VER=2;
    const CSS_VER=2;
    const DEBUG_ENABLE=true;
    const PYLON='/home/dev/devspace/pylon/src';
    const ZMARK_ROOT='/home/z/php/zmark/';

    const ADMIN_TPL_PATH='_src/_web_inf/tpls/admin/';
    const BASE_TPL_PATH ='_src/_web_inf/tpls/base/';
    const STAGE_TPL_PATH='_src/_web_inf/tpls/stage/';

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
