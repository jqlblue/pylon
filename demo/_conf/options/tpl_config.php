<?php
$prj_path  = "${PYLON_HOME}/src/:${PRJ_ROOT}/_src:/home/z/php:";
set_include_path($prj_path.get_include_path() );
class DBconf 
{/*{{{*/
    public $host="${DB_HOST}";
    public $name="${DB_NAME}";
    public $user="${DB_USER}";
    public $password="${DB_PWD}";
}/*}}}*/

class Conf
{
    const PRJ_ROOT='${PRJ_ROOT}';
    const APP_NAME = 'PYLON管理后台';
    const JS_RELEASE=false;
    const CSS_RELEASE=false;
    const JS_VER=2;
    const CSS_VER=2;
    const DEBUG_ENABLE=true;
    const PYLON='${PYLON_HOME}/src';
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
