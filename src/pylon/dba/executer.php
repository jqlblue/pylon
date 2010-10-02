<?php 

/**\addtogroup dbaccess
 * @{
 */
/** 
 * @brief 
 */
interface SQLExecuter
{/*{{{*/
    /// 
    /// @brief 
    /// 
    /// @param $cmd 
    /// 
    /// @exception DBException 
    /// @return array 
    /// 
    public function query($cmd);
    /// 
    /// @brief 
    /// 
    /// @param $cmd 
    /// 
    /// @exception DBException 
    /// @return object Array 
    /// 
    public function querys($cmd);
    /// 
    /// @brief 
    /// 
    /// @param $cmd 
    /// @param $begin 
    /// @param $count 
    /// @exception DBException 
    /// @return array of array
    /// 
    public function querysPage($cmd, $begin, $count);
    /// 
    /// @brief 
    /// 
    /// @param $cmd 
    /// 
    /// @exception DBException 
    /// @return bool 
    /// 
    public function exeNoQuery($cmd);
    public function beginTrans();
    public function commit();
    public function rollback();
    public function regLogger($writeLogger, $readLogger);
    public function haveCollector();
    public function regCollector($collector);
    public function unRegCollector();
    public function exeNoQueryDirect($cmd);
}/*}}}*/

/** 
 * @brief 
 */
class DBException extends Exception
{/*{{{*/
    public function __construct($msg)
    {
        parent::__construct($msg);
    }
}/*}}}*/

/** 
 *  @}
 */
?>
