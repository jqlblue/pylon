<?php

/**\addtogroup domin_mdl
 * @{
 */
interface UnitWork
{/*{{{*/
    public function commit();
    public function regLoad($obj);
    public function regAdd($obj);
    public function regDel($obj);
}/*}}}*/
class EmptyUnitWork implements UnitWork
{/*{{{*/
    public function commit()
    {}
    public function regLoad($obj)
    {
        return $obj;
    }
    public function regAdd($obj)
    {
        return $obj;
    }
    public function regDel($obj)
    {}
    public function clean()
    {}
}/*}}}*/
class ScopeAutoTrans
{/*{{{*/
    private $exer;
    private $issuc=false;
    public function __construct($exer)
    {
        $this->exer= $exer;
        $this->exer->beginTrans();
    }
    public function isSuccess()
    {
        $this->issuc=true;
    }
    public function __destruct()
    {
        if($this->issuc)
            $this->exer->commit();
        else
            $this->exer->rollback();
    }
}/*}}}*/
class UnitWorkImpl extends ObjUpdater implements UnitWork
{/*{{{*/
    public function __construct()
    {
    }
    public function __destruct()
    {
        parent::__destruct();
    }
    public function dao($obj)
    {
        return DaoFinder::find($obj);    
    }
    public function addImpl($obj)
    {
        $this->dao($obj)->add($obj);
    }
    public function delImpl($obj)
    {
        $this->dao($obj)->del($obj);
    }
    public function updateImpl($obj)
    {
        $obj->upgrade();
        $this->dao($obj)->update($obj);
    }

    public function commit()
    {/*{{{*/
        $exers = DaoFinder::getExecuterList();
        $this->transCommit($exers);
        $this->clean();
    }/*}}}*/
    private function transCommit($exers)
    {/*{{{*/
        $transObjs=array();
        foreach($exers as $e)
        {
            $transObjs[] = new ScopeAutoTrans($e);
        }
        $this->commitUpdate( array($this,'addImpl'), array($this,'delImpl'),array($this,'updateImpl'));
        foreach($transObjs as $o)
        {
            $o->isSuccess();
        }
    }/*}}}*/
}/*}}}*/

class AppSession
{/*{{{*/
    private     $isCancle=true;
    protected   $unitWork=null;
    protected   $useDaos=array();
    static protected $name;
    static protected $idMap=null;
    static public function begin($name='app')
    {/*{{{*/
        self::$idMap=null;
        self::$name=$name;
        $aps = new AppSession(new UnitWorkImpl());
        return $aps;
    }/*}}}*/

    protected function __construct($unitWork)
    {/*{{{*/
        $this->unitWork = $unitWork;
        Entity::unitWork($unitWork);
    }/*}}}*/

    public function __destruct()
    {/*{{{*/
        if($this->isCancle )
            Entity::unitWork()->clean();
        $this->unitWork = null;
    }/*}}}*/
    public function commit()
    {/*{{{*/
        Entity::unitWork()->commit();
//        CacheAdmin::$innerCacheSpace->clears

        $this->isCancle=true;
    }/*}}}*/
}/*}}}*/


/** 
 *  @}
 */
?>
