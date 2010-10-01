<?php
class ModuleSvc
{
    static function buildPkgname($key,$version)
    {/*{{{*/
        return "pkg_".$key."_".$version.".7z";
    }/*}}}*/
    static function buildIconname($key,$version)
    {/*{{{*/
        return "ico_".$key."_".$version.".jpg";
    }/*}}}*/
    static function buildPkgurl($key,$version)
    {/*{{{*/
        return null;
        //return Conf::DOWN_FILE_DOMAIN."/".self::buildPkgname($key,$version);
    }/*}}}*/
    static function buildDllPath($key,$version)
    {/*{{{*/
        return "{$key}-{$version}\\{$key}.dll";
    }/*}}}*/
    static function isExistKey($key)
    {/*{{{*/
        $dq     = Dquery::ins();
        $module = $dq->get_module_by_mkey($key);
        return is_null($module)?false:true;
    } /*}}}*/
    static function getByKey($key)
    {/*{{{*/
        $dda    = DDA::ins();
        $moduleObj  = $dda->get_module_by_mkey($key);
        BR::notNull($moduleObj,"不存在KEY[$key]的组件");
        $moduleMark = ZMarkProxy::enable($moduleObj,new ModuleTagDef());
        $module = $moduleObj->getPropArray();
        unset($module['entityid']);
        $module['createtime'] = $moduleObj->createtime();
        $module['id'] = (int)$moduleObj->id();
        $module['key']  = $module['mkey'];
        $module['size'] = $module['msize'];
        $module['desc'] = $module['mdesc'];
        $module['tags'] = $moduleMark->getTags(ModuleTagDef::LABEL);
        $module['pkg_url']  = self::buildPkgurl($module['mkey'],$module['version']);
        //$module['icon_url'] = Conf::SHOW_FILE_DOMAIN.(($module['icon'])?$module['icon']:"/noicon.jpg");
        return $module;
    }/*}}}*/
    static function getByKeyVersion($key,$version)
    {/*{{{*/
        $dda    = DDA::ins();
        $moduleObj  = $dda->get_modulemore_by_mkey_version($key,$version);
        BR::notNull($moduleObj,"不存在KEY[$key]-VERSION[$version]的组件");
        $moduleMark = ZMarkProxy::enable($moduleObj,new ModuleTagDef());
        $module = $moduleObj->getPropArray();
        unset($module['entityid']);
        $module['id'] = $moduleObj->id();
        $module['key']  = $module['mkey'];
        $module['size'] = $module['msize'];
        $module['desc'] = $module['mdesc'];
        $module['tags'] = $moduleMark->getTags(ModuleTagDef::LABEL);
        $module['pkg_url']  = self::buildPkgurl($module['mkey'],$module['version']);
        $module['icon_url'] = Conf::SHOW_FILE_DOMAIN.(($module['icon'])?$module['icon']:"/noicon.jpg");
        return $module;
    }/*}}}*/
    static function listMoreByKey($key,$status=NULL)
    {/*{{{*/
        $dq    = Dquery::ins();
        if($status)
            $modules = $dq->list_modulemore_by_mkey_lifestatus($key,$status);
        else
            $modules = $dq->list_modulemore_by_mkey($key);
        BR::notNull($modules,"不存在KEY[$key]-的历史组件");
        foreach($modules as $k=>$module)
        {
            $modules[$k]['key'] = $module['mkey'];
            $modules[$k]['size'] = $module['msize'];
            $modules[$k]['desc'] = $module['mdesc'];
            $modules[$k]['pkg_url']  = self::buildPkgurl($module['mkey'],$module['version']);
            //$modules[$k]['icon_url'] = Conf::SHOW_FILE_DOMAIN.(($module['icon'])?$module['icon']:"/noicon.jpg");
        }
        return $modules;
    }/*}}}*/
    static function editByKey($key,$name,$developer,$desc,$icon=null,$tags,$pkg=null,$version)
    {/*{{{*/
        $dda    = DDA::ins();
        $moduleObj  = $dda->get_module_by_mkey($key);
        BR::notNull($moduleObj,"不存在KEY[$key]的组件");
        if($moduleObj->version!=$version)
        {
            //BR::isTrue($icon["name"],"因版本更新必须上传ICON");
            BR::isTrue(($key=="mainexe")||$pkg["name"],"因版本更新必须上传ZIP包");
        }
        $iconUrl = null;
        if($icon["name"])
        {
            UploadSvc::init(Conf::SHOW_FILE_PATH,"",102400000);
            $u = UploadSvc::getUpload("image");
            if(!empty($icon))
            {
                $resUpload = $u->upload($icon,self::buildIconname($key,$version));
                BR::isTrue($resUpload['ST']=="OK","上传图片出错");
                $iconUrl = $resUpload['URL'];
            }
        }
        if($pkg["name"])
        {
            UploadSvc::init(Conf::DOWN_FILE_PATH,"",102400000);
            $u = UploadSvc::getUpload("pkg");
            if(!empty($pkg))
            {
                $resUpload = $u->upload($pkg,self::buildPkgname($key,$version));
                BR::isTrue($resUpload['ST']=="OK",$resUpload['MSG']);
                $pkgUrl = $resUpload['URL'];
            }
        }
        $moduleObj->update($name,$desc,$iconUrl,$developer,null,null,$version);
        $module = ZMarkProxy::enable($moduleObj,new ModuleTagDef());
        $module->replaceTags(ModuleTagDef::LABEL,$tags);
        return true;
    }/*}}}*/
    static function add($key,$name,$developer,$desc,$icon=null,$tags,$pkg=null,$version,$mtype)
    {/*{{{*/
        $iconUrl = null;
        /*
        if($icon["name"])
        {
            UploadSvc::init(Conf::SHOW_FILE_PATH,"",102400000);
            $u = UploadSvc::getUpload("image");
            if(!empty($icon))
            {
                $resUpload = $u->upload($icon,self::buildIconname($key,$version));
                BR::isTrue($resUpload['ST']=="OK","上传图片出错");
                $iconUrl = $resUpload['URL'];
            }
        }
        if($pkg["name"])
        {
            UploadSvc::init(Conf::DOWN_FILE_PATH,Conf::DOWN_FILE_DOMAIN,102400000);
            $u = UploadSvc::getUpload("pkg");
            if(!empty($pkg))
            {
                $resUpload = $u->upload($pkg,self::buildPkgname($key,$version));
                BR::isTrue($resUpload['ST']=="OK","上传文件出错");
                $pkgUrl = $resUpload['URL'];
            }
        }
         */
        $module = Module::createByBiz($key,$name,$desc,$iconUrl,$developer,null,null,$version,$mtype);
        if($module)
        {
            Daofinder::query("ModulemoreQuery")->add($module->id(),$key,$name,$desc,$icon,$developer,$depend,$recommend,$version,$mtype);
        }
        //$module = ZMarkProxy::enable($module,new ModuleTagDef());
        //$module->addTags(ModuleTagDef::LABEL,$tags);
        return true;
    }/*}}}*/
    static function listBy($mtype,$life=null,$offset=null,$num=null)
    {/*{{{*/
        $modules = DaoFinder::query("ModuleQuery")->listBy($mtype,$life,$offset,$num);
        $dda    = DDA::ins();
        foreach($modules as $k => $module)
        {
            $modules[$k]['id']  =  (int)$module['id'];
            $modules[$k]['key']  = $module['mkey'];
            $modules[$k]['desc'] = $module['mdesc'];
            $modules[$k]['size'] = $module['msize'];
            $moduleObj  = $dda->get_module_by_id($module['id']);
            $moduleMark = ZMarkProxy::enable($moduleObj,new ModuleTagDef());
            $modules[$k]['tags'] = $moduleMark->getTags(ModuleTagDef::LABEL);
            $modules[$k]['pkg_url']  = self::buildPkgurl($module['mkey'],$module['version']);
            //$modules[$k]['icon_url'] = Conf::SHOW_FILE_DOMAIN.(($module['icon'])?$module['icon']:"/noicon.jpg");
        }
        return $modules;
    }/*}}}*/
    static function listMoreBy($mtype,$life=null,$offset=null,$num=null)
    {/*{{{*/
        $modules = DaoFinder::query("ModulemoreQuery")->listBy($mtype,$life,$offset,$num);
        $dda    = DDA::ins();
        foreach($modules as $k => $module)
        {
            $modules[$k]['key']  = $module['mkey'];
            $modules[$k]['desc'] = $module['mdesc'];
            $modules[$k]['size'] = $module['msize'];
            $modules[$k]['pkg_url']  = self::buildPkgurl($module['mkey'],$module['version']);
            $modules[$k]['icon_url'] = Conf::SHOW_FILE_DOMAIN.(($module['icon'])?$module['icon']:"/noicon.jpg");
        }
        return $modules;
    }/*}}}*/
    static function delMore($key,$version)
    {/*{{{*/
        $dda    = DDA::ins();
        $moduleObj  = $dda->get_modulemore_by_mkey_version($key,$version);
        $moduleObj->del();
        return true;
    }/*}}}*/
}
