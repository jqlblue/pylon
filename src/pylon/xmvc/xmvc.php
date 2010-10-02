<?php
/**\addtogroup xmvc   xmvc is  mvc framework
 * @{
 */

interface XScopeInterceptor
{/*{{{*/
    public function _before($request,$xcontext);
    public function _after($request,$xcontext);
}/*}}}*/

interface XErrorInterceptor
{/*{{{*/
    public function _procError($e,$request,$xcontext);
}/*}}}*/


class XAop
{/*{{{*/
    const LOGIC= 1 ;  //逻辑处理
    const TPL= 2 ;    //模板展现
    const ACTION= 3 ; //NEXT Action
    const JUMP= 4 ;   //URL 跳转
    const ERROR= 10 ; //错误处理
    const NORET= 11 ; //无返回值


    static public $rules=array() ;

    static public function pos($pos)
    {
        if(!isset(self::$rules[$pos]))
            self::$rules[$pos] = new XAopRuleSet();
        return self::$rules[$pos];
    }
    static public function using($pos,$conf)
    {/*{{{*/
        $rules = self::pos($pos);
        return $rules->using($conf);
    }/*}}}*/


}/*}}}*/

class  XBoxScopeIntc  implements XScopeInterceptor 
{/*{{{*/
    private $sub_intcs=null;
    private $suc_intcs=null;
    public function __construct($list)
    {/*{{{*/
        $this->sub_intcs = $list;
    }/*}}}*/
    public function _before($request,$xcontext)
    {/*{{{*/

        $this->suc_intcs = array();
        foreach($this->sub_intcs as $i)
        {
            $ret =  $i->_before($request,$xcontext);
            $this->suc_intcs[] = $i;
            if ($ret === false ) break;
        }
    }/*}}}*/
    public function _after($request,$xcontext)
    {/*{{{*/
        while(count($this->suc_intcs) > 0 )
        {
            $i = array_pop($this->suc_intcs);
            $i->_after($request,$xcontext);
        }
    }/*}}}*/
    static public function make()
    {/*{{{*/
        $list = func_get_args();
        return new XBoxScopeIntc($list);
    }/*}}}*/
    static public function makeByArray($list)
    {/*{{{*/
        return new XBoxScopeIntc($list);
    }/*}}}*/
}/*}}}*/

class  XBoxErrorIntc  implements XErrorInterceptor 
{/*{{{*/
    private $sub_intcs=null;
    public function __construct($list)
    {/*{{{*/
        $this->sub_intcs = $list;
    }/*}}}*/
    public function _procError($e,$request,$xcontext)
    {/*{{{*/
        foreach($this->sub_intcs as $i)
        {
            $ret =  $i->_procError($e,$request,$xcontext);
            if(!empty($ret)) return $ret;
        }
        return  null;
    }/*}}}*/
    static public function make()
    {/*{{{*/
        $list = func_get_args();
        return new XBoxErrorIntc($list);
    }/*}}}*/
}/*}}}*/


interface XActionFinder
{/*{{{*/
    public function _find($name);
}/*}}}*/


abstract class  XAction
{/*{{{*/

    public function _setup($request,$xcontext)
    {}
    public function _teardown($request,$xcontext)
    {}
    abstract public function _run($request,$xcontext);
}/*}}}*/

class XNext
{/*{{{*/
    static public function action($name,$args=array())
    {/*{{{*/
        return new ActionCmd($name,$args) ;
    }/*}}}*/
    static public function useTpl($tpl,$vars=null)
    {/*{{{*/
        return new ViewCmd($tpl,$vars);
    }/*}}}*/
    static public function gotoUrl($url)
    {/*{{{*/
        return new JumpCmd($url);
    }/*}}}*/
    static public function nothing()
    {/*{{{*/
        return new EmptyCmd();
    }/*}}}*/
    static public function mutiTpls()
    {/*{{{*/
        return new ViewBoxCmd(func_get_args());
    }/*}}}*/

}/*}}}*/


