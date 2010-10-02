<?php

/**\addtogroup domin_mdl
 * @{
 */
interface AutoUpdate
{/*{{{*/
    public function index();
    public function buildSummery();
}/*}}}*/

class  EntityID extends PropertyObj
{/*{{{*/
    protected function __construct($id,$ver,$cTime,$uTime)
    {/*{{{*/
        $this->id=$id;
        $this->ver=$ver;
        $this->createTime=$cTime;
        $this->updateTime=$uTime;
    }/*}}}*/
    static public function create($idname='other')
    {/*{{{*/
        $t = date("Y-m-d H:i:s",time()); 
        $id= EntityUtls::createPureID($idname);
        return new EntityID($id,1,$t,$t);
    }/*}}}*/
    public static function load(&$array)
    {/*{{{*/
        $id = new EntityID(intval($array['id']),intval($array['ver']),$array['createtime'],$array['updatetime']);
        unset($array['id']);
        unset($array['ver']);
        unset($array['createtime']);
        unset($array['updatetime']);
        return $id;
    }/*}}}*/
    public function upgrade()
    {/*{{{*/
        $this->updateTime = date("Y-m-d H:i:s",time());
        $this->ver += 1;
    }/*}}}*/
}/*}}}*/
class Entity extends PropertyObj implements AutoUpdate
{/*{{{*/
    const LAZY_LOADER=1;
    const IMMED_LOADER=2 ;
    static $s_unitwork=null;
    protected function __construct($entityID,$prop=null)
    {/*{{{*/
        parent::__construct();
        $this->entityID=$entityID;
        if($prop != null)
            $this->merge($prop);
        DBC::requireNotNull($this->entityID,"not entity id" );
    }/*}}}*/
    public function upgrade()
    {/*{{{*/
        $this->entityID->upgrade();
    }/*}}}*/

    public function id()
    {/*{{{*/
        return $this->entityID->id;
    }/*}}}*/

    public function index()
    {/*{{{*/
        return get_class($this).'_'.$this->id();
    }/*}}}*/
    public function ver()
    {/*{{{*/
        return $this->entityID->ver;
    }/*}}}*/

    /** 
        * @brief  hash store need,override  this fun in subclass;
        * 
        * @return  string key; default is null;
     */
    public function hashStoreKey()
    {/*{{{*/
        return null;
    }/*}}}*/

    public function createTime()
    {/*{{{*/
        return $this->entityID->createTime;
    }/*}}}*/
    public function updateTime()
    {/*{{{*/
        return $this->entityID->updateTime;
    }/*}}}*/
    public function getDTO($mappingStg)
    {/*{{{*/
        $vars = $this->getPropArray();
        return  $mappingStg->convertDTO($vars);
    }/*}}}*/
    public function getRelationSets()
    {/*{{{*/
        return array();
    }/*}}}*/
    public function buildSummery()
    {/*{{{*/
        $sets = $this->getRelationSets();
        $key=serialize($this->getDTO(StdMapping::ins()));
        $setskey="";
        foreach($sets as $set)
        {
            $setskey  .= $set->haveChange();
        }
        return md5($key.$setskey);
    }/*}}}*/
    static public function unitWork($unitwork=null)
    {/*{{{*/
        if($unitwork !=null)
            self::$s_unitwork =$unitwork;
        DBC::requireNotNull(self::$s_unitwork,'unitwork maybe not register by EntityUtls::assembly()! $s_unitwork ');
        return self::$s_unitwork;
    }/*}}}*/
    static public function cleanUnitWork()
    {/*{{{*/
        self::$s_unitwork = null;
    }/*}}}*/
    static public function createByBiz($entity)
    {/*{{{*/
        DBC::requireNotNull($entity);
        $obj = self::unitWork()->regAdd($entity);
        return $obj;
    }/*}}}*/
    public function __wakeup()
    {/*{{{*/
        if(self::$s_unitwork)
            self::unitWork()->regLoad($this);
    }/*}}}*/
    static public function loadEntity($cls,$array,$mappingStg,$clsmap=array())
    {/*{{{*/
        $entityID = EntityID::load($array);
        $prop=$mappingStg->buildEntityProp($array);
        $entity = new $cls($entityID,$prop);
        $obj = self::unitWork()->regLoad($entity);
        return $obj;
    }/*}}}*/
    static public function loadEntity2($cls,$array,$oprop,$mappingStg,$clsName=array())
    {/*{{{*/
        $entityID = EntityID::load($array);
        $prop=$mappingStg->buildEntityProp($array);
        $prop->merge($oprop);
        $entity = new $cls($entityID,$prop);
        $obj = self::unitWork()->regLoad($entity);
        return $obj;
    }/*}}}*/
    public function signAdd()
    {/*{{{*/
        self::unitWork()->regAdd($this);
    }/*}}}*/
    public function signLoad()
    {/*{{{*/
         self::unitWork()->regLoad($this);
    }/*}}}*/
    public function del()
    {/*{{{*/
        self::unitWork()->regDel($this); 
    }/*}}}*/
}/*}}}*/

