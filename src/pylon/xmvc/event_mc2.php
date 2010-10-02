<?php


interface IRequest
{/*{{{*/
   public function getVars();
}/*}}}*/

class LRUCaceh
{/*{{{*/
    public $counts=array();
    public $datas=array();
    public function __construct($max)
    {/*{{{*/
        $this->max = $max; }/*}}}*/
    public function cache($key,$val)
    {/*{{{*/
        $this->datas[$key] = $val;
        $this->counts[$key]  += $this->max;
        $min =99999;
        $minkey = null;
        foreach ($this->counts as $key => $cnt)
        {
            $this->counts[$key] -= 1;
            if($min > $cnt)
            {
                $min = $cnt;
                $minkey = $key;
            }
        }
        if(count($this->datas) > $this->max)
        {
            unset($this->counts[$minkey]);
            unset($this->datas[$minkey]);
        }
    }/*}}}*/
    public function get($key)
    {/*{{{*/
        return isset($this->datas[$key])? $this->datas[$key]:null;
    }/*}}}*/
}/*}}}*/
class EventsDefine
{/*{{{*/
    public $events;
    public $defaultEvent=null;
    public $defaultEventFun=null;
    static $funPrefix=null;
    public function __construct($eventActions)
    {/*{{{*/
        $this->events = $eventActions;
    }/*}}}*/

    static public function make($eventStr,$funPrefix=null)
    {/*{{{*/
        self::$funPrefix = $funPrefix;
        $events = array();
        $evnetNames = split(',',$eventStr);
        foreach($evnetNames as $name)
        {
            if(!empty($name))
            {
                $events[$name]= $funPrefix.$name; 
                $events[$name."_x"]= $funPrefix.$name;  //for IE image submit;
            }
        }
        return new EventsDefine($events);
    }/*}}}*/
    public function setDefaultEvent($name)
    {/*{{{*/
        $this->defaultEvent= $name; 
        $this->defaultEventFun= self::$funPrefix.$name; 
    }/*}}}*/

    public function  getEvents()
    {/*{{{*/
        return $this->events;
    }/*}}}*/
    public function getAllElements()
    {/*{{{*/
        $elements = array();
        foreach($this->areaSpecs  as $spec )
        {
            $elements = array_merge($elements, $spec->elementArray);   
        }
        return $elements;
    }/*}}}*/

}/*}}}*/
/** 
 * @brief 
 */
class ActionEventConf extends PropertyObj
{/*{{{*/
    public function __construct()
    {/*{{{*/
    }/*}}}*/

}/*}}}*/

/** 
 * @brief 
 * @example eventmc_tc.php
 */
class EventMachine2
{/*{{{*/
    const URI=1;
    const GETVARS=2;
    const POSTVARS=3;
    private $storeSpace;
    private $args;
    private $getReqDataFun;
    private $scopeListener=null;
    public  $isDebug=false;
    private $allSpaceData=null;
    private $maxStorePoint=5;
    private $needKeepData = true;
    public function __construct($storeSpace)
    {/*{{{*/
        DBC::requireNotNull($storeSpace,'$storeSpace');
        $this->storeSpace=$storeSpace;
    }/*}}}*/
    public function regScopeListener($listener)
    {/*{{{*/
        $this->scopeListener = $listener;
    }/*}}}*/