interface XRenderer
{
    public function _draw($xcontext);
}


class XTools
{/*{{{*/
    static public $actionFinder = null;
    static public $viewRenderer = null;
    static public function actFinder()
    {/*{{{*/

        return self::$actionFinder;
    }/*}}}*/
    static public function renderer()
    {/*{{{*/
        return self::$viewRenderer;
    }/*}}}*/
    static public function regRenderer($r)
    {/*{{{*/
       self::$viewRenderer = $r;
    }/*}}}*/
    static public function regActFinder($r)
    {/*{{{*/
       self::$actionFinder= $r;
    }/*}}}*/

}/*}}}*/
/** 
 * @example: index.html
 * @brief 
 *
 */
class XUrlRouter
{
}

class XController
{/*{{{*/


    public static $actionFinder = null;
    static $exeCmds     =  array();

    static function debug_enable($xcontext,$request)
    {/*{{{*/
        if($request->have('debug'))
        {
            $xcontext->_debug=$request->debug;
            $xcontext->debug=$request->debug; //compatible
            setcookie("debug", $request->debug); 
        }
        else
        {
            $xcontext->_debug= isset($_COOKIE["debug"])? $_COOKIE["debug"] : 0;
            $xcontext->debug= isset($_COOKIE["debug"])? $_COOKIE["debug"] : 0; //compatible
        }
    }/*}}}*/

    static function envPrePare($request,$actionOp,$defaultAction)
    {/*{{{*/
        $actionName = $request->have($actionOp) ?  $request->$actionOp :$defaultAction;
        $actionName = strtolower($actionName);
        $conf       = XTools::actFinder()->_find($actionName);
        return array($actionName,$conf);
    }/*}}}*/


    static public function process($actionOp,$defaultAction)
    {/*{{{*/
        XGod::init();
        XTools::regRenderer(new SimpleRenderer());
        ob_start();
        $xcontext =  XGod::$xcontext;
        $request  =  XGod::$request;
        try
        {

            $dc = DiagnoseContext::create(__METHOD__);
            $dc->log("actionop :$actionOp");
            list($actionName,$conf) = self::envPrePare($request,$actionOp,$defaultAction);
            self::debug_enable($xcontext,$request);
            $dc->log("action :$actionName"); 

            $xcontext->_action      = $actionName;
            $xcontext->_action_op   = $actionOp;
            $xcontext->_action_name  = $cmdChain->name;
            $xcontext->action       = $actionName;
            $xcontext->action_op    = $actionOp;
            $xcontext->action_name  = $cmdChain->name;



            $actcls    = $conf->getCls();
            $actIns =  new $actcls($request,$xcontext);
            array_push(self::$exeCmds,new ExecEnabledCmd( new LogicCmd($actIns),$conf,self::$exeCmds));
            while(1) 
            {
                
                if(empty(self::$exeCmds)) break;
                $cmd = array_shift(self::$exeCmds);
                $result= strtolower($cmd->execute($request,$xcontext));
                if($xcontext->have("_debug") && $xcontext->_debug==2)
                    echo "<br>MVC:cmd excute result:$result!<br>";
                if($result == "end") break;
                if($result == "ignore") continue;
                $xcontext->_cmd_result=$result;

            } 
            $dc->notkeep();
            ob_end_flush();
        }
        catch(Exception $e)
        {
            echo  $e->getMessage();
            $callTraceArr =explode('#',$e->getTraceAsString()); 
            foreach($callTraceArr as $item)                                                               
            {                                                                                             
                echo "$item<br>";                                                                         
            }                                                                                             
            echo "************   DiagnoseMonitor:   ***********<br>";
            $msgs = DiagnoseMonitor::msgs();
            foreach($msgs as $msg)
            {
                echo "$msg<br>";                                                                         
            }
        }
    }/*}}}*/ 

}/*}}}*/
