<?php
class Action_module_list extends AdminActionBase  
{/*{{{*/
    static $_tag = array("modulepark");
    static $_name = "组件列表";
    public function  _run($request ,$xcontext)
    {/*{{{*/
        $pageObj = $request->pageObj;
        $offset  = ($pageObj->curpage-1)*$pageObj->pagerows;
        $num     = $pageObj->pagerows;
        $modules = ModuleSvc::listBy(null,null,$offset,$num);
        array_walk($modules,create_function('&$item,$key','
            $item[lifestatus] = MiniOPs::lifeStatus($item[lifestatus],"module",$item[id]);
        '));
        $xcontext->modules = $modules;
        $xcontext->mtypenames = array_flip(Module::listmtype());
        $xcontext->pageObj = $pageObj;
    }/*}}}*/
}/*}}}*/
class Action_module_add extends ArsyncPostAdmBase
{/*{{{*/
    public static function _validate_conf($conf)
    {
    }
    public function _run($request,$xcontext)
    {/*{{{*/
        if($request->submit_module_add)
        {
            $request->submit_module_add = false;
            $key = BR::isTrue($request->key,"请输入组件KEY!");
            $version = BR::isTrue($request->version,"请输入组件VERSION!");
            if(!ModuleSvc::isExistKey($key))
            {
                $name      = BR::isTrue($request->name,"请输入组件名称");
                $desc      = $request->desc;
                $tags      = $request->tags;
                $version   = $request->version;
                $mtype     = $request->mtype;
                ModuleSvc::add($key,$name,$developer,$desc,$_FILES['icon'],$tags,$_FILES['pkg'],$version,$mtype);
                return   XNext::useTpl("admin_success.html");
            }
        }
        else
        {
            $xcontext->mtypes = Module::listmtype();
            return XNext::useTpl("AUTO");
        }
    }/*}}}*/
}/*}}}*/
class Action_module_more extends AdminActionBase
{/*{{{*/
    public function _run($request,$xcontext)
    {/*{{{*/
        $modules = ModuleSvc::listMoreByKey($request->key);
        array_walk($modules,create_function('&$item,$key','
            $item[lifestatus_txt] = LifeStatus::nameOf($item[lifestatus]);
        '));
        $xcontext->modules = $modules;
        return XNext::useTpl("module_more.html");
    }/*}}}*/
}/*}}}*/
class Action_module_edit extends AdminActionBase
{/*{{{*/
    public function _run($request,$xcontext)
    {/*{{{*/
        if($request->submit_module_edit)
        {
            $key = BR::isTrue($request->key,"请输入组件KEY!");
            $name      = BR::isTrue($request->name,"请输入组件名称");
            $developer = BR::isTrue($request->developer,"请输入组件开发者信息");
            $desc      = $request->desc;
            $tags      = $request->tags;
            $version   = $request->version;
             ModuleSvc::editByKey($key,$name,$developer,$desc,$_FILES['icon'],$tags,$_FILES['pkg'],$version);
            return   XNext::useTpl("admin_success.html");
            //return XNext::action("module_list");
        }
        else
        {
            $key = BR::isTrue($request->key,"请传递组件KEY!");
            $module = ModuleSvc::getByKey($key);
            $module['tags'] = implode(" ",$module['tags']);
            $xcontext->module = $module;
            $xcontext->mtypes = Module::listmtype();
            return XNext::useTpl("module_add.html");
        }
    }/*}}}*/
}/*}}}*/
class Action_modulemore_del extends AdminActionBase
{/*{{{*/
    public function _run($request,$xcontext)
    {/*{{{*/
        ModuleSvc::delMore($request->key,$request->version); 
        return XNext::action("module_list");
    }/*}}}*/
}/*}}}*/
?>