class NullEntity extends Entity
{/*{{{*/
    private $className;
    public function __construct($clsName)
    {
        $this->className=$clsName;
    }
    public function getClass()
    {
        return $this->className;
    }
    public function __call($name,$params)
    {
       throw new LogicException("Call [$name] failed! This is NullEnitity of ".$this->className); 
    }

}/*}}}*/

abstract 
    class Relation extends PropertyObj   implements AutoUpdate
    {/*{{{*/

        protected function __construct($prop=null)
        {/*{{{*/
            parent::__construct();
            if($prop != null)
                $this->merge($prop);
        }/*}}}*/
        public function id()
        {/*{{{*/
            return $this->id;
        }/*}}}*/
        public function getDTO($mappingStg)
        {/*{{{*/
            $vars = $this->getPropArray();
            return  $mappingStg->convertDTO($vars);
        }/*}}}*/
        public function getRelationSets()
        {/*{{{*/
            return array();
        }/*}}}*/
        public function buildSummery()
        {/*{{{*/
            return md5(serialize($this->getDTO(StdMapping::ins())));
        }/*}}}*/

        /** 
         * @brief  hash store need,override  this fun in subclass;
         * 
         * @return  string key; default is null;
         */
        public function hashStoreKey()
        {/*{{{*/
            return null;
        }/*}}}*/
        static public function  loadRelation($cls,$array,$mappingStg)
        {/*{{{*/
            $prop=$mappingStg->buildEntityProp($array);
            return new $cls($prop);
        }/*}}}*/
    }/*}}}*/


