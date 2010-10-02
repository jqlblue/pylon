<?php
class OneIns
{/*{{{*/
    static $insCnt = array() ;
    public function __construct()
    {
        $cls = get_class($this);
        self::$insCnt[$cls] ++ ;
        if(self::$insCnt[$cls] > 1)
            DBC::requireTrue(false,  "$cls have more one instance ");
    }
}/*}}}*/

class AutoCommit  implements XScopeInterceptor  
{/*{{{*/
    static $aps=null;
    private $needCommit=true;
    public function  _before($request ,$xcontext) 
    {
        self::$aps = AppSession::begin();
        $xcontext->_autocommit=$this;
    }
    public function commitAndBegin()
    {
        self::$aps->commit();
        self::$aps=null;
        self::$aps = AppSession::begin();
    }
    public function cancleCommit()
    {
        $this->needCommit = false;
    }
    public function _after($request,$xcontext)
    {
       // return ;
        if($this->needCommit)
            self::$aps->commit();
        self::$aps=null;
        $xcontext->_autocommit=null;
    }
}/*}}}*/

class ArsyncErrorPoc   implements XErrorInterceptor  
{/*{{{*/
    public function _procError($e,$request,$xcontext)
    {
        if(!empty($_POST))
        {
            $loger = LogerManager::getBizErrLoger();
            $xcontext->errorMsg = ActionUtls::logError($e,$loger);
            $xcontext->callTrace= $e->getTraceAsString();
            return   XNext::useTpl("async_error.html");
        }
    }
}/*}}}*/


class NormalErrorPoc  extends OneIns implements XErrorInterceptor  
{/*{{{*/
    public function _procError($e,$request,$xcontext)
    {
            $loger = LogerManager::getBizErrLoger();
            $xcontext->errorMsg = ActionUtls::logError($e,$loger);
            $xcontext->callTrace= $e->getTraceAsString();
            return   XNext::useTpl("AUTO") ;
    }
}/*}}}*/

class StructErrorProc extends OneIns implements XErrorInterceptor  
{/*{{{*/
    public function _procError($e,$request,$xcontext)
    {/*{{{*/
        if( $e instanceof UserInputException)
        {
            $xcontext->errorMsg = $e->getMessage();
        }
        else if($e instanceof AuthorizationException)
        {
            $xcontext->errorMsg = $e->getMessage();
            return XNext::action("login");
        }
        else if ($e instanceof BizException )
        {
            $loger = LogerManager::getBizErrLoger();
            $xcontext->errorMsg = ActionUtls::logError($e,$loger);
            $xcontext->callTrace= $e->getTraceAsString();
        }
        else if ($e instanceof SysException )
        {
            $loger = LogerManager::getSysErrLoger();
            $xcontext->errorMsg = ActionUtls::logError($e,$loger);
            $xcontext->callTrace= $e->getTraceAsString();
        }
        else if ($e instanceof LogicException)
        {
            $loger = LogerManager::getDebugLoger();
            $xcontext->errorMsg = ActionUtls::logError($e,$loger);
            $xcontext->callTrace= $e->getTraceAsString();
        }
        else 
        {
            $loger = LogerManager::getDebugLoger();
            $xcontext->errorMsg = ActionUtls::logError($e,$loger);
            $xcontext->callTrace= $e->getTraceAsString();
        }

        return   XNext::mutiTpls(   XNext::useTpl("AUTO","STRUCT"), 
                                    XNext::useTpl("admin_error.html"));


    }/*}}}*/

}/*}}}*/


?>
