<?php
class DiagnoseMonitor
{/*{{{*/
    static $contexts=array();
    static public function push($context)
    {
        array_push(self::$contexts,$context);
    }
    static public function pop()
    {
        array_pop(self::$contexts);
    }
    static public function msgs()
    {
        $msgs = array();
        foreach( self::$contexts as $c)
        {
            $msgs[] = "context: [{$c->name}]";
            $msgs = array_merge($msgs,$c->messages);
        }
        return $msgs;
    }
}/*}}}*/

class DiagnoseContext
{/*{{{*/
    public $name;
    public $iskeep=true;
    public $messages=array();
    private  function __construct($name)
    {
        $this->name = $name;
        $this->impl = new DContextImpl();
        DiagnoseMonitor::push($this->impl);
    }
    static function create($name)
    {
        return new DiagnoseContext($name);
    }
    public function log($msg)
    {
        $this->impl->log($msg) ;
    }
    public function notkeep()
    {
        $this->iskeep=false;
    }
    public function __destruct()
    {
        if(!$this->iskeep)
            DiagnoseMonitor::pop();
    }

}/*}}}*/

class DContextImpl
{/*{{{*/
    public $name;
    public $messages=array();
    public function log($msg)
    {
        array_push($this->messages,$msg);
    }
}/*}}}*/