class ObjUpdater
{/*{{{*/
    protected $items=array();
    protected $additems=array(); 
    protected $delitems=array(); 
    protected $loaditems=array(); 
    protected $loaditemSummerys=array();
    const OBJ_LOAD=1;
    const OBJ_ADD=2;
    const OBJ_DEL=3;
    protected function __construct(&$items=array(),$objType)
    {/*{{{*/
        $array =null;
        if(ObjUpdater::OBJ_LOAD===$objType)
        {
            $array = & $this->loaditems;
        }
        elseif(ObjUpdater::OBJ_ADD===$objType)
        {
            $array= &$this->addItems;
        }
        elseif(ObjUpdater::OBJ_DEL===$objType)
        {
            $array= &$this->delitems;
        }
        else
        {
            DBC::unExpect($objType,"objType");
        }
        foreach($items as $item)
        {
            $array[$item->index()] = $item;
            $this->items[$item->index()]=$item;
            if(ObjUpdater::OBJ_LOAD===$objType)
                $this->loaditemSummerys[$item->index()]=$item->buildSummery();
        }

    }/*}}}*/
    public function cleanItem(&$items)
    {/*{{{*/
        $cnt = count($items);
        for($i=0 ; $i<$cnt; $i++)
            $items[$i] = null;
    }/*}}}*/
    public function __destruct()
    {/*{{{*/
        $this->clean();

    }/*}}}*/
    public function haveChange()
    {/*{{{*/
        if(count($this->additems)>0)  return true;
        if(count($this->delitems)>0)  return true;
        foreach($this->loaditems as $key=>$item)
        {
            if($item->buildSummery() != $this->loaditemSummerys[$key])
                return true;
        }
        return false;
    }/*}}}*/
    protected function commitUpdate($addfun,$delfun,$updatefun)
    {/*{{{*/
        foreach($this->additems as $item)
        {
            call_user_func($addfun,$item);
        }
        foreach($this->delitems as $item)
        {
            call_user_func($delfun,$item);
        }
        foreach($this->loaditems as $key=>$item)
        {
            if($item->buildSummery() != $this->loaditemSummerys[$key])
            {
                call_user_func($updatefun,$item);
            }
        }
    }/*}}}*/
    public function regLoad($obj)
    {/*{{{*/
        DBC::requireNotNull($obj);
        //消除副本的影响！
        if(isset($this->items[$obj->index()]))
        {
            //反回第一个对象。
            return  $this->items[$obj->index()];
        }
        $this->items[$obj->index()]=$obj;
        $this->loaditems[$obj->index()]=$obj;

        $this->loaditemSummerys[$obj->index()]=$obj->buildSummery();
        return $obj;
    }/*}}}*/
    public function regAdd($obj)
    {/*{{{*/

        DBC::requireNotNull($obj);
        //消除副本的影响！
        if(isset($this->items[$obj->index()]))
        {
            DBC::requireNotNull($this->items[$obj->index()]);
            //反回第一个对象。
            return  $this->items[$obj->index()];
        }
        $this->additems[$obj->index()]=$obj;
        $this->items[$obj->index()]=$obj;
        return $obj;
    }/*}}}*/
    public function regDel($obj)
    {/*{{{*/
        $this->regDelByIndex($obj->index());
    }/*}}}*/
    public function regDelByIndex($index)
    {/*{{{*/
        if(isset($this->additems[$index]))
        {
            unset($this->additems[$index]);
        }
        else
        {
            $this->delitems[$index]=$this->get($index);
            unset($this->loaditems[$index]);
        }
        unset($this->items[$index]);
    }/*}}}*/
    public function clean()
    {/*{{{*/
       
        $this->cleanItem($this->items);
        $this->items= array();
        $this->cleanItem($this->additems);
        $this->additems= array();
        $this->cleanItem($this->delitems);
        $this->delitems= array();
        $this->cleanItem($this->loaditems);
        $this->loaditems= array();
        $this->cleanItem($this->loaditemSummerys);
        $this->loaditemSummerys=array();

    }/*}}}*/
    public function get($index)
    {/*{{{*/
        DBC::requireTrue(isset($this->items[$index]),"not found index[$index] obj!");
        return $this->items[$index];
    }/*}}}*/
    public function getByObj($indexObj)
    {/*{{{*/
        return $this->get($indexObj->index());
    }/*}}}*/
    public function &items()
    {/*{{{*/
        return $this->items;
    }/*}}}*/
    public function  objcomp($a,$b)  
    {/*{{{*/
        if($a==$b) return 0; 
        return $a->id() > $b->id() ? 1 :-1;
    }/*}}}*/
    public function equal($other)
    {/*{{{*/
        DBC::requireNotNull($other);
        $cur=$this->items();
        $oth=$other->items();
        $diff = array_udiff_assoc($cur,$oth,array($this,'objcomp'));
        return count($diff)==0;
    }/*}}}*/
    public function regAll2Del()
    {/*{{{*/
        foreach($this->items as $i)
        {
            $this->regDel($i);
        }
    }/*}}}*/

}/*}}}*/

class ObjectSet  extends ObjUpdater 
{/*{{{*/
    protected $clsName;
    static public function load($clsName,$items)
    {/*{{{*/
        DBC::requireTrue(is_string($clsName));
        DBC::requireTrue(is_array($items));
        $obj =new ObjectSet($items,ObjUpdater::OBJ_LOAD);
        $obj->clsName=$clsName;
        return $obj;
    }/*}}}*/

    static public function createByBiz($clsName)
    {/*{{{*/
        DBC::requireTrue(is_string($clsName));
        $item=array();
        $obj=new ObjectSet($item,ObjUpdater::OBJ_ADD);
        $obj->clsName=$clsName;
        return $obj;
    }/*}}}*/
    public function getClsName()
    {/*{{{*/
        return $this->clsName;
    }/*}}}*/
    public function saveDateSet($dao)
    {/*{{{*/
        $this->commitUpdate(array($dao,'add'),array($dao,'del'),array($dao,'update'));
    }/*}}}*/
}/*}}}*/
interface MappingStg
{/*{{{*/
    public function convertDTO($vars);
    public function buildEntityProp(&$array,$argsmap=array());
}/*}}}*/
class SimpleMapping implements MappingStg
{/*{{{*/
    static private $ins=null;
    static public function ins()
    {/*{{{*/
        if(self::$ins == null)
           self::$ins = new SimpleMapping(); 
        return self::$ins;
    }/*}}}*/
    public function convertDTO($vars)
    {/*{{{*/
        $subdtos = array();
        $dtovars = array();
        foreach($vars as $key=>$val)
        {/*{{{*/
            if(is_object($val) && $val instanceof  NullEntity)
            {
                $dtovars[$key."__id"]= null;
            }
            elseif(is_object($val) && $val instanceof  Entity)
            {
                $dtovars[$key."__id"]= $val->id();
            }
            else if(is_object($val) && $val instanceof  EntityID)
            {
                $subdtos[] = PropertyObj::create($val->getPropArray());
            }
            else if (is_object($val) && $val instanceof LDProxy)
            {
                $dtovars[$key."__id"]= $val->id();
            }
            else if (is_object($val) && $val instanceof ObjectSet)
            {
            }
            else
                $dtovars[$key]=$val;
        }/*}}}*/
        $maindto = PropertyObj::create($dtovars); 
        $maindto->merges($subdtos);
        return $maindto;
    }/*}}}*/

