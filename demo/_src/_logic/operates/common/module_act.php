<?php
class ModuleAction extends AdminBaseAction
{
    public function __construct()
    {/*{{{*/
        parent::__construct("submit_module_add,submit_module_edit");
    }/*}}}*/
    public function do_module_list($vars,$xcontext,$dda)
    {/*{{{*/
        $pageObj = $vars->pageObj;
        $offset  = ($pageObj->curpage-1)*$pageObj->pagerows;
        $num     = $pageObj->pagerows;
        $modules = ModuleSvc::listBy(null,null,$offset,$num);
        array_walk($modules,create_function('&$item,$key','
            $item[lifestatus] = MiniOPs::lifeStatus($item[lifestatus],"module",$item[id]);
        '));
        $xcontext->modules = $modules;
        $xcontext->mtypenames = array_flip(Module::listmtype());
    }/*}}}*/
    public function do_module_add($vars,$xcontext,$dda)
    {/*{{{*/
        $xcontext->mtypes = Module::listmtype();
    }/*}}}*/
    public function do_submit_module_add($vars,$xcontext,$dda)
    {/*{{{*/
        $key     = BR::isTrue($vars->key,"请输入组件KEY!");
        $version = BR::isTrue($vars->version,"请输入组件VERSION!");
        if(!ModuleSvc::isExistKey($key))
        {
            BR::isTrue($_FILES['pkg']['name'],"请上传zip文件包");
            $name      = BR::isTrue($vars->name,"请输入组件名称");
//            $developer = BR::isTrue($vars->developer,"请输入组件开发者信息");
            $desc      = $vars->desc;
            $tags      = $vars->tags;
            $version   = $vars->version;
            $mtype     = $vars->mtype;
            ModuleSvc::add($key,$name,$developer,$desc,$_FILES['icon'],$tags,$_FILES['pkg'],$version,$mtype);
            return XConst::SUCCESS;
        }
        throw new BizException("组件KEY已存在!");
    }/*}}}*/
    public function do_module_edit($vars,$xcontext,$dda)
    {/*{{{*/
        $key = BR::isTrue($vars->key,"请传递组件KEY!");
        $module = ModuleSvc::getByKey($key);
        $module['tags'] = implode(" ",$module['tags']);
        $xcontext->module = $module;
        $xcontext->mtypes = Module::listmtype();
        $appTplPath  = Conf::APP_ROOT . "/" . Conf::ADMIN_TPL_PATH;
        $baseTplPath = Conf::APP_ROOT . "/" . Conf::BASE_TPL_PATH;
        $tpl = InterceptUtls::actionTpl("module_add", $appTplPath,$baseTplPath);
        $xcontext->_autoview = $tpl;
    }/*}}}*/
    public function do_submit_module_edit($vars,$xcontext,$dda)
    {/*{{{*/
        $key = BR::isTrue($vars->key,"请输入组件KEY!");
        $name      = BR::isTrue($vars->name,"请输入组件名称");
        $developer = BR::isTrue($vars->developer,"请输入组件开发者信息");
        $desc      = $vars->desc;
        $tags      = $vars->tags;
        $version   = $vars->version;
        ModuleSvc::editByKey($key,$name,$developer,$desc,$_FILES['icon'],$tags,$_FILES['pkg'],$version);
        return XConst::SUCCESS;
    }/*}}}*/
    public function do_module_more($vars,$xcontext,$dda)
    {
        $modules = ModuleSvc::listMoreByKey($vars->key);
        array_walk($modules,create_function('&$item,$key','
            $item[lifestatus_txt] = LifeStatus::nameOf($item[lifestatus]);
        '));
        $xcontext->modules = $modules;
    }
    public function do_modulemore_del($vars,$xcontext,$dda)
    {
        ModuleSvc::delMore($vars->key,$vars->version); 
        return XConst::SUCCESS;
    }
}
?>
