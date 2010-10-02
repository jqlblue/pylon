<?php
///@defgroup smc StatusMachine

/** 
 *@ingroup smc
 *
 * @brief 
 */
class StatusDirect
{
    public $from;
    public $to;
    public function __construct($from,$to)
    {
        $this->from = $from;
        $this->to = $to;
    }
};
/** 
 * @ingroup smc
 * @brief 
 * @example status_machine_tc.php
 */
class StatusMachine
{/*{{{*/

    private $moveableDirects ;
    private $parentSonsMap   ;
    private $rootStatus=null;
    private $currentStatus;
    private $originStatus;

    public  function __construct()
    {/*{{{*/
        $this->moveableDirects = array();
        $this->parentSonsMap = array();
    }/*}}}*/
    public function add()
    {/*{{{*/
        $statusList =  func_get_args();
        foreach($statusList as $status)
        {
            $this->addSon($this->rootStatus,$status); 
        }
    }/*}}}*/
    public function addSon($parent, $son1)
    {/*{{{*/
        $args = func_get_args();
        $parentStatus = array_shift($args); 
        if(array_key_exists($parent,$this->parentSonsMap))
        {
            $oldSons = $this->parentSonsMap[$parent];
            $this->parentSonsMap[$parent] = array_merge($args,$oldSons);
        }
        else
        {
            $this->parentSonsMap[$parent] = $args;
        }
    }/*}}}*/
    public function  parentStatus($son)
    {/*{{{*/
        foreach($this->parentSonsMap as $parent => $sonlist)
        {
            if(in_array($son, $sonlist))
                return $parent;
        }
        return $this->rootStatus;
    }/*}}}*/

    public function isParentSonRelation($parent, $son)
    {       /*{{{*/
        $parentid = $son;
        do      
    {       
        $parentid=$this->parentStatus($son);
        if($parentid == $parent) return true;
        $son = $parentid;
    }while($parentid!= $this->rootStatus);
        return false;
    }       /*}}}*/

    public function bidirecMoveable($first, $second)
    {/*{{{*/
        $this->moveable($first,$second);
        $this->moveable($second,$first);
    }/*}}}*/


    public function moveable()
    {/*{{{*/
        $statusList = func_get_args();
        $len = count($statusList);
        DBC::requireTrue($len >= 2 );
        for($i =1 ; $i<$len ; ++$i )
        {
            $left2right = new StatusDirect($statusList[$i-1],$statusList[$i]); 
            array_push($this->moveableDirects,$left2right);
        }
    }/*}}}*/
    public function selfMoveable($status)
    {/*{{{*/
        $this->moveable($status,$status);
    }/*}}}*/
    public function canMoveTo($status)
    {/*{{{*/
        foreach( $this->moveableDirects as $statusDirect)
        {
            if(($statusDirect->from == $this->currentStatus  || 
                $this->isParentSonRelation($statusDirect->from ,$this->currentStatus)) && 
                $statusDirect->to == $status )
                return true;
        }
        return false;
    }/*}}}*/
    public function moveTo($status)
    {/*{{{*/
        DBC::requireTrue($this->canMoveTo($status),"cant move to $status");
        $this->originStatus = $this->currentStatus;
        $this->currentStatus = $status;
    }/*}}}*/
    public function undo()
    {/*{{{*/
        $this->currentStatus = $this->originStatus;
    }/*}}}*/
    public function  curStatus()
    {/*{{{*/
        return $this->currentStatus;
    }/*}}}*/
    public function setCurrentStatus( $status)
    {/*{{{*/
        $this->currentStatus= $status;
    }/*}}}*/
}/*}}}*/
?>
