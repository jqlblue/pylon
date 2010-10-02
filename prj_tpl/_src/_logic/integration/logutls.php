<?php
class DebugUtls
{

    static public function sqlLogEnable()
    {
        $logImpl= new MemCollectLogger();
        $executer =  ObjectFinder::find('SQLExecuter');
        $log = ScopeSqlLog::echoCollectLog($executer,$logImpl);
        return $log;
    }
}
?>
