<?php
class SqlProcUtls
{/*{{{*/
    static public function bindCond($key,$val)
    {/*{{{*/
        //处理非单值的情况 
        if ($val  instanceof DQLObj)
        {
            return  '(' . $val->tosql($key) . ')';
        }
        return  " $key = ? " ;
    }/*}}}*/
    static public function filterCondVal($arr)
    {/*{{{*/
        $v= array_filter($arr,create_function('$item','return  ! $item instanceof DQLObj;'));
        return $v;
    }/*}}}*/
    static public function  bindUpdate($key,$val)
    {/*{{{*/
        return  " $key = ? ";
    }/*}}}*/
}/*}}}*/


class DQLObj
{/*{{{*/
    public $express;
    public $symbol;
    public function __construct($express,$symbol='?')
    {
        $this->express = $express ;
        $this->symbol  = $symbol;
    }
    public function tosql($symbolVal)
    {
        return  str_replace($this->symbol, $symbolVal, $this->express) ;
    }
    
}/*}}}*/


class Query 
{/*{{{*/
    public $exer=null;
    public function __construct($exer,$name=null)
    {/*{{{*/
        $this->exer=$exer;
        $this->name=$name;
    }/*}}}*/
    public function getByCmd($cmd,$valsArr=array())
    {/*{{{*/
        return $this->exer->query($cmd,$valsArr);
    }/*}}}*/
    public function getRegName()
    {/*{{{*/
        if($this->name == null)
            return get_class($this);
        else
            return $this->name;
    }/*}}}*/
    public function getExecuter()
    {/*{{{*/
        return $this->exer;
    }/*}}}*/

    static public function toEntity($cls,$data)
    {/*{{{*/
        DBC::unImplement('not implement toEntity function!');
    }/*}}}*/

    public function getByProp($prop,$view,$viewCond='',$columns="*",$addiWhereCmd ="")
    {/*{{{*/
        DBC::requireNotNull($prop);
        $statement = new SQLSelectStatement($view,$viewCond);
        $statement->columns($columns);
        $valsArr =  array();
        $propWhere = self::prop2cmd($prop,$valsArr);
        $statement->where($propWhere.$addiWhereCmd);
        return $this->getByCmd($statement->generateSql(),$valsArr);
    }/*}}}*/
    public function listByCmd($cmd,$valsArr=array())  
    {/*{{{*/
        $rows = $this->exer->querys($cmd,$valsArr);
        return $rows;

    }/*}}}*/

    public function listByCmdPage($cmd,$page,$valsArr=array())  
    {/*{{{*/
        if($page !=null)
        {
            if(!$page->isInit) $page->initTotalRows($this->countOfCmd($cmd,$valsArr));
            $cmd = $cmd . $page->toLimitStr(); 
        }
        $rows = $this->exer->querys($cmd,$valsArr);
        return $rows;

    }/*}}}*/

    private function countOfCmd($cmd,$valsArr=array())
    {/*{{{*/
        if(stristr($cmd,"group") )
        {
            DBC::unImplement(" not support   create count sql from  have 'group' sql");
        }
        $cntcmd = preg_replace("/(select .+)(from .+)/i","select count(1) as cnt \$2",$cmd);
        $row = $this->exer->query($cntcmd,$valsArr);
        return $row['cnt'];
    }/*}}}*/
    private function getObjsCountImpl($statement,$valsArr=array())
    {/*{{{*/
        $statement->columns('count(1) as cnt');
        $row = $this->exer->query($statement->generateSql(),$valsArr);
        return $row['cnt'];
    }/*}}}*/
    static public function prop2cmd($prop,&$valsArr)
    {/*{{{*/
        if($prop !=null && (!$prop->isEmpty()))
        {
            $condsArr     = $prop->getPropArray();
            $valsArr      = SqlProcUtls::filterCondVal(array_values($condsArr));
            $placeholders = array_fill(0,count($condsArr),'?');
            $propCmd= JoinUtls::jassoArrayEx(' and ',$condsArr,array('SqlProcUtls','bindCond'));
            return $propCmd;
        }
        return "";
    }/*}}}*/
    public function listByProp($view,$viewCond,$columns,$prop=null,$page=null,$orderkey=null,$ordertype='DESC',$addiWhereCmd="")
    {/*{{{*/
        $statement = new SQLSelectStatement($view,$viewCond);
        $valsArr = array();
        $propWhere="";
        if($prop !=null && (!$prop->isEmpty()))
        {
            $propWhere = self::prop2cmd($prop,$valsArr);
        }
        $statement->where($propWhere.$addiWhereCmd);
        if($page !=null)
        {
            if(!$page->isInit)
                $page->initTotalRows($this->getObjsCountImpl($statement,$valsArr));
            $begin=0;
            $count=0;
            $page->getRowRange($begin,$count);
            $statement->limit($begin,$count);
        }
        if($orderkey !=null)
        {
            $statement->orderBy($orderkey,$ordertype);
        }
        $statement->columns($columns);
        $rows=$this->listByCmd($statement->generateSql(),$valsArr);
        return $rows;
    }/*}}}*/
}/*}}}*/

class SimpleQueryFactory
{/*{{{*/
    private $execr = null;
    private $isSelfDefFun=null;
    public function __construct($execr)
    {
        $this->execr = $execr;
    }
    public function create($name)
    {
        $selfCls = "{$name}";
        if(Sys::classExists($selfCls))
            return new $selfCls($this->execr);
        return new Query($this->execr,$selfCls);
//        return new Query($this->execr);
    }

    static public function funIns($executer,$isSelfDefFun=null)
    {
        $facotry= new SimpleQueryFactory($executer,array("ComboLoader","classExists"));
        return array($facotry,"create");
    }
}/*}}}*/
?>
