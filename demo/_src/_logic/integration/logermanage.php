<?php
class LogerManager
{/*{{{*/
    static public function getSvcLoger()
    {/*{{{*/
        return  new UnixSysLogger("cube-svc");
    }/*}}}*/
    static public function getDebugLoger()
    {/*{{{*/
        return  new UnixSysLogger("cube-bug");
    }/*}}}*/
    static public function getSysErrLoger()
    {/*{{{*/
        return  new UnixSysLogger("cube-sys");
    }/*}}}*/
    static public function getBizErrLoger()
    {/*{{{*/
        return  new UnixSysLogger("cube-biz");
    }/*}}}*/
    static public function getDataLoger()
    {/*{{{*/
        return  new UnixSysLogger("cube-data");
    }/*}}}*/
    static public function getEvtLoger()
    {/*{{{*/
        return  new UnixSysLogger("cube-evt");
    }/*}}}*/
    static public function getSynLoger()
    {/*{{{*/
        return  new UnixSysLogger("cube-syn");
    }/*}}}*/
    static public function getBizOpLoger()
    {/*{{{*/
        return  new UnixSysLogger("cube-op");
    }/*}}}*/
    static public function getSqlLoger()
    {/*{{{*/
        return  new UnixSysLogger("cube-sql");
    }/*}}}*/

    static public function getAcctLoger()
    {/*{{{*/
        return  new UnixSysLogger("cube-acct");
    }/*}}}*/
    static public function getPayLoger()
    {/*{{{*/
        return  new UnixSysLogger("cube-pay");
    }/*}}}*/
}/*}}}*/
?>
