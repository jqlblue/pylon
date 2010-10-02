<?php
class FilterProp extends PropertyObj 
{/*{{{*/
    static public function create($arr=array())
    {/*{{{*/
        if(empty($arr))
            return new FilterProp();
        $newarr=array();
        $keys = array_keys($arr);
        $vars = array_values($arr);
        array_walk($keys,array("PropertyObj","tolower"));
        array_walk($vars,array("FilterProp","todql"));
        $newarr = array_combine($keys,$vars);
        return new FilterProp($newarr);
    }/*}}}*/
    public function __set($name,$val)
    {/*{{{*/
        $name=strtolower($name);
        if($this->needCreatDQL($val))
            $val = new DQLObj($val,"?");
        $this->_attrs[$name]=$val;
    }/*}}}*/

    public function chname($old,$new)
    {/*{{{*/
        if($this->have($old))
            $this->_attrs[$new] = $this->_attrs[$old]; 
        unset($this->_attrs[$old]);
    }/*}}}*/
    public function chnameByArray($arr)
    {/*{{{*/
        foreach( $arr as $old=>$new)
        {
            $this->chname($old,$new);
        }
    }/*}}}*/
    public function toscopeVal($begin,$end,$what)                                                                                                                            
    {/*{{{*/
        if($this->have($begin) && $this->have($end))                                                                                                                         
        {                                                                                                                                                                    
            $begv = $this->$begin;                                                                                                                                           
            $endv = $this->$end;                                                                                                                                             
            if(is_string($begin) && is_string($end))                                                                                                                         
                $this->$what = " ? >= \"$begv\"  and  ? <= \"$endv\"" ;                                                                                                      
            else                                                                                                                                                             
                $this->$what = " ? >= $begv  and  ? <= $endv" ;                                                                                                              
        }                                                                                                                                                                    
        elseif($this->have($begin))                                                                                                                                          
        {                                                                                                                                                                    
            $begv = $this->$begin;                                                                                                                                           
            if(is_string($begin))                                                                                                                                            
                $this->$what = "? >= \"$begv\"";                                                                                                                             
            else                                                                                                                                                             
                $this->$what = "? >= \"$begv\"";                                                                                                                             
        }                                                                                                                                                                    
        elseif($this->have($end))                                                                                                                                            
        {                                                                                                                                                                    
            $endv = $this->$end;                                                                                                                                             
            if(is_string($end))                                                                                                                                              
                $this->$what = "? <= \"$endv\"";                                                                                                                             
            else                                                                                                                                                             
                $this->$what = "? <= \"$endv\"";                                                                                                                             
        }                                                                                                                                                                    
        unset($this->_attrs[$begin]);                                                                                                                                        
        unset($this->_attrs[$end]);   
    }/*}}}*/

    public function filterby()
    {/*{{{*/
        $names = func_get_args();
        foreach($names as $i)
        {
            unset($this->_attrs[$i]);
        }
    }/*}}}*/
    protected function todql(&$item,$key)
    {/*{{{*/
        if(self::needCreatDQL($item))
            $item = new DQLObj($item,"?");
    }/*}}}*/
    protected function needCreatDQL($val)
    {/*{{{*/
        if (is_string($val) && strstr($val,"?")) 
            return true;
        else
            return false;
    }/*}}}*/
}/*}}}*/

/**\addtogroup domin_mdl
 * @{
 */
class CondProp
{/*{{{*/
    static public function make($key,$value)
    {
        $prop = PropertyObj::create();
        $prop->$key=$value;
        return $prop;
    }
    static public function makeByObj($obj)
    {
        $prop = PropertyObj::create();
        $key= strtolower(get_class($obj)) . "__id";
        $prop->$key=$obj->id();
        return $prop;
    }
}/*}}}*/


class DynCallParser
{/*{{{*/
    static private function separatorOfBy($by)
    {/*{{{*/
        $seps['by'] = '/([a-z\d])_([a-z\d])/i';
        $seps['by2'] = '/([a-z\d])__([a-z\d])/i';
        $seps['by3'] = '/([a-z\d])___([a-z\d])/i';
        return $seps[$by];
    }/*}}}*/
    static public function condObjParse($callName)
    {/*{{{*/
        $matchs=array();
        if(strpos($callName,'by'))
        {
            preg_match('/(\S+)_(\S+)_(by\d?)_(\S+)/',$callName,$matchs);
            list($all,$op,$cls,$by, $condnames)=$matchs;
            $condnames = preg_replace(self::separatorOfBy($by),'$1#$2',$condnames);
            $result['condnames']=explode('#',$condnames);
        }
        else
        {
            preg_match('/(\S+)_(\S+)/',$callName,$matchs);
            list($all,$op,$cls)=$matchs;
            $result['condnames']=array();
        }

        $result['op']  = $op;
        $result['cls'] = $cls;
        return $result;
    }/*}}}*/
    static public function buildCondProp($condnames,$params,&$extraParams)
    {/*{{{*/
        $cnt = count($condnames);
        $extraParams = array_splice($params,$cnt);
        if($cnt == 0 )
            return FilterProp::create();
        
        $first = count($condnames);
        $second = count($params);
        if($first  != $second)
        {
            $names  = JoinUtls::jarray(",", $condnames);
            $values  = JoinUtls::jarray(",", $params);
            DBC::unExpect(null,"count of params name not match value! names is [ $names] value is [$values]");
        }
        $condArr = array_combine($condnames,$params);
        if(in_array('Prop',$condnames))
        {
            $userDefprop = $condArr['Prop'];
            unset($condArr['Prop']);
            $prop = FilterProp::create($condArr); 
            if(!empty($userDefprop))
                $prop->merge($userDefprop);
        }
        else
        {
            $prop = FilterProp::create($condArr); 
        }
        return $prop;
    }/*}}}*/