    private function getScopeStoreVar($reqkey,$storeSpace,$varsLifeConf)
    {/*{{{*/
        $this->allSpaceData = $storeSpace->get('__all_space_data');
        $aSpaceData   =  isset($this->allSpaceData['auto'])? $this->allSpaceData['auto'] : array();
        $cSpaceData = array();
        if(isset($this->allSpaceData['cache']))
        {
            $cache = $this->allSpaceData['cache'];
            $cSpaceData = $cache->get($reqkey);
            if($cSpaceData == null) $cSpaceData = array();
        }
        $gSpaceData   =  isset($this->allSpaceData['global'])?  $this->allSpaceData['global'] : array();
        $sSpaceName = $varsLifeConf->getSelfdefSpace();
        $sSpaceData = isset($this->allSpaceData[$sSpaceName])?  $this->allSpaceData[$sSpaceName] : array();

        if(!$varsLifeConf->isChangeAutoStoreSpace())
        {
            $rSpaceData = $aSpaceData;
        }
        else
        {
            $rSpaceData = array_merge($cSpaceData,$gSpaceData,$sSpaceData);
        }

        $nokeeps = $varsLifeConf->getNoKeepVars();
        foreach($nokeeps as $item)
        {
            $unsetVar = strtolower($item);
            unset($rSpaceData[$unsetVar]);
        }
        return $rSpaceData;
    }/*}}}*/
    public function fetchData($reqkey,$request,$storeSpace,$varsLifeConf)
    {/*{{{*/
        $restoreName=null;
        $failurl=null;
        if($this->needRestore($storeSpace,$restoreName,$failurl))
        {/*{{{*/
            if($restoreName != null)
            {
                $this->args = $this->getRestoreData($storeSpace,$restoreName);
//                $this->setHaveRestore($storeSpace);
                return ;
            }
            else
            {
                header("Location: http://$failurl");
            }
        }/*}}}*/
        $keeps = $this->getScopeStoreVar($reqkey,$storeSpace,$varsLifeConf);
        $getVars= $request->getVars();
        $datas = array_merge($keeps,$getVars);
        $this->args = PropertyObj::create($datas);
    }/*}}}*/
    private function getRestoreData($storeSpace,$name)
    {/*{{{*/
        $points = $storeSpace->get("store_point");
        $data=$points->get($name);
        return $data['vars'];
    }/*}}}*/
    private function needRestore($storeSpace,&$name,&$failurl)
    {/*{{{*/
        $failurl   = $storeSpace->get('__restore_failurl');
        $need      = $storeSpace->get('__need_restore');
        $name      = $storeSpace->get('__restore_name');
        $storeSpace->save('__need_restore',null);
        return     $need? true: false;
    }/*}}}*/
    private function setHaveRestore($storeSpace)
    {/*{{{*/
        $storeSpace->save('__need_restore',null);
    }/*}}}*/

    static public function key_compare_func($key1,$key2)
    {/*{{{*/
        $lowkey1 = strtolower($key1);
        $lowkey2 = strtolower($key2);
        if ($lowkey1== $lowkey2)
            return 0;
        else if ($lowkey1> $lowkey2)
            return 1;
        else
            return -1;
    }/*}}}*/

    public function notKeepData()
    {
        $this->needKeepData =false; 
    }
    public function keepData($reqkey,$storeSpace,$events,$varsLifeConf)
    {/*{{{*/
        $allArgs = $this->args->getPropArray();
        $gSpaceData   =  isset($this->allSpaceData['global'])?   $this->allSpaceData['global'] : array();
        $sSpaceName   = $varsLifeConf->getSelfdefSpace();
        $sSpaceData   =  isset($this->allSpaceData[$sSpaceName])?  $this->allSpaceData[$sSpaceName] : array();

        $gSpaceKeys   = $varsLifeConf->getGloablVars();
        $sSpaceKeys   = $varsLifeConf->getSelfDefVars($sSpaceName);
        foreach ($gSpaceKeys as $key)
        {
            if(isset($allArgs[$key]))
                $gSpaceData[$key] = $allArgs[$key];
        }
        foreach ($sSpaceKeys as $key)
        {
            if(isset($allArgs[$key]))
                $sSpaceData[$key] = $allArgs[$key];
        }
        $this->allSpaceData['global']       = $gSpaceData;
        $this->allSpaceData[$sSpaceName]    = $sSpaceData;
        $cache = isset($this->allSpaceData["cache"]) ?  $this->allSpaceData['cache'] :  
            new LRUCaceh($varsLifeConf->maxAutoStore);
        if($this->needKeepData)
        {
            $this->allSpaceData['auto']             = $allArgs;
            $cache->cache($reqkey,$allArgs);
        }
        else
        {
            $this->allSpaceData['auto']    = array();            
            $cache->cache($reqkey,array());
        }
        $this->allSpaceData["cache"]  = $cache  ;

        $storeSpace->save('__all_space_data',$this->allSpaceData);
    }/*}}}*/

