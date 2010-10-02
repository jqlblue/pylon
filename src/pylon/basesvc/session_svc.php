<?php
class NullSessionDriver
{
    public function init()
    { }
}
/**\addtogroup basesvc
 * @{
 */
class SessionSvc
{
    static $_isStart = false;
    private $driver;
    public function __construct($sessName,$driver)
    {/*{{{*/
        $this->driver=$driver;
        $driver->init();
        session_name($sessName);
    }/*}}}*/
    static private function ensureStarOnce()
    {/*{{{*/
        if (!self::$_isStart)
        {
            session_start();
            self::$_isStart = true;
        }
    }/*}}}*/
    static function clear()
    {
        session_destroy();
    }
    public function setSessionID($id)
    {/*{{{*/
        session_id($id);
    }/*}}}*/
    public function getSessionID()
    {/*{{{*/
        self::ensureStarOnce(); 
        return session_id();
    }/*}}}*/
    /*public method*/
    public function save($key, $var='')
    {/*{{{*/
        self::ensureStarOnce(); 
        $_SESSION[$key] = $var;
        return true;
    }/*}}}*/

    public function get($key)
    {	/*{{{*/
        self::ensureStarOnce(); 
        if (isset($_SESSION[$key]))
            return $_SESSION[$key];
        else
            return NULL;
    }/*}}}*/

    public function del($key)
    {/*{{{*/
        return $this->destroy($key);
    }/*}}}*/
    public function destroy($key)
    {/*{{{*/
        self::ensureStarOnce(); 
        unset($_SESSION[$key]);
        return true;
    }/*}}}*/

    public function destroyAll()
    {/*{{{*/
        self::ensureStarOnce(); 
        $_SESSION = array();
        return true;
    }/*}}}*/

    public function getAll()
    {/*{{{*/
        self::ensureStarOnce(); 
        return $_SESSION;
    }/*}}}*/

/** 
 *  @}
 */
}

?>
