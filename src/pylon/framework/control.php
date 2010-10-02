<?php
interface IControl 
{/*{{{*/
    const ALLOW=true;
    const FORBID=false;
    public function need_do($name,$params);
    public function do_before($name,$params);
    public function do_after($name,$params);
    public function other_after($name,$params);
};/*}}}*/

class EmptyControl implements IControl
{/*{{{*/
    public function need_do($name,$params)
    {/*{{{*/
        return false;
    }/*}}}*/
    public function do_before($name,$params)
    {/*{{{*/
    }/*}}}*/
    public function do_after($name,$params)
    {/*{{{*/
    }/*}}}*/

    public function other_after($name,$params)
    {/*{{{*/
    }/*}}}*/
}/*}}}*/

class PylonCtrl
{/*{{{*/
    const ON =1 ;
    const OFF=2;
    static public $s_daoCacheCtrl = null;
    static public $s_objLoadCtrl = null;
    static public $s_clearCacheStg = null;
    static public function daoCache()
    {/*{{{*/
        if (self::$s_daoCacheCtrl == null)
            self::$s_daoCacheCtrl =  new DiyKeyControl(IControl::ALLOW, array(""));
        return self::$s_daoCacheCtrl;
    }/*}}}*/
    static public function objLazyLoad()
    {/*{{{*/
        if (self::$s_objLoadCtrl== null)
            self::$s_objLoadCtrl=  new DiyKeyControl(IControl::ALLOW, array(""));
        return self::$s_objLoadCtrl;
    }/*}}}*/
    static public function  switchDaoCache($switch)
    {/*{{{*/
        if($switch == PylonCtrl::ON)
            DaoFinder::regBinder( new DaoProxyBinder);
        if($switch == PylonCtrl::OFF)
            DaoFinder::regBinder(null);
    }/*}}}*/
    static public function  switchLazyLoad($switch)
    {/*{{{*/
        if($switch == PylonCtrl::ON)
            self::objLazyLoad()->reset(IControl::ALLOW, array(""));
        if($switch == PylonCtrl::OFF)
            self::objLazyLoad()->reset(IControl::FORBID, array(""));
    }/*}}}*/

    static public function clearCacheStg()
    {/*{{{*/
        if (self::$s_clearCacheStg  == null)
            self::$s_clearCacheStg = new MappingConf(create_function(
                '$cls',' return array($cls,"$cls"."Query");'));
        return  self::$s_clearCacheStg;
    }/*}}}*/

}/*}}}*/

class DiyKeyControl extends EmptyControl
{/*{{{*/
    public function __construct($default,$excepts)
    {/*{{{*/
        $this->reset($default,$excepts);

    }/*}}}*/
    public function need_do($name,$params)
    {/*{{{*/
        $key = strtolower($name);
        if(array_key_exists($key,$this->excepts))
        {
            return ! $this->default ;
        }
        return $this->default;

    }/*}}}*/
    public function addExcepts($appends)
    {/*{{{*/
        array_walk($appends,create_function('&$item,$key','$item = strtolower($item);'));
        $appendsArr = array_combine($appends,$appends);
        $this->excepts=  array_merge($this->excepts,$appendsArr);

    }/*}}}*/
    public function reset($default,$excepts)
    {/*{{{*/
        array_walk($excepts,create_function('&$item,$key','$item = strtolower($item);'));
        $this->default= $default;
        $this->excepts= array_combine($excepts,$excepts);
    }/*}}}*/
}/*}}}*/

class DaoInnerCacheCtrl extends DiyKeyControl
{/*{{{*/
    public function __construct($cls,$clearMap)
    {/*{{{*/
        DBC::requireNotNull($clearMap,'DaoInnerCacheCtrl::$clearMap not null');
        parent::__construct(DiyKeyControl::ALLOW,array("add","set","del","update","getRegName","getExecuter"));
        $this->cls = $cls;
        $this->clearMap = $clearMap;
    }/*}}}*/
    public function other_after($name,$params)
    {/*{{{*/
        if(!parent::need_do($name,$params))
        {
            $clears  = $this->clearMap->values($this->cls);
            CacheAdmin::$netCacheSpace->clearArr($clears);
            CacheAdmin::$innerCacheSpace->clearAll();
        }
    }/*}}}*/
}/*}}}*/

class MappingConf 
{/*{{{*/
    public function __construct($defaultFun)
    {/*{{{*/
        $this->reset($defaultFun);
    }/*}}}*/
    public function addAddtions($key,$values)
    {/*{{{*/
        $key =  strtolower($key);
        $this->addtions[$key] = $values;
    }/*}}}*/
    public function reset($defaultFun)
    {/*{{{*/
        $this->defaultFun = $defaultFun;
        $this->addtions    =  array();
    }/*}}}*/
    public function values($key)
    {/*{{{*/
        $key =  strtolower($key);
        $addtions = array();
        if( isset($this->addtions[$key])  )
        {
           $addtions = $this->addtions[$key]; 
        }
        return array_merge($addtions,call_user_func($this->defaultFun,$key));
    }/*}}}*/
}/*}}}*/