    public function restore($storeSpace,$name,$failurl)
    {/*{{{*/
        $storeSpace->save('__need_restore',true);
        $storeSpace->save('__restore_name',$name);
        $storeSpace->save('__restore_failurl',$failurl);
        $points = $storeSpace->get("store_point");
        $data= $points->get($name);
        if($data != null)
            return $data['url'];
        return null;
    }/*}}}*/
    public function restore2LastPoint($storeSpace,$failurl)
    {/*{{{*/
        $lastpoint= self::lastPoint($storeSpace);
        return $this->restore($storeSpace,$lastpoint,$failurl);
    }/*}}}*/
    public function lastPoint($storeSpace)
    {/*{{{*/
        return  $storeSpace->get("__last_point");
    }/*}}}*/
    public function setReStorePoint($storeSpace,$name,$desc="")
    {/*{{{*/
        $data['vars'] = $this->getCurVars();
        $storeSpace->save("__last_point",$name);
        $points = $storeSpace->get("store_point");
        if( empty($points))
            $points  = new LRUCaceh($this->maxStorePoint);
        $data['url'] = $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        $data['desc'] = $desc;
        $points->cache($name,$data);
        $storeSpace->save("store_point",$points);
    }/*}}}*/
    public function setMaxPoints($storeSpace,$max)
    {/*{{{*/
        $this->maxStorePoint =$max;
    }/*}}}*/
    public function listRestorePoints($storeSpace)
    {/*{{{*/
        $points = $storeSpace->get("store_point");
        $out=array();
        if(!empty($points))
        {
            foreach($points->datas as $key=>$data)
            {
                $out[$key]=$data['desc'];
            }
        }
        return $out;
        
    }/*}}}*/

    public function getCurVars()
    {/*{{{*/
        return $this->args;
    }/*}}}*/

    /** 
     * @brief 
     * 
     * @return PropertyObj
     */
    public function triggerEvent($name)
    {/*{{{*/
        if($this->args->haveSet($name) )
        {
            $val = $this->args->$name;
            return !empty($val);
        }
        return false;
    }/*}}}*/
    /** 
        * @brief 
        * 
        * @param $eventDef 
        * @param $varsLifeConf 
        * @param $funOwner 
        * @param $request 
        * @param $parms1 
        * @param $parms2 
        * @param $parms3 
        * 
        * @return 
     */
    public function dispach($reqkey,$eventDef,$varsLifeConf,$funOwner,$request,$xcontext,
        $parms1=null,$parms2=null,$parms3=null)
    {/*{{{*/
        $dc = DiagnoseContext::create(__METHOD__);
        $varsLifeConf->signNoKeepVars(array_keys($eventDef->getEvents()));
        $this->fetchData($reqkey,$request,$this->storeSpace,$varsLifeConf);
        $vars=$this->getCurVars();
        $events= $eventDef->getEvents();
        try
        {
            $eventCall= $eventDef->defaultEventFun;
            $retval=null;
            foreach($events as $name => $fun) 
            {/*{{{*/
                if($this->triggerEvent($name))
                {  
                    $dc->log("trigger event: $name");
                    $eventCall = $fun;
                    break;
                }
            }/*}}}*/
            $xcontext->_event_call=$eventCall;
            $dc->log("trigger event fun: $eventCall");
            $xcontext->_vars=$vars;

            if($this->scopeListener)
                $this->scopeListener->setup($vars,$xcontext,$parms1,$parms2,$parms3);
            $retval = call_user_func(array(&$funOwner,$eventCall),$vars, $xcontext,$parms1,$parms2,$parms3);
            if($this->scopeListener)
                $this->scopeListener->tearDown($vars, $xcontext,$parms1,$parms2,$parms3);
            $dc->notkeep();
        }
        catch(Exception $e)
        {
            $this->keepData($reqkey,$this->storeSpace,$events,$varsLifeConf);
            throw $e;
        }
        $this->keepData($reqkey,$this->storeSpace,$events,$varsLifeConf);
        return $retval;

    }/*}}}*/
}/*}}}*/

?>