    public function buildEntityProp(&$array,$argsmap=array())
    {/*{{{*/
        foreach ( $array as $col=>$val)
        {/*{{{*/
            if(isset($argsmap[$col]))
            {/*{{{*/
                $propName= $argsmap[$col];
                $array[$propName]=$array[$col];
                unset($array[$col]);

            }/*}}}*/
            elseif( strpos($col,'__id') != false)
            {/*{{{*/
                $prop = PropertyObj::create(); 
                $key= str_replace('__id','',$col);
                if(isset($array[$col]) && $array[$col]!=null)
                {
                    $prop->id= $array[$col];
                    $prop->cls=$key; 
                    $ctrl = PylonCtrl::objLazyLoad();
                    if($ctrl->need_do($key,null))
                    {
                        $obj  =new LDProxy(array("EntityUtls","loadObjByID"),$prop);
                    }
                    else
                    {
                        $obj   = EntityUtls::loadObjByID($prop);
                    }
                    $array[$key]  = $obj;
                    unset($array[$col]);
                }
                else
                {
                        $array[$key]= new NullEntity($key);
                }
            }/*}}}*/

        }/*}}}*/
        $prop = PropertyObj::create($array);
        return $prop;
    }/*}}}*/
}/*}}}*/
class StdMapping implements MappingStg
{/*{{{*/
    static private $ins=null;
    static public function ins()
    {/*{{{*/
        if(self::$ins == null)
           self::$ins = new StdMapping(); 
        return self::$ins;
    }/*}}}*/
    public function convertDTO($vars)
    {/*{{{*/
        $subdtos = array();
        $dtovars = array();
        foreach($vars as $key=>$val)
        {/*{{{*/
            if(is_object($val) && $val instanceof  NullEntity)
            {
                $dtovars[$key."__".strtolower($val->getClass())]=  null;
            }
            elseif(is_object($val) && $val instanceof  Entity)
            {
                $dtovars[$key."__".strtolower(get_class($val))]= $val->id();
            }
            else if(is_object($val) && $val instanceof  EntityID)
            {
                $subdtos[] = PropertyObj::create($val->getPropArray());
            }
            else if (is_object($val) && $val instanceof LDProxy)
            {
                $dtovars[$key."__".strtolower(get_class($val->getObj()))]= $val->id();
            }
            else if (is_object($val) && $val instanceof ObjectSet)
            {
            }
            else
                $dtovars[$key]=$val;
        }/*}}}*/
        $maindto = PropertyObj::create($dtovars); 
        $maindto->merges($subdtos);
        return $maindto;
    }/*}}}*/
    public function buildEntityProp(&$array,$argsmap=array())
    {/*{{{*/
        foreach ( $array as $col=>$val)
        {
            if(isset($argsmap[$col]))
            {
                $propName= $argsmap[$col];
                $array[$propName]=$array[$col];
                unset($array[$col]);

            }
            elseif( strpos($col,'__') != false)
            {
                $prop = PropertyObj::create(); 
                list($key,$cls) = explode('__', $col);
                if(isset($array[$col]) && $array[$col]!=null)
                {
                    $prop->id= $array[$col];
                    $prop->cls=$cls; 

                    $ctrl = PylonCtrl::objLazyLoad();
                    if($ctrl->need_do($key,null))
                    {
                        $obj  =new LDProxy(array("EntityUtls","loadObjByID"),$prop);
                    }
                    else
                    {
                        $obj   = EntityUtls::loadObjByID($prop);
                    }
                    $array[$key]=$obj;
                    unset($array[$col]);
                }
            }
            else
            {
                $array[$key]=new NullEntity($key);
            }

        }
        $prop = PropertyObj::create($array);
        return $prop;
    }/*}}}*/
}/*}}}*/
interface Dao
{/*{{{*/
    public function getByID($id);
    public function update($obj);
    public function add($obj);
    public function del($obj);
    public function row2obj($cls,$row);
    public function obj2row($obj);
}/*}}}*/

