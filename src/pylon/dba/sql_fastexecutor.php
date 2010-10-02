<?php

/**\addtogroup dbaccess
 * @{
 */
class FastSQLExecutor
{/*{{{*/
    const LONG_CONN=true;
    const SHORT_CONN=false;
    private $_dbh = null;
    private $_sqlCollector =null;
    private $_sqlLogger  = null;
    private $_wsqlLogger  = null;

    public function __construct($host, $userName, $password, $dbName,$connType=FastSQLExecutor::SHORT_CONN,$charset='GBK')
    {/*{{{*/
        $this->_dbh = new PDO("mysql:host=$host;dbname=$dbName;",
            $userName,
            $password,
            array(PDO::ATTR_PERSISTENT => $connType)
        );
        $this->_dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
//        $this->_dbh->exec("set charset $charset");
        $this->_dbh->query("SET NAMES $charset");
    }/*}}}*/


    public function regCollector($collector)
    {/*{{{*/
        $this->_sqlCollector = $collector;
    }/*}}}*/
    public function haveCollector()
    {
        return $this->_sqlCollector !=null;
    }
    public function unRegCollector()
    {
        $this->_sqlCollector = null;
    }
    public function regLogger($sqlLogger,$wirteSqlLogger) 
    {/*{{{*/
        $this->_sqlLogger = $sqlLogger;
        $this->_wsqlLogger = $wirteSqlLogger;
    }/*}}}*/
    public function query($sql, $values=array())
    {/*{{{*/
        $dc = DiagnoseContext::create(__METHOD__);
        $this->logAllSql($dc,$sql,$values);
        $sth = $this->_dbh->prepare($sql);
        $i = 0;
        foreach($values as $value)
        {
            $sth->bindValue(++$i, $value);
        }
        if($sth->execute())
        {
            $results = $sth->fetchAll(PDO::FETCH_ASSOC);
            if(is_array($results) && isset($results[0]))
            {
                $dc->notkeep();
                return $results[0];
            }
        }
        $dc->notkeep();
        return null;
    }/*}}}*/

    public function querys($sql, $values=array())
    {/*{{{*/

        $dc = DiagnoseContext::create(__METHOD__);
        $this->logAllSql($dc,$sql,$values);
        $sth = $this->_dbh->prepare($sql);
        $i = 0;
        $res = array();
        foreach($values as $value)
        {
            $sth->bindValue(++$i, $value);
        }
        if($sth->execute())
        {
            $res= $sth->fetchAll(PDO::FETCH_ASSOC);
        }
        $dc->notkeep();
        return  $res;
    }/*}}}*/


    static public function stdSqlValues($arr)
    {/*{{{*/
        $lists = array();
        foreach($arr as $key => $item)
        {
            if(is_string($item))
                $lists[$key] = "'$item'";
            elseif(is_null($item))
                $lists[$key] = "null";
            else                                                                                                            
                $lists[$key] = $item;
        }
        return $lists;
    }/*}}}*/
    public function logWritedSql($dc,$sql, $values=array())
    {/*{{{*/
        if(!is_null($this->_wsqlLogger) ) 
        {
            if(!empty($values))
            {
                // proc sql such as " like xxx%c"
                $logsql = str_replace('%','#',$sql); 
                $logsql = str_replace('?','%s',$logsql);
                $logsql= vsprintf($logsql,self::stdSqlValues($values));
                $logsql = str_replace('#','%s',$logsql);
                $this->_wsqlLogger->log($logsql);
                $dc->log("sql:$logsql");
            }
            else
            {
                $this->_wsqlLogger->log($sql);
                $dc->log("sql:$sql");
            }

        }
    }/*}}}*/

    public function logAllSql($dc,$sql, $values=array())
    {/*{{{*/
        if(!is_null($this->_sqlLogger) ) 
        {
            if(!empty($values))
            {
                $logsql = str_replace('%','#',$sql); // process like : linke %xxx%
                $logsql = str_replace('?','%s',$logsql);
                $logsql= vsprintf($logsql,self::stdSqlValues($values));
                $logsql = str_replace('#','%',$logsql);
                $this->_sqlLogger->log($logsql);
                $dc->log("sql:$logsql");
            }
            else
            {
                $this->_sqlLogger->log($sql);
                $dc->log("sql:$sql");
            }


        }
    }/*}}}*/
    public function exeNoQuery($sql, $values=array())
    {/*{{{*/
        $dc = DiagnoseContext::create(__METHOD__);
        $this->logWritedSql($dc,$sql,$values);
        $this->logAllSql($dc,$sql,$values);
        $sth = $this->_dbh->prepare($sql);
        $i = 0;
        foreach($values as $value)
        {
            $sth->bindValue(++$i, $value);
        }
        $res= $sth->execute();
        $dc->notkeep();
        return $res;
    }/*}}}*/
    public function exeNoQueryDirect($sql, $values=array())
    {/*{{{*/
//        $this->logAllSql($sql,$values);
        $sth = $this->_dbh->prepare($sql);
        $i = 0;
        foreach($values as $value)
        {
            $sth->bindValue(++$i, $value);
        }
        return $sth->execute();
    }/*}}}*/
    public function exeNoQuerys($cmds)
    {/*{{{*/
        if(is_array($cmds) && !empty($cmds))
            foreach($cmds as $cmd)
            {
                if(!$this->exeNoQuery($cmd)) 
                    return false;
            }
        else
            return false;

        return true;
    }/*}}}*/
    public function execute($sql, $values=array())
    {/*{{{*/
        $dc = DiagnoseContext::create(__METHOD__);
        $this->logWritedSql($dc,$sql,$values);
        $this->logAllSql($dc,$sql,$values);
        $sth = $this->_dbh->prepare($sql);
        $i = 0;
        foreach($values as $value)
        {
            $sth->bindValue(++$i, $value);
        }
        $sth->execute();
        $dc->notkeep();
        return $sth->rowCount();
    }/*}}}*/


    public function beginTrans()
    {/*{{{*/
        $this->_dbh->beginTransaction();
    }/*}}}*/

    public function commit()
    {/*{{{*/
        return $this->_dbh->commit();
    }/*}}}*/

    public function rollback()
    {/*{{{*/
        return $this->_dbh->rollback();
    }/*}}}*/

    public function getLastInsertID()
    {/*{{{*/
        return (int)$this->_dbh->lastInsertId();
    }/*}}}*/

}/*}}}*/

/** 
 *  @}
 */

?>
