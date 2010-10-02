<?php

/**\addtogroup domin_mdl
 * @{
 */

class CallWrapper extends PropertyObj
{/*{{{*/
    public function __construct($fun,$obj)
    {
        $this->fun=$fun;
        $this->obj=$obj;
    }
    public function __call($name,$params)
    {
        $ret=call_user_func_array(array($this->obj,$name),$params);
        return call_user_func($this->fun,$ret);
    }
}/*}}}*/

class  LDProxy
{/*{{{*/
    private  $fun;
    private  $args;
    private  $obj = null;

    public function __construct($fun,$args)
    {/*{{{*/
        DBC::requireNotNull($fun);
        $this->fun = $fun;
        $this->args=$args;
    }/*}}}*/
    public function loadObj()
    {/*{{{*/
        $this->obj=call_user_func($this->fun,$this->args);
        DBC::requireNotNull($this->obj);
    }/*}}}*/
    public function getObj()
    {/*{{{*/
        if($this->obj == null)
            $this->loadObj();
        return $this->obj;
    }/*}}}*/
    public function __get($name)
    {/*{{{*/
        if($this->obj==null)
            $this->loadObj();
        return $this->obj->$name;
    }/*}}}*/
    public function __set($name,$value)
    {/*{{{*/
        if($this->obj==null)
            $this->loadObj();
        $this->obj->$name=$value;
    }/*}}}*/
    public function __call($name,$params)
    {/*{{{*/
        if($this->obj==null)
            $this->loadObj();
        $obj = call_user_func_array(array($this->obj,$name),$params);
        return $obj;

    }/*}}}*/

}/*}}}*/

class  CtrlCacheProxy
{/*{{{*/
    private  $cache;
    private  $dao;
    private  $prefix;
    private  $ctrl;

    public function __construct($cache,$dao,$ctrl)
    {/*{{{*/
        $this->cache=$cache;
        $this->dao=$dao;
        $this->ctrl= $ctrl;
    }/*}}}*/
    public function __get($name)
    {/*{{{*/
        return $this->dao->$name;
    }/*}}}*/
    public function haveDataPage($params)
    {/*{{{*/
        foreach($params as $i)
        {
            if($i instanceof DataPage)
            {
                return true;
            }
        }
        return false;
    }/*}}}*/
    public function __call($name,$params)
    {/*{{{*/
        if ($this->ctrl->need_do($name,$params) && ! $this->haveDataPage($params))
        {/*{{{*/
            $this->ctrl->do_before($name,$params);
            $key     = md5($name.serialize($params));
            $obj     =  $this->cache->get($key);
            if($obj) return  $obj;
            $obj = call_user_func_array(array($this->dao,$name),$params);
            if($obj == null) return $obj;
            $this->cache->set($key,$obj);
            $this->ctrl->do_after($name,$params);
            return $obj;
        }/*}}}*/
        $obj = call_user_func_array(array($this->dao,$name),$params);
        $this->ctrl->other_after($name,$params);
        return $obj;

    }/*}}}*/

}/*}}}*/

class DaoProxyBinder
{/*{{{*/
    public function __construct()
    {/*{{{*/
        $this->idmaps =  array();
    }/*}}}*/
    public function proxy($name,$dao)
    {/*{{{*/
        $daoCtrl=  new DaoInnerCacheCtrl($name,PylonCtrl::clearCacheStg());
        $innerCache = CacheAdmin::$innerCacheSpace->find($name);
        if (PylonCtrl::daoCache()->need_do($name,$dao))
        {
                
            $innerProxy = new  CtrlCacheProxy($innerCache,$dao,$daoCtrl);
            $cache = CacheAdmin::$netCacheSpace->find($name);
           return new CtrlCacheProxy($cache,$innerProxy, $daoCtrl);
        }
        return new  CtrlCacheProxy($innerCache,$dao,$daoCtrl);
    }/*}}}*/
}/*}}}*/


class  RWCacheProxy
{/*{{{*/
    private  $cache;
    private  $dao;
    private  $prefix;
    private  $observer;
    private  $groupkey;