    static public function buildUpdateArray($updatenames,$condnames,$params)
    {/*{{{*/
        array_walk($params,create_function('&$item',
            ' if (is_string($item) && strstr($item,"?")) $item = new DQLObj($item,"?");' ));
        $ucnt = count($updatenames);
        $condParams = array_splice($params,$ucnt);
        $props['updateArray']= array_combine($updatenames,$params);
        if(count($condnames) >0)
        {
            $condArr = array_combine($condnames,$condParams);
            if(in_array('Prop',$condnames))
            {
                $userDefprop = $condArr['Prop'];
                $userDefArr = $userDefprop->getPropArray();
                unset($condArr['Prop']);
                $condArr = array_merge($condArr,$userDefArr);
            }
        }
        else
        {
            $condArray = array();
        }
        $props['condArray']= $condArr;
        return $props;
    }/*}}}*/

    static public function condUpdateObjParse($callName)
    {/*{{{*/
        $resut=array();
        $matchs=array();
        if(strpos($callName,'by'))
        {
            preg_match('/update_(\S+)_set_(\S+)_(by\d?)_(\S+)/',$callName,$matchs);
            list($all,$cls,$updates,$by, $condnames)=$matchs;
            $condnames = preg_replace(self::separatorOfBy($by),'$1#$2',$condnames);
            $result['condnames']=explode('#',$condnames);
        }
        else
        {
            preg_match('/update_(\S+)_set_(\S+)/',$callName,$matchs);
            list($all,$cls,$updates,)=$matchs;
            $by="";
            $result['condnames']=array();
            
        }
        $updateKeys = explode('_',$updates);
        $result['op']="set";
        $result['cls']=$cls;
        $result['by']=$by;
        $result['updatenames']=$updateKeys;
        return $result;

    }/*}}}*/
}/*}}}*/
/** 
 * @brief  datat direct accessor
 */


class DDA 
{/*{{{*/
    const OP_LIST=1;
    const OP_GET=2;
    const OP_ADD=3;
    const OP_DEL=4;
    const OP_UPDATE=5;
    private $loadStg=null;
    static public function ins()
    {/*{{{*/
        static $inst=null;
        if($inst == null)
            $inst = new DDA();
        return $inst;
    }/*}}}*/
    public function setLoadStg($stg)
    {/*{{{*/
        $this->loadStg = $stg;
    }/*}}}*/
    private function getDao($cls)
    {/*{{{*/
        $dao = DaoFinder::find($cls);
        if($this->loadStg != null) 
        {
            $dao->updateLoadStg($this->loadStg);
        }
        return $dao;
    }/*}}}*/
    public function listCall($op,$cls,$catch4add,$name,$paramNames,$params)
    {/*{{{*/
        $extraParams=null;
        $prop=DynCallParser::buildCondProp($paramNames,$params,$extraParams);
        if(count($extraParams) == 0 )
        {
            if($catch4add)
            {
                return  $this->getDao($cls)->catch4add_listByProp($prop);
            }
            else
            {
                return  $this->getDao($cls)->listByProp($prop);
            }
        }
        else
        {
            $page      = isset($extraParams[0])? $extraParams[0] : null;
            $orderkey  = isset($extraParams[1])? $extraParams[1] : null;
            $ordertype = isset($extraParams[2])? $extraParams[2] : 'DESC';
            if($catch4add)
            {
                return  $this->getDao($cls)->catch4add_listByProp($prop,$page,$orderkey,$ordertype);
            }
            else
            {

                return  $this->getDao($cls)->listByProp($prop,$page,$orderkey,$ordertype);
            }
        }
    }/*}}}*/
    public function getCall($op,$cls,$catch4add,$name,$paramNames,$params)
    {/*{{{*/
        $extraParams=null;
        $prop=DynCallParser::buildCondProp($paramNames,$params,$extraParams);

        if($catch4add)
            return  $this->getDao($cls)->catch4add_getByProp($prop);
        else
            return  $this->getDao($cls)->getByProp($prop);
        break;
    }/*}}}*/
    public function delCall($op,$cls,$catch4add,$name,$paramNames,$params)
    {/*{{{*/

        $extraParams=null;
        $prop=DynCallParser::buildCondProp($paramNames,$params,$extraParams);
        return  $this->getDao($cls)->delByProp($prop);
    }/*}}}*/
    public function callImpl($op,$cls,$catch4add,$name,$paramNames,$params)
    {/*{{{*/
        
        $dc = DiagnoseContext::create("DDA::callImpl");
        $dc->log("call name : $name ");
        switch($op)
        {
        case 'list':
            return $this->listCall($op,$cls,$catch4add,$name,$paramNames,$params);
            break;
        case 'get':
            return $this->getCall($op,$cls,$catch4add,$name,$paramNames,$params);
            break;
        case 'del':
            return $this->delCall($op,$cls,$catch4add,$name,$paramNames,$params);
            break;
        default:
            DBC::unExpect($op,"unsupport op type");
        }
        $dc->notkeep();
    }/*}}}*/
    public function __call($name,$params)
    {/*{{{*/
        extract( DynCallParser::condObjParse($name));
        $catch4add=false;
        return $this->callImpl($op,$cls,$catch4add,$name,$condnames,$params);
    }/*}}}*/
}/*}}}*/

