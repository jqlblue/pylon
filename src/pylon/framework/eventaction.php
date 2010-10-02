<?php

/**\addtogroup domin_mdl
 * @{
 */

class Request
{/*{{{*/
    static public $uri=null;
    public function getGetVars()
    {
        return $_GET;
    }

    public function getPostVars()
    {
        return $_POST;
    }
}/*}}}*/

class SimpleRequest implements  IRequest
{/*{{{*/
    public function getVars()
    {
        array_walk($_REQUEST,create_function('&$item','if(is_string($item)) $item=trim($item);')); 
        return $_REQUEST;
    }
}/*}}}*/

class EventAction2 extends PropertyObj
{/*{{{*/
    private $haveDynCall=false;
    private $request=null;
    public function exec_method($action)
    {/*{{{*/
        return "exec_action";
    }/*}}}*/
    public function bindRequest($request)
    {/*{{{*/
        $this->request = $request;
    }/*}}}*/
    public function getRequest()
    {/*{{{*/
        if(  $this->request ==null )
            $this->request = new SimpleRequest();
        return $this->request;    
    }/*}}}*/
    public function getEventDef()
    {/*{{{*/
        return $this->eventConf;
    }/*}}}*/
    public function __construct($sessSvc,$eventsConf,$param1=null,$param2=null,$param3=null)
    {/*{{{*/
        $this->eventConf= $eventsConf;
        $this->eventMC = new EventMachine2($sessSvc);  
        $this->eventMC->regScopeListener($this); 
        $this->varsLife = new LifeScope($sessSvc);
        $this->sessSvc = $sessSvc;
        $this->param1 = $param1;
        $this->param2 = $param2;
        $this->param3 = $param3;
    }/*}}}*/
    public function setReStorePoint($name,$desc="")
    {/*{{{*/
        $this->eventMC->setReStorePoint($this->sessSvc,$name,$desc);
    }/*}}}*/
    public function restore($name,$failurl=null)
    {/*{{{*/
        $url = $this->eventMC->restore($this->sessSvc,$name,$failurl);
        $this->caller->appendCmd(new JumpCmd($url));
    }/*}}}*/
    public function listRestorePoints()
    {/*{{{*/
        return $this->eventMC->listRestorePoints($this->sessSvc);
    }/*}}}*/
    public function restore2LastPoint($failurl=null)
    {/*{{{*/
        $url = $this->eventMC->restore2LastPoint($this->sessSvc,$failurl);
        $this->caller->appendCmd(new JumpCmd($url));
    }/*}}}*/
    public function setMaxPoints($max)
    {/*{{{*/
        $this->eventMC->setMaxPoints($this->sessSvc,$max);
    }/*}}}*/
    public function setup($spec,$request,$xcontext,$dda)
    {/*{{{*/
    }/*}}}*/
    public function tearDown($spec,$request,$xcontext,$dda)
    {}
    public function exec_action($caller,$xcontext)
    {/*{{{*/
        $actionName = $xcontext->_action;
        if($this->haveDynCall)
        {
            $cls = get_class($this);
            throw new LogicException("your action class [$cls] not define [$name] function" );
        }
        $this->haveDynCall = true;
        $this->eventConf->setDefaultEvent($actionName);
        $this->caller=$caller;

        $this->varsLife->swapAutoStoreSpace($xcontext->action);
        $request = $this->getRequest();
        $ret = $this->eventMC->dispach($actionName,$this->eventConf,$this->varsLife,$this,$request,$xcontext,
            $this->param1,$this->param2,$this->param3);
        return $ret;
    }/*}}}*/
}/*}}}*/
/** 
 *  @}
 */
?>
