<?php

/**\addtogroup domin_mdl
 * @{
 */
class BizException extends RuntimeException
{/*{{{*/
    const NO_DATA = "no data ";
    const OP_OTHERUSER_DATA= "op otheruser data!";
    public function __construct($message, $code = 0) 
    {
        parent::__construct($message, $code);
    }
}/*}}}*/

class AuthorizationException extends BizException
{/*{{{*/
    public function __construct($message=null) 
    {
        if(is_null($message))
            $message = "you can't access this page";
        $code =0;
        parent::__construct($message, $code);
    }
}/*}}}*/

class SysException extends RuntimeException
{/*{{{*/
    public function __construct($message, $code = 0) 
    {
        parent::__construct($message, $code);
    }
}/*}}}*/

class ResourceException extends RuntimeException
{/*{{{*/
    public function __construct($message,$code =0)
    {
        parent::__construct($message, $code);
    }
}/*}}}*/
class UserInputException extends RuntimeException 
{/*{{{*/
    public $obj;
    public function __construct($message, $obj=null,$code = 0) 
    {
        parent::__construct($message, $code);
        $this->obj = $obj;
    }
}/*}}}*/

class BizResult
{/*{{{*/
    static public function ensureNull( $result,$info)
    {/*{{{*/
        if(!is_null($result))
            throw new BizException($info);
        return $result;
    }/*}}}*/
    static public function ensureNotNull($result, $info)
    {/*{{{*/
        if(is_null($result))
            throw new BizException($info);
        return $result;
    }/*}}}*/
    static public function ensureNotEmpty($result,$info)
    {/*{{{*/
        if(empty($result))
            throw new BizException($info);
        return $result;
    }/*}}}*/
    static public function ensureNotFalse($result,$info)
    {/*{{{*/
        if(!$result)
            throw new BizException($info);
        return $result;
    }/*}}}*/
}/*}}}*/


class BR
{/*{{{*/
    static public function isNull( $result,$info)
    {/*{{{*/
        if(!is_null($result))
            throw new BizException($info);
        return $result;
    }/*}}}*/
    static public function notNull($result, $info)
    {/*{{{*/
        if(is_null($result))
            throw new BizException($info);
        return $result;
    }/*}}}*/
    static public function isEmpty($result,$info)
    {/*{{{*/
        if(!empty($result))
            throw new BizException($info);
        return $result;
    }/*}}}*/
    static public function isTrue($result,$info)
    {/*{{{*/
        if(!$result)
            throw new BizException($info);
        return $result;
    }/*}}}*/
}/*}}}*/
/** 
 *  @}
 */
?>