class EntityUtls
{/*{{{*/
    static public function loadObjByID($prop)
    {/*{{{*/
        return DaoFinder::find($prop->cls)->getByID($prop->id);
    }/*}}}*/

    static public function loadRelation($cls,$array,$loadstg,$clsmap=array(),$argsmap=array())
    {/*{{{*/

        $reflectionObj = new ReflectionClass($cls);
        $constructFun = $reflectionObj->getConstructor();
        $args = $constructFun->getParameters();
        $constrctArgs=array();
        foreach ( $args as $arg)
        {/*{{{*/
            $key=strtolower($arg->getName());
            $col=$key;
            if(isset($argsmap[$key]))
                $col=$argsmap[$key];
            if(isset($array[$col]))
            {
                $constrctArgs[$key]=$array[$col] ;
            }
            elseif( isset($array[$col."__id"] ))
            {
                $prop = PropertyObj::create(); 

                $prop->id= $array[$col."__id"];
                $prop->cls= $clsmap[$col];
                $obj = new LDProxy(array("EntityUtls","loadObjByID"),$prop);
                if($loadstg == Entity::LAZY_LOADER)
                    $constrctArgs[$key]=$obj;
                else
                    $constrctArgs[$key]=$obj->getObj();
            }
            else
            {
                $msg = Prompt::recommend($key,array_keys($array));
                $msg = JoinUtls::jarray(',',$msg);
                DBC::unExpect("$key not unexpect!  col is $col,<br>\n key mabey is [ $msg ] <br>\n");
            }
        }/*}}}*/
        $obj= $reflectionObj->newInstanceArgs($constrctArgs);
        return $obj;
    }/*}}}*/

    static public function assembly($unitwork)
    {/*{{{*/
        DBC::requireNotNull($unitwork);
        Entity::unitWork($unitwork);
    }/*}}}*/

    static public function createPureID($idname='other')
    {/*{{{*/
        $idSvc = ObjectFinder::find('IDGenterService');
        $id= $idSvc->createID($idname);
        return $id;
    }/*}}}*/
}/*}}}*/

class DaoFinderImpl
{ /*{{{*/
    const factory='##factory';
    private $daoList=null; 
    private $queryList=null; 
    private $exerList=array();
    public function __construct() 
    {/*{{{*/
        $this->daoList   =  new RunSpaceContainer();
        $this->queryList =  new RunSpaceContainer();
    }/*}}}*/

    static protected function showListSummary($list)
    {/*{{{*/
        foreach($list as $key =>$val)
        {
            echo "[$key] \n";
        }
    }/*}}}*/

    protected function findByCls($clsName)
    {/*{{{*/
        $stdName= strtolower($clsName);
        $daos = $this->daoList->getObjs();
        foreach($daos as $key => $val)
        {
//            if(self::is_a($clsName,$key))
            if($stdName == strtolower($key))
            {
                DBC::requireNotNull($val);
                return $val;
            }
        }
        
        if($this->daoList->haveObj(DaoFinderImpl::factory))
        {
            $dao = call_user_func($this->daoList->getObj(DaoFinderImpl::factory),$clsName); 
            $this->register($dao);
            return $dao;
        }
        $names = Prompt::recommend($stdName,array_keys($this->daoList->getObjs()));
        $str   = JoinUtls::jarray(',',$names);
        DBC::unExpect($stdName,"dao not find!, it maybe one of list [$str]");
    }/*}}}*/

    public function getExecuterList()
    {/*{{{*/
        return $this->exerList;
    }/*}}}*/

    public function registerFactory($daoFactory,$queryFactory)
    {/*{{{*/
        $this->daoList->setObj(DaoFinderImpl::factory,$daoFactory);
        $this->queryList->setObj(DaoFinderImpl::factory,$queryFactory);
    }/*}}}*/
    static protected function is_a($firstCls,$secondCls) 
    {/*{{{*/
        if(strcasecmp($firstCls,$secondCls) == 0 ) return true;
        $parentCls = get_parent_class($firstCls);
        if($parentCls != null)
            return self::is_a($parentCls,$secondCls);
        return false;
    }/*}}}*/

