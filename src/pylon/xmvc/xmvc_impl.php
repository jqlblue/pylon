<?php

class XPeropty
{/*{{{*/
    public $attr=null;
    public function __construct($arr=array())
    {
        $this->attr= &$arr;
    }
    public function have($name)
    {
        if(!is_string($name)) return false;
        return array_key_exists($name,$this->attr);
    }
    public function __get($name)
    {
        return $this->attr[$name];
    }
    public function __set($name,$val)
    {
        return $this->attr[$name]=$val;
    }
    public function merge($other)
    {
        $this->attr = array_merge($this->attr,$other->attr);
    }
    public function mergeArray($other)
    {
        $this->attr = array_merge($this->attr,$other);
    }
}/*}}}*/

class XFinder implements XActionFinder
{/*{{{*/
    public $data;
    public function __construct($file)
    {
        require_once($file);
        $this->data = $_data;
    }

    public function _find($name)
    {
        if(isset($this->data[$name]))
        {
            $d =  $this->data[$name];
            $arr = unserialize($this->data[$name]);
            return new XActionConf($arr);
        }
        DBC::requireTrue(false,"not find action conf data");
    }
    public function _index($exName,$key)
    {
        $i = $this->buildExtendIndex($exName);
        return $i[$key];
    }
    public function buildExtendIndex($ex)
    {
        static $indexMap = array();
        foreach($this->data as $i)
        {
            $data = unserialize($i);
            if(isset($data['extends'][$ex] ))
            {
                $key =  $data['extends'][$ex] ;
                if(is_array($key))
                {
                    foreach($key as $k )
                    {
                        $indexMap[$ex][$k][] = new XActionConf($data); 
                    }
                }
                else
                {
                    $indexMap[$ex][$key][] = new XActionConf($data); 
                }
            }
        }
        return $indexMap[$ex];
    }
}/*}}}*/

class XGod
{/*{{{*/
    static $xcontext = null;
    static $request  = null;
    static public function init()
    {
        self::$xcontext = new XPeropty();
        self::$request  = new XPeropty($_REQUEST);
    }
}/*}}}*/

class AutoScopeIntc
{/*{{{*/
    private $xcontext=null;
    private $intcpt  =null;
    private $args    =null;
    public function __construct($intcpt,$request,$xcontext)
    {
        $this->intcpt = $intcpt;
        $this->xcontext= $xcontext;
        $this->request    = $request;
        $this->intcpt->_before($request,$xcontext);
    }
    public function __destruct()
    {
        $this->intcpt->_after($this->request,$this->xcontext);
    }

}/*}}}*/


class XAopRule
{/*{{{*/
    public $pos;
    public $regex;
    public $args;
    public $isMatch;
    public function __construct($pos,$isMatch,$regex,$args)
    {
        $this->pos  = $pos;
        $this->regex  = $regex; 
        $this->args   = $args;
        $this->isMatch = $isMatch;
    }
    public function using($conf)
    {
        $data = $conf->confValue($this->pos);
        if($data != null   )
        {
            if($this->isMatch && eregi($this->regex,$data ) )
            {
                return $this->args;
            }
            if(!$this->isMatch && !eregi($this->regex,$data ) )
            {
                return $this->args;
            }

        }
        return null;
    }
}/*}}}*/


class XAopRuleSet
{/*{{{*/
    public $set=array();
    public function __call($name,$params)
    {/*{{{*/
        if(!preg_match('/(\S+)_(by)_(\S+)_(\S+)/',$name ,$matchs))
        {
            DBC::unExpect("unknow $callName ,eg:  append_by_name_match('.*',xxxx)");
        }
        list($all,$op,$by,$rule, $pos )=$matchs;
        $match  = null;
        if($rule === "match")
            $match = true;
        else if ($rule === "dismatch") 
            $match = false;
        else
            DBC::unExpect("unknow $match, only support  match , dismatch");

        if($op === "append")
        {
            $this->set[] = new XAopRule($pos,$match,$params[0],$params[1]) ;
        }
        else if ($op === "replace")
        {
            $this->set = array();
            $this->set[] = new XAopRule($pos,$match,$params[0],$params[1]) ;
        }
        else
        {
            DBC::unExpect("unknow $op, only support  append, replace ");
        }
    }/*}}}*/

