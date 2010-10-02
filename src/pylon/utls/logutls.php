<?php
class EchoLogger
{/*{{{*/
    public function log($msg)
    {
        echo "\nlog: $msg\n<br>";
    }
}/*}}}*/
class MemCollectLogger
{/*{{{*/
    public $logMsgs=array();
    public function log($msg)
    {
        $this->logMsgs[]=$msg;
    }
}/*}}}*/
class CombinLogger
{/*{{{*/
    private $logers=null;
    public function __construct()
    {
        $this->logers =  func_get_args();
    }
    public function log($msg)
    {
        foreach($this->logers as $logger)
        {
            $logger->log($msg);
        }
    }
}/*}}}*/
class ScopeExec
{/*{{{*/
    protected $endFun;
    protected $arg;
    protected function __construct($beginFun,$endFun,$arg)
    {
        $this->endFun=$endFun;
        $this->arg=$arg;
        $beginFun($this->arg);
    }
    public function __destruct()
    {
        $endFun=$this->endFun;
        $endFun($this->arg);
    }
}/*}}}*/

class ScopeSqlLog extends ScopeExec
{
    static public function echoLog($executer)
    {/*{{{*/
        return new ScopeSqlLog(
            create_function('$executer','$executer->regLogger( new EchoLogger(), new NullLogger());'),
            create_function('$executer','$executer->regLogger( new NullLogger(), new NullLogger());'),
            $executer);
    }/*}}}*/
    static public function collectLog($executer, $collectLog)
    {/*{{{*/
        $prop = PropertyObj::create();
        $prop->executer=$executer;
        $prop->log=$collectLog;
        return new ScopeSqlLog(
            create_function('$prop','$prop->executer->regLogger( $prop->log, new NullLogger());'),
            create_function('$prop','$prop->executer->regLogger( new NullLogger(), new NullLogger());'),
            $prop);
    }/*}}}*/

    static public function echoCollectLog($executer, $collectLog)
    {/*{{{*/
        $prop = PropertyObj::create();
        $prop->executer=$executer;
        $prop->log=new CombinLogger($collectLog, new EchoLogger());
        return new ScopeSqlLog(
            create_function('$prop','$prop->executer->regLogger( $prop->log, new NullLogger());'),
            create_function('$prop','$prop->executer->regLogger( new NullLogger(), new NullLogger());'),
            $prop);
    }/*}}}*/

    static public function echoCollectWLog($executer, $collectLog)
    {/*{{{*/
        $prop = PropertyObj::create();
        $prop->executer=$executer;
        $prop->log=new CombinLogger($collectLog, new EchoLogger());
        return new ScopeSqlLog(
            create_function('$prop','$prop->executer->regLogger( new NullLogger(),$prop->log );'),
            create_function('$prop','$prop->executer->regLogger( new NullLogger(), new NullLogger());'),
            $prop);
    }/*}}}*/

    static public function collectWLog($executer, $collectLog)
    {/*{{{*/
        $prop = PropertyObj::create();
        $prop->executer=$executer;
        $prop->log=$collectLog;
        return new ScopeSqlLog(
            create_function('$prop','$prop->executer->regLogger( new NullLogger(),$prop->log );'),
            create_function('$prop','$prop->executer->regLogger( new NullLogger(), new NullLogger());'),
            $prop);
    }/*}}}*/
}

class ScopeEchoLog extends ScopeExeCode
{/*{{{*/
    public $_executer = null;
    public function __construct($executer)
    {       
        $this->_executer = $executer;
        parent::__construct(
            '$this->_executer->regLogger(new EchoLogger(),new NullLogger());',

            ' $this->_executer->regLogger(new NullLogger(),new NullLogger());');
    }   
}/*}}}*/
?>
