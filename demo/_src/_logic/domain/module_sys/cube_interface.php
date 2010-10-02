<?php
interface ICube
{
    public function getCubeList($type);
    public function getCubeByKey($key);    
}
class CubeImpl implements ICube
{
    private static function formatCubeArr($cube)
    {/*{{{*/
        $fmtCube  = array();
        $filterArr= array("createtime","id","key","name","desc","developer","version","dll_path","pkg_url","icon_url","status");
        foreach($filterArr as $filter)
            $fmtCube[$filter] = $cube[$filter];
        return $fmtCube; 
    }/*}}}*/

    public function getCubeList($type)
    {/*{{{*/
        $types = Module::listmtype();
        if(!in_array($type,$types))
            $type = $types['cube'];
        $cubes = ModuleSvc::listBy($type,LifeStatus::NORMAL);
        $cubeList = array();
        foreach($cubes as $cube)
        {
            $cube["dll_path"] = ModuleSvc::buildDllPath($cube['key'],$cube['version']);
            $cube['status']   = 1;
            $cubeList[] = self::formatCubeArr($cube);
        }
        return $cubeList;
    }/*}}}*/
    public function getCubeByKey($key)
    {/*{{{*/
        $cube = ModuleSvc::getByKey($key);
        $cube["dll_path"] = ModuleSvc::buildDllPath($cube['key'],$cube['version']);
        $cube['status']   = 1;
        return self::formatCubeArr($cube);
    }/*}}}*/
    public function getCubeByKeyVersion($key,$version)
    {/*{{{*/
        $cube = ModuleSvc::getByKeyVersion($key,$version);
        $cube["dll_path"] = ModuleSvc::buildDllPath($cube['key'],$cube['version']);
        $cube['status']   = 1;
        return self::formatCubeArr($cube);
    }/*}}}*/
    public function setCube($key,$name,$desc,$developer,$version)
    {/*{{{*/
        $key = BR::isTrue($key,"请输入组件KEY!");
        BR::isTrue($_FILES['pkg']['name'],"no zip");
        $name      = BR::isTrue($name,"请输入组件名称");
        $developer = BR::isTrue($developer,"请输入组件开发者信息");
        $desc      = $desc;
        $tags      = ""; 
        $version   = $version;
        if(!ModuleSvc::isExistKey($key))
        {
            ModuleSvc::add($key,$name,$developer,$desc,null,$tags,$_FILES['pkg'],$version);
        }
        else
        {
            ModuleSvc::editByKey($key,$name,$developer,$desc,null,$tags,$_FILES['pkg'],$version);
        }
        return "ok";
    }/*}}}*/

}