    public function using($conf)
    {
        $its =array();
        foreach( $this->set as $r)
        {
            $obj = $r->using($conf);
            if($obj  != null)
                $its[] = $obj;
        }
        return $its;
    }
}/*}}}*/

class XActionConf
{/*{{{*/

    public $data;
    public function __construct($arr)
    {

        DBC::requireNotNull($arr,'arr');
        $this->data = $arr;
    }
    public function getCls()
    {
        $cls = $this->data['cls'];
        return $cls;
    }

    public function confValue($name)
    {
        return isset($this->data[$name]) ?  $this->data[$name] : null;
    }
    public function extendValue($name)
    {
        return isset($this->data['extends'][$name]) ?  $this->data['extends'][$name] : null;
    }
}/*}}}*/

interface ICommand
{/*{{{*/
    public function itcPos();
    public function execute($request,$xcontext);
}/*}}}*/

class ViewBoxCmd implements ICommand
{/*{{{*/
    public $cmds;
    public function __construct($cmdsArr)
    {
        DBC::requireTrue(! empty($cmdsArr),"cmds");
        $this->cmds = $cmdsArr;
    }
    public function itcPos()
    {
        return  XAop::TPL;
    }
    public function execute($request,$xcontext)
    {
        foreach($this->cmds as $c)
        {
            $c->execute($request,$xcontext);
        }
        return null;
    }
}/*}}}*/

class LogicCmd implements ICommand
{/*{{{*/
    public $obj;
    public $cmdChain;
    public $conf;
    public function __construct($actIns)
    {/*{{{*/
        DBC::requireNotNull($actIns,'actIns');
        $this->obj = $actIns;

    }/*}}}*/

    public function itcPos()
    {/*{{{*/
        return  XAop::LOGIC;
    }/*}}}*/
    public function execute($request,$xcontext)
    {/*{{{*/
        $cmd = null;
        if($xcontext->have("_debug") && $xcontext->_debug==2)
            echo "<br>Call LogicCmd : " . get_class($this->obj) ;
        $this->obj->_setup($request,$xcontext);
        $cmd = $this->obj->_run($request,$xcontext);
        $this->obj->_teardown($request,$xcontext);
        return $cmd;
    }/*}}}*/
}/*}}}*/

class ViewCmd implements ICommand
{/*{{{*/
    public $tpl;
    public $vars;
    public function __construct($tpl,$vars)
    {/*{{{*/
        XGod::$xcontext->_view = $tpl;
        XGod::$xcontext->_view_vars = $vars;
    }/*}}}*/
    public function itcPos()
    {/*{{{*/
        return  XAop::TPL;
    }/*}}}*/
    public function execute($request,$xcontext)
    {/*{{{*/
        if($xcontext->have("_debug") && $xcontext->_debug==2)
            echo "<br>Call ViewCmd : " . $xcontext->view  ;
        XTools::renderer()->_draw($xcontext);
        return null;
    }/*}}}*/
}/*}}}*/

class JumpCmd implements ICommand
{/*{{{*/
    public $url;
    public function __construct($url)
    {/*{{{*/
        $this->url=$url;
    }/*}}}*/
    public function itcPos()
    {/*{{{*/
        return  XAop::JUMP;
    }/*}}}*/
    public function execute($request,$xcontext)
    {/*{{{*/
        if($xcontext->have("_debug") && $xcontext->_debug==2)
            echo "<br>Call JumpCmd: {$this->url} <br>";
        header("Location: http://{$this->url}");
        return null;
    }/*}}}*/
}/*}}}*/

class ActionCmd implements ICommand
{/*{{{*/
    public $naction;
    public $args ;
    public function  __construct($nexaction,$args=array())
    {/*{{{*/
        $this->naction=$nexaction;
        $this->args   = $args;
    }/*}}}*/
    public function itcPos()
    {/*{{{*/
        return  XAop::ACTION;
    }/*}}}*/
    public function execute($request,$xcontext)
    {/*{{{*/
        $actionUri = $_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'];
        $op= $xcontext->_action_op;
        $args = $this->args;
        $args[$op] = $this->naction;
        $argStr = JoinUtls::jassoArray("&","=",$args);
        $url="$actionUri?$argStr";
        if($xcontext->have("_debug") && $xcontext->_debug==2)
            echo "<br>Call ActionCmd: $url <br>";
        header("Location: http://$url");
        return null;
    }/*}}}*/
}/*}}}*/


