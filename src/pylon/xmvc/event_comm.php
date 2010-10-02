<?php
///defgroup emc  EventMachine
/** 
 * @brief 
 * @ingrop emc
 */
interface EMRequest
{/*{{{*/

    public function getGetVars();
    public function getPostVars();
    public function getURI();
}/*}}}*/
/** 
 * @brief  
 * @ingrop emc
 */
interface EMStore
{/*{{{*/
    public function get($name);
    public function save($name,$val);
}/*}}}*/

class LifeScope extends PropertyObj
{/*{{{*/
    const SCOPE_GLOBAL  = 1;
    const SCOPE_URI    = 2;
    const SCOPE_SELF   = 3;
    const SCOPE_NOKEEP = 4;
    const GLOBAL_VARS_KEY = '__G_VARS';
    private $storeSpace=null;
    public  $maxAutoStore= 10;
    public function __construct($storeSpace)
    {/*{{{*/
        DBC::requireNotNull($storeSpace);
        $this->globalVars = array();
        $this->selfDefVars=array();
        $this->selfDefTag = "__NULL";
        $this->nokeepVars = array();
        $this->autoDefTag = null;
        $this->changeAutoStoreSpace=true;
        $this->selfdefSpace=null;
        $this->storeSpace  = $storeSpace;
    }/*}}}*/
    /** 
     * @brief 
     * 
     * @param $argNames  ag:"name,age,year"
     * 
     * @return 
     */

    protected function  getNameArray($name)
    {/*{{{*/
        $name = "__life__$name";
        $stored = $this->storeSpace->get($name);
        return $stored ? $stored :array();
//        $storedVars=array();
//        if($stored !=null)
//        {
//            $storedVars = unserialize($stored);
//        }
//        return $storedVars;
    }/*}}}*/
    protected function  keepNameArray($name,$arr)
    {/*{{{*/
        $name = "__life__$name";
        $stored= $this->getNameArray($name);
        $needKeep= array_merge($stored,$arr);
        $this->storeSpace->save($name,$needKeep);
        
    }/*}}}*/
    public function signGlobalVars($argNames)
    {/*{{{*/
        DBC::requireNotNull($argNames,'$argNames');
        $newSpace = array();
        if(is_string($argNames))
            $newGlobal = split(',',$argNames);
        if(is_array($argNames))
            $newGlobal = $argNames;
        $this->keepNameArray("Global",$newGlobal);
    }/*}}}*/
    public function getGloablVars()
    {/*{{{*/
        return $this->getNameArray("Global");
    }/*}}}*/
    /** 
     * @brief 
     * 
     * @param $defTag 
     * @param $argsNames   ag: "name,age,year";
     * 
     * @return void
     */
    public function signSelfDefVars($defTag,$argsNames)
    {/*{{{*/
        DBC::requireNotNull($argsNames,'$argsNames');
        DBC::requireNotNull($defTag,'$defTag');
        $newArr = array();
        if(is_string($argsNames))
            $newArr = split(',',$argsNames);
        if(is_array($argsNames))
            $newArr= $argsNames;
        
        $this->keepNameArray($defTag,$newArr);
    }/*}}}*/
    public function getSelfDefVars($defTag)
    {
        return $this->getNameArray($defTag);
    }
    /** 
        * @brief  sign not keep vars
        * 
        * @param $argNames 
        * 
        * @return void  
     */
    public function signNoKeepVars($argNames)
    {/*{{{*/
        DBC::requireNotNull($argNames,'$argNames');
        $newArr = array();
        if(is_string($argNames))
        {
            $newArr = split(',',$argNames);
        }
        if(is_array($argNames))
            $newArr= $argNames;
        $this->nokeepVars= array_merge($this->nokeepVars,$newArr );
    }/*}}}*/
    public function getNoKeepVars()
    {
        return $this->nokeepVars;
    }
    public function autoStoreSpace()
    {
        return  $this->storeSpace->get('__pAutoStoreSpace');
    }
    public function setAutoStoreSpace($name)
    {/*{{{*/
        $this->storeSpace->save('__pAutoStoreSpace',$name);
    }/*}}}*/
    public function swapAutoStoreSpace($newSpace)
    {/*{{{*/
        $old = $this->storeSpace->get('__pAutoStoreSpace');
        $this->storeSpace->save('__pAutoStoreSpace',$newSpace);
        if($old == $newSpace || $old==null ) 
            $this->changeAutoStoreSpace=false;
        return $old;
    }/*}}}*/
    public function isChangeAutoStoreSpace()
    {
        return $this->changeAutoStoreSpace;
    }
    public function setSelfDefSpace($space)
    {
        $this->selfdefSpace = $space;
    }
    public function getSelfdefSpace()
    {
        return $this->selfdefSpace;
    }
}/*}}}*/

interface ScopeListener
{/*{{{*/
    public function setup($screenSpec, $request,$parms1,$parms2,$parms3);
    public function tearDown($screenSpec, $request,$parms1,$parms2,$parms3);
}/*}}}*/
?>