    public function query($clsName)
    {/*{{{*/
        $stdName= strtolower($clsName);
        $querys= $this->queryList->getObjs();
        foreach($querys as $key => $val)
        {
            if($key == $stdName)
            {
                DBC::requireNotNull($val);
                return $val;
            }
        }
        if($this->queryList->haveObj(DaoFinderImpl::factory))
        {
            $query = call_user_func($this->queryList->getObj(DaoFinderImpl::factory),$clsName); 
            $this->registerQuery($query);
            return $query;
        }

        $names = Prompt::recommend($stdName,array_keys($this->queryList->getObjs()));
        $str   = JoinUtls::jarray(',',$names);
        DBC::unExpect($stdName,"query not find!, it maybe one of list [$str]");
    }/*}}}*/

    public function find($obj)
    {/*{{{*/
        $dao=null;
        if(is_object($obj))
            $obj = get_class($obj);
         $dao = $this->findByCls($obj);
        return $dao;
    }/*}}}*/

    public function listDaoSummary()
    {/*{{{*/
        return self::showListSummary($this->_daoList);
    }/*}}}*/

    private function registerExer($exer)
    {/*{{{*/
        $found =false;
        foreach($this->exerList  as $i)
        {
            if ($i === $exer)
                $found = true;
        }
        if(!$found)
            $this->exerList[] = $exer;
    } /*}}}*/
    public function register($dao)
    {/*{{{*/

        $this->registerExer($dao->getExecuter());
        $clsName = strtolower($dao->cls);
        DBC::requireNotNull($clsName,"cls name ");
        if($this->daoList->haveObj($clsName))
            DBC::unExpect($clsName," $clsName dao have register");
        $this->daoList->setObj($clsName,$dao);
    }/*}}}*/

    public function registerQuery($query)
    {/*{{{*/
        $this->registerExer($query->getExecuter());
        $clsName = strtolower($query->getRegName());
        if($this->queryList->haveObj($clsName)) 
            DBC::unExpect($clsName," $clsName query have register");
        $this->queryList->setObj($clsName,$query);
    }/*}}}*/

    public function registerAll($dao,$query)
    {/*{{{*/
        if($dao !=null)
            $this->register($dao);
        if($query !=null)
            $this->registerQuery($query);
    }/*}}}*/
    public function clean()
    {/*{{{*/
        $this->daoList->cleanObjs();
        $this->queryList->cleanObjs();
    }/*}}}*/

}/*}}}*/

class DaoFinder
{/*{{{*/
    private static $impl=null;
    private static $binder=null;

    static public function regBinder($binder)
    {/*{{{*/
        self::$binder = $binder;
    }/*}}}*/
    static public function clearBinder()
    {/*{{{*/
        self::regBinder(null);
    }/*}}}*/
    static public function ins()
    {/*{{{*/
        if(self::$impl ==null) 
            self::$impl= new DaoFinderImpl();
        return self::$impl;
    }/*}}}*/
    static public function swapIns($ins)
    {/*{{{*/
        $old=self::$impl;
        self::$impl=$ins;
        return  $old;
    }/*}}}*/
    static public function find($obj)
    {/*{{{*/
        $dao = self::ins()->find($obj);
        if(self::$binder!= null)
        {
            return self::$binder->proxy(
                is_string($obj)? $obj: get_class($obj),
                $dao);
        }
        return $dao;
    }/*}}}*/

    static public function registerFactory($daoFactory,$queryFactory)
    {/*{{{*/
        return self::ins()->registerFactory($daoFactory,$queryFactory);
    }/*}}}*/

    static public function query($clsName)
    {/*{{{*/
        $query = self::ins()->query($clsName);
        if(self::$binder!= null)
        {
            return self::$binder->proxy($clsName,$query);
        }
        return $query;
    }/*}}}*/

    static public function register($dao)
    {/*{{{*/
        return self::ins()->register($dao);
    }/*}}}*/
    static public function registerDaos()
    {/*{{{*/
        $daos = func_get_args();
        foreach($daos as $dao)
        {
            self::ins()->register($dao);
        }
    }/*}}}*/

    static public function registerQuerys()
    {/*{{{*/
        $querys = func_get_args();
        foreach($querys as $query)
        {
            self::ins()->registerQuery($query);
        }
    }/*}}}*/
    static public function registerQuery($query)
    {/*{{{*/
        return self::ins()->registerQuery($query);
    }/*}}}*/

    static public function registerAll($dao,$query)
    {/*{{{*/
        return self::ins()->registerAll($dao,$query);
    }/*}}}*/

    static public function getExecuterList()
    {/*{{{*/
        return self::ins()->getExecuterList();
    }/*}}}*/
    static public function clean()
    {/*{{{*/
        return self::ins()->clean();
    }/*}}}*/

}/*}}}*/

/** 
 *  @}
 */
?>
