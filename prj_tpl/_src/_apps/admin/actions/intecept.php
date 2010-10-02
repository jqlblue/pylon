<?php
class AdminCommonIntc implements XScopeInterceptor
{/*{{{*/
    public $logImpl=null;
    public $log=null;
    public function _before($request,$xcontext) 
    {/*{{{*/
        if($xcontext->debug)
        {
            $executer = ObjectFinder::find('SQLExecuter');
            $executer->regLogger( new EchoLogger, new EchoLogger());
        }
    }/*}}}*/
    public function _after($request,$xcontext)
    {/*{{{*/
        $this->log = null;
    }/*}}}*/
}/*}}}*/
class AdminAuthorization implements XScopeInterceptor
{/*{{{*/
    public function _before($request,$xcontext) 
    {/*{{{*/
        $sessSvc = ObjectFinder::find('SessionSvc');
        if($sessSvc->get('staff') == null)
        {
            throw new AuthorizationException("无登录信息，请您重新登录！");
        }
        $xcontext->staff=$sessSvc->get('staff');
        $logger = LogerManager::getBizOpLoger();
        $logger->log($xcontext->staff->logname ." do: " . $xcontext->action);
    }/*}}}*/
    public function _after($request,$xcontext)
    {/*{{{*/
    }/*}}}*/
}/*}}}*/



class AutoSmartyView implements XScopeInterceptor
{/*{{{*/
    static function viewProc($tpl,$vars,$request,$xcontext)
    {/*{{{*/
        if($vars == "STRUCT")
        {
            $xcontext->_view = "admin_struct.html";
            if($tpl == "AUTO")
            {
                $xcontext->_autoview = $xcontext->_action  . ".html";
            }
            else
            {
                $xcontext->_autoview = $tpl;
            }
        }
        else
        {
            if($tpl == "AUTO")
            {
                $xcontext->_view= $xcontext->_action  . ".html";
            }
            else
            {
                $xcontext->_view= $tpl;
            }
        }
    }/*}}}*/
    public function _before($request,$xcontext) 
    {/*{{{*/
        $smartyRoot  = Conf::PRJ_ROOT."/tmp/smarty";
        $appTplPath  = Conf::PRJ_ROOT . "/" . Conf::ADMIN_TPL_PATH;
        $baseTplPath = Conf::PRJ_ROOT . "/" . Conf::BASE_TPL_PATH;
        $tpldirs = Conf::PRJ_ROOT . "/_src/_web_inf/tpls/admin/" ;
        XTools::regRenderer( new SmartyRenderer($smartyRoot,$tpldirs));
        self::viewProc($xcontext->_view,$xcontext->_view_vars,$request,$xcontext);
        $xcontext->theme = new Theme($appTplPath,$baseTplPath);
    }/*}}}*/
    public function _after($request,$xcontext)
    {/*{{{*/
    }/*}}}*/
}/*}}}*/
