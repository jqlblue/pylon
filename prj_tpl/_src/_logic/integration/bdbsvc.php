<?php
class BdbDataSvc
{/*{{{*/
    public $dbPath;
    protected $fileHandler;

    public function  __construct($dbPath)
    {/*{{{*/
        DBC::requireNotNull($dbPath);
        $this->dbPath = $dbPath;
    }/*}}}*/
    public function __destruct()
    {/*{{{*/
        $this->close();
    }/*}}}*/
    public function create()
    {/*{{{*/
        $this->fileHandler= BizResult::ensureNotFalse(
            dba_open($this->dbPath, "n",'db4' ),
            "open bdb file  Failed! {$this->dbPath}");
    }/*}}}*/
    public function open()
    {/*{{{*/
        $this->fileHandler= BizResult::ensureNotFalse(
            dba_open($this->dbPath, "c",'db4' ),
            "open bdb file  Failed! {$this->dbPath}");
    }/*}}}*/
    public function readonly()
    {/*{{{*/
        $this->fileHandler= BizResult::ensureNotFalse(
            dba_open($this->dbPath, "r",'db4' ),
            "open bdb file  Failed! {$this->dbPath}");
    }/*}}}*/
    public function close()
    {/*{{{*/
        if($this->fileHandler !=null)
        {
            dba_close($this->fileHandler);
            $this->fileHandler=null;
        }
    }/*}}}*/

    public function get($key)
    {/*{{{*/
        return unserialize(dba_fetch($key,$this->fileHandler));
    }/*}}}*/
    public function set($key,$data)
    {/*{{{*/
        return dba_replace($key, serialize($data), $this->fileHandler);
    }/*}}}*/
}/*}}}*/