class EndCmd implements ICommand
{/*{{{*/
    public function itcPos()
    {/*{{{*/
        return 0;
    }/*}}}*/
    public function execute($request,$xcontext)
    {
        return null;
    }
}/*}}}*/

class EmptyCmd implements ICommand
{/*{{{*/
    public function itcPos()
    {/*{{{*/
        return 0;
    }/*}}}*/
    public function execute($request,$xcontext)
    {
        return null;
    }
}/*}}}*/

class ExecEnabledCmd implements ICommand
{/*{{{*/
    public $cmd;
    public $conf;
    public $cmdChain;

    public function __construct($cmd,$conf,&$cmdChain)
    {/*{{{*/
        DBC::requireNotNull($cmd,'cmd');
        $this->cmd = $cmd;
        $this->cmdChain = &$cmdChain;
        $this->conf = $conf;
    }/*}}}*/
    public function itcPos()
    {/*{{{*/
        return $this->cmd->itcPos();
    }/*}}}*/
    public function  execute($request,$xcontext)
    {/*{{{*/
        $autoScope = null;

        $scopeItc = new XBoxScopeIntc(XAop::using($this->cmd->itcPos(),$this->conf));
        $errItc   = new XBoxErrorIntc(XAop::using(XAop::ERROR,$this->conf));
        try
        {
            if(!empty($scopeItc) )
            {
                $autoScope =  new AutoScopeIntc($scopeItc,$request,$xcontext);
            }
            $resCmd = $this->cmd->execute($request,$xcontext);
            if($this->cmd->itcPos()  == XAop::LOGIC)
            {
                if(empty($resCmd) )
                {
                    $defaultCmds = XAop::using(XAop::NORET,$this->conf);
                    array_push($this->cmdChain,new ExecBoxCmd($defaultCmds,$this->conf,$this->cmdChain ));
                }
                else
                    array_push($this->cmdChain,new ExecEnabledCmd($resCmd,$this->conf,$this->cmdChain ));
            }
        }
        catch(Exception $e)
        {/*{{{*/

            if($errItc != null)
            {
                $cmd =  $errItc->_procError($e,$request,$xcontext);
                array_push($this->cmdChain,new ExecEnabledCmd($cmd,$this->conf,$this->cmdChain ));
            }
            if($xcontext->have('debug')  && $xcontext->debug > 0 )
            {/*{{{*/
                $errorMsg = $e->getMessage();
                $errorPos = $e->getTraceAsString();
                echo  "#####################   Exception Message #################<br>";
                echo  $errorMsg;
                echo  "<br>#####################   Exception Trace    #################<br>";
                $callTraceArr =explode('#',$errorPos);
                if(is_array($callTraceArr ))
                {
                    foreach($callTraceArr as $item)
                    {
                        echo "$item<br>";
                    }
                }
                echo  "<br>----------------------------<br>";
                echo "************   DiagnoseMonitor:   ***********<br>";
                $msgs = DiagnoseMonitor::msgs();
                foreach($msgs as $msg)
                {
                    echo "$msg<br>";
                }

            }/*}}}*/

        }/*}}}*/
        $autoScope = null;
//        return $result;
    }/*}}}*/
}/*}}}*/


class ExecBoxCmd implements ICommand
{/*{{{*/
    public $cmds;
    public $conf;
    public $cmdChain;
    public function __construct($cmdsArr,$conf,$cmdChain)
    {
        DBC::requireTrue(! empty($cmdsArr),"cmds");
        $this->cmds = $cmdsArr;
        $this->cmdChain = $cmdChain;
        $this->conf = $conf;
    }
    public function itcPos()
    {
        DBC::unImplement();
    }
    public function execute($request,$xcontext)
    {
        foreach($this->cmds as $c)
        {
            $ec = new ExecEnabledCmd($c,$this->conf,$this->cmdChain);
            $ec->execute($request,$xcontext);
        }
        return null;
    }
}/*}}}*/


class SimpleRenderer implements XRenderer
{/*{{{*/

    public function _draw($xcontext)
    {/*{{{*/
        $_datas= $xcontext->attr;
        foreach($_datas as $key=>$value){
            $$key = $value;    
        }
        if($xcontext->have("_debug") && $xcontext->_debug==2)
            echo "<br>Smarty TPL:{$xcontext->_xview}<br>";
        include($xcontext->_view);
    }/*}}}*/

}/*}}}*/