    public function __construct($cache,$dao,$prefix,$observer)
    {/*{{{*/
        $this->cache=$cache;
        $this->dao=$dao;
        $this->prefix = $prefix;
        $this->observer= $observer;
        $this->groupkey= $this->prefix.get_class($this->dao);
    }/*}}}*/
    public function __get($name)
    {
        return $this->dao->$name;
    }
    public function __call($name,$params)
    {/*{{{*/
        if($this->observer->isNotCare($name))
        {
            return call_user_func_array(array($this->dao,$name),$params);
        }
        if($this->observer->isSensitive4Add($name))
        {/*{{{*/
            $cls=get_class($this->dao);
            $funname=$this->observer->getSensitiveName($name);
            $key = md5($this->prefix.$funname.serialize($params));

            $this->observer->regSensitive4Add($this->groupkey,$key);
            if($this->dao instanceof RWCacheProxy)
            {
                call_user_func_array(array($this->dao,$name),$params);
            }
            return ;
        }/*}}}*/
        $key = md5($this->prefix.$name.serialize($params));
        if($this->observer->isWriteCall($name))
        {/*{{{*/
            $obj = call_user_func_array(array($this->dao,$name),$params);
            if($this->observer->isAddCall($name))
            {
                $this->observer->invalidate4Add($this->groupkey,$this->cache);
            }
            else
            {
                $this->observer->invalidate($params[0],$this->cache);
            }
            return $obj;
        }/*}}}*/
        else
        {/*{{{*/
            //            Debug::watch(__FILE__,__LINE__,$key,'$key');
            $obj =  $this->cache->get($key);
            if($obj) 
            {
                return  $obj;
            }
            $obj = call_user_func_array(array($this->dao,$name),$params);
            if($obj == null)
                return $obj;
            $this->cache->set($key,$obj);
            if(is_array($obj) && !isset($obj['id']))
            {
                $this->observer->regCachedlist($key,$obj);
            }
            else
            {
                $this->observer->regCached($key,$obj);
            }
            return $obj;
        }/*}}}*/

    }/*}}}*/

}/*}}}*/
abstract 
    class CacheObserverBase
    {/*{{{*/
        private $keysMap;
        public function __construct($keysMap)
        {/*{{{*/
            DBC::requireNotNull($keysMap);
            $this->keysMap = $keysMap;
        }/*}}}*/
        public function regCachedlist($key,&$arr)
        {/*{{{*/
            foreach ($arr as $item)
            {
                $this->regCached($key,$item);
            }
        }/*}}}*/
        abstract public function isNotCare($name);
        abstract public function isSensitive4Add($name);
        abstract public function getSensitiveName($name);
        abstract public function isWriteCall($name);
        abstract function isAddCall($name);
        public function regCached($key,$data)
        {/*{{{*/
            $id=0;
            if(is_object($data))
                $id=$data->id();
            elseif(is_array($data))
                $id=$data['id'];
            $id=intval($id); // must convert int value,  because id will change to string value;
            $cacheKeys = $this->keysMap->get($id);
            if($cacheKeys == null ) $cacheKeys=array();
            $cacheKeys[]=$key;
            $this->keysMap->set($id,$cacheKeys);
        }/*}}}*/
        public function regSensitive4Add($groupkey,$cachekey)
        {/*{{{*/
            $cacheKeys = $this->keysMap->get($groupkey);
            if($cacheKeys == null) $cacheKeys =array();
            if(!in_array($cachekey,$cacheKeys))
            {
                $cacheKeys[]=$cachekey;
                $this->keysMap->set($groupkey,$cacheKeys);
            }
        }/*}}}*/
        public function invalidate($item,$cache)
        {/*{{{*/
            $id = $item;
            if(is_object($item)) $id= $item->id();
            if(is_array($item))  $id= $item['id'];
            $cacheKeys = $this->keysMap->get($id);
            if($cacheKeys)
            {
                foreach($cacheKeys as $key)
                {
                    $cache->delete($key) ;
                }
            }
            $this->keysMap->delete($id);
        }/*}}}*/
        public function invalidate4Add($groupkey,$cache)
        {/*{{{*/
            $cacheKeys = $this->keysMap->get($groupkey);
            if($cacheKeys)
            {
                foreach($cacheKeys as $key)
                {
                    //$cls = get_class($cache);
                    $cache->delete($key) ;

                }
            }
            $this->keysMap->delete($groupkey);
        }/*}}}*/
    }/*}}}*/
class CacheObserver extends CacheObserverBase
{/*{{{*/
    private $writeNames;
    public function __construct($keysMap)
    {/*{{{*/
        parent::__construct($keysMap);
        $this->writeNames= array("add","set","update","del"); 
    }/*}}}*/
    public function isNotCare($name)
    {/*{{{*/
        return strtolower($name) == 'delbyprop';
    }/*}}}*/
    public function isSensitive4Add($name)
    {/*{{{*/
        return strstr($name,'catch4add_')!=false;
    }/*}}}*/
    public function getSensitiveName($name)
    {/*{{{*/
        $name=str_replace('catch4add_','',$name);
        return $name;
    }/*}}}*/
    public function isWriteCall($name)
    {/*{{{*/
        foreach($this->writeNames as $prefix)
        {
            if(strncmp($prefix,$name,strlen($prefix)) ==0 )
                return true;
        }
        return false;
    }/*}}}*/
    public function isAddCall($name)
    {/*{{{*/
        return strtolower($name) === 'add';
    }/*}}}*/
}/*}}}*/
class RunSpaceSvcProxy
{/*{{{*/
    public $space;
    public $svc;
    public function __construct($space,$svc)
    {/*{{{*/
        $this->space=$space;
        $this->svc=$svc;
    }/*}}}*/
    public function __call($name,$params)
    {/*{{{*/
        $s = ScopeSwapSpace::change($this->space);
        $r=call_user_func_array(array($this->svc,$name),$params);
        $s = null;
        return $r;
    }/*}}}*/
    static public function create($space,$svc)
    {/*{{{*/
        return new RunSpaceSvcProxy($space,$svc);
    }/*}}}*/
}/*}}}*/

/** 
 *  @}
 */
?>