/** 
 * @brief  direct query ;
 */
class Dquery 
{/*{{{*/
    static public function ins()
    {/*{{{*/
        static $inst=null;
        if($inst == null)
            $inst = new Dquery();
        return $inst;
    }/*}}}*/
    /** 
     * @brief     $dq->list_by_occurtime('flux_table','*',date); 
     * 
     * @param $name 
     * @param $params 
     * 
     * @return 
     */
    public function __call($name,$params)
    {/*{{{*/
        extract( DynCallParser::condObjParse($name));
        return $this->callImpl($op,strtolower($cls),$name,$condnames,$params);
    }/*}}}*/

    public function callImpl($op,$table,$name,$paramNames,$params)
    {/*{{{*/
        $dc = DiagnoseContext::create("Dquery::callImpl");
        $dc->log("call name : $name ");
        switch($op)
        {
        case 'list':
            return $this->listCall($table,$name,$paramNames,$params);
            break;
        case 'get':
            return $this->getCall($table,$name,$paramNames,$params);
            break;
        default:
            DBC::unExpect($op,"$op unsupport! ");
        }
        $dc->notkeep();
    }/*}}}*/

    public function listCall($table,$name,$paramNames,$params)
    {/*{{{*/
        $extraParams=null;
        $prop=DynCallParser::buildCondProp($paramNames,$params,$extraParams);
//        $table = DaoFinder::find($table)->getStoreTable();
        if(count($extraParams) == 0 )
        {
            return  DaoFinder::query("{$table}Query")->listByProp($table,null,'*',$prop);
        }
        else
        {
            $page      = isset($extraParams[0])? $extraParams[0] : null;
            $orderkey  = isset($extraParams[1])? $extraParams[1] : null;
            $ordertype = isset($extraParams[2])? $extraParams[2] : 'DESC';
            return  DaoFinder::query("{$table}Query")->listByProp($table,null,'*',$prop,$page,$orderkey,$ordertype);
        }
    }/*}}}*/
    public function getCall($table,$name,$paramNames,$params)
    {/*{{{*/
        $extraParams=null;
        $prop=DynCallParser::buildCondProp($paramNames,$params,$extraParams);
//        $table = DaoFinder::find($cls)->getStoreTable();
        return  DaoFinder::query("{$table}Query")->getByProp($prop,$table);
    }/*}}}*/

}/*}}}*/

class Dwriter
{/*{{{*/

    static public function ins()
    {/*{{{*/
        static $inst=null;
        if($inst == null)
            $inst = new Dwriter();
        return $inst;
    }/*}}}*/
    public function delCall($cls,$name,$paramNames,$params)
    {/*{{{*/
        $extraParams=null;
        $prop=DynCallParser::buildCondProp($paramNames,$params,$extraParams);
        return  DaoFinder::find($cls)->delByProp($prop);
    }/*}}}*/
    public function updateCall($cls,$updatenames,$condnames,$params)
    {/*{{{*/
        extract(DynCallParser::buildUpdateArray($updatenames,$condnames,$params));
        return DaoFinder::find($cls)->updateByArray($updateArray,$condArray,null);
    }/*}}}*/
    public function __call($name,$params)
    {/*{{{*/
        switch(substr($name,0,3))
        {
        case 'del':
            extract( DynCallParser::condObjParse($name));
            return  $this->delCall($cls,$name,$condnames,$params);
            break;
        case 'upd':
            extract( DynCallParser::condUpdateObjParse($name));
            return  $this->updateCall($cls,$updatenames,$condnames,$params);
            break;
        default:
            DBC::unExpect($op,"unsupport op type");
        }

    }/*}}}*/
}/*}}}*/

/** 
 *  @}
 */
?>
