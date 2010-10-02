<?php
class UnixSysLogger
{/*{{{*/
    public function __construct($what)
    {
        $this->what=$what;
//        openlog( $this->what, LOG_PID, LOG_LOCAL6);
    }
    public function log($msg)
    {
        openlog( $this->what, LOG_PID, LOG_LOCAL6);
        syslog(LOG_INFO,$msg);
    }
    public function err($msg)
    {
        openlog( $this->what, LOG_PID, LOG_LOCAL6);
        syslog(LOG_ERR,$msg);
    }
    public function debug($msg)
    {
        openlog( $this->what, LOG_PID, LOG_LOCAL6);
        syslog(LOG_DEBUG,$msg);
    }
}/*}}}*/
?>
