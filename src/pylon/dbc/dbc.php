<?php

/**\addtogroup DBC
 * @{
 */
class DBCException extends LogicException
{
}


/** 
 * @example dbc_tc.php
 * @brief 
 * 
 */
class DBC
{/*{{{*/
    const DO_EXCEPTION  =1;
    const DO_ABORT      =2 ;
    const DO_WARN       =3 ;
    const DO_NO         =4 ;

    public static  $failAction = DBC::DO_EXCEPTION;
    static private function notEqualsMsg($first,$second,$firstName)
    {/*{{{*/
        if(is_null($first) ) $first = "null";
        if(is_null($second) ) $second= "null";
        if(is_bool($first) ) $first = $first? "bool:true":"bool:false";
        if(is_bool($second) ) $second= $second? "bool:true":"bool:false";
        return "$firstName  is [ $first ] , but expect is not [ $second ]  <br>\n" ;
    }/*}}}*/

    static private function equalsMsg($first,$second,$firstName)
    {/*{{{*/
        if(is_null($first) ) $first = "null";
        if(is_null($second) ) $second= "null";
        if(is_bool($first) ) $first = $first? "bool:true":"bool:false";
        if(is_bool($second) ) $second= $second? "bool:true":"bool:false";
        return "$firstName  is [ $first ] , but expect is  [ $second ] <br>\n" ;
    }/*}}}*/
    static private function objMsg($obj,$msg)
    {/*{{{*/
        if(is_null($obj) ) $obj = "null";
        return  "object $msg: [$obj]";
    }/*}}}*/
    static private function dofailAction($msg)
    {/*{{{*/
        switch(DBC::$failAction)
        {
        case  DBC::DO_EXCEPTION :
            throw new DBCException($msg);
            break;
        case DBC::DO_ABORT:
            echo $msg;
            exit ;
        case DBC::DO_WARN: 
            echo "$msg\n";     
            break;
        default:
            exit;
        }
    }/*}}}*/

    static private function nullMsg($val,$type)
    {/*{{{*/
        return  "type is $type, Object is null !";
    }/*}}}*/

    /** 
     * @brief 
     * 
     * @param $first 
     * @param $second 
     * @param $firstName 
     * 
     * @return  $first
     */
    static public function requireEquals($first,$second,$msg= "first value != second value")
    {/*{{{*/
        if($first != $second)
            DBC::dofailAction($msg);
        return $first;
    }/*}}}*/
    /** 
     * @brief 
     * 
     * @param $first 
     * @param $parentClass 
     * 
     * @return $first
     */
    static public function requireIsA($first,$parentClass,$msg="value is not subclass of ")
    {/*{{{*/
        if(!is_a($first,$parentClass))
            DBC::dofailAction($msg);
        return $first;
    }/*}}}*/
    static public function requireNotEquals($first,$second,$msg="first value == second value")
    {/*{{{*/
        if($first == $second )
            DBC::dofailAction($msg);
        return $first;
    }/*}}}*/
    static  public function unExpect($obj,$msg= "unexcept!")
    {/*{{{*/
        DBC::dofailAction("$obj $msg");
    }/*}}}*/
    /** 
        * @brief  ���ڶ��Դ�����û��ʵ�֡�
        * 
        * @param $funName 
        * 
        * @return void  
     */
    static public function unImplement($msg="have not implment")
    {/*{{{*/
        self::dofailAction($msg);
    }/*}}}*/
    static public function requireNull($obj,$msg="value is not null")
    {/*{{{*/
        if(is_null($obj)) return $obj;
        DBC::dofailAction($msg);

    }/*}}}*/
    /** 
     * @brief 
     * 
     * @param $obj 
     * @param $msg 
     * 
     * @return $obj 
     */
    static public function requireNotNull($obj,$msg="value is  null ")
    {/*{{{*/
        if(!is_null($obj)) return $obj;
        DBC::dofailAction($msg);
    }/*}}}*/

    /** 
     * @brief 
     * 
     * @param $obj 
     * @param $msg 
     * 
     * @return $obj 
     */
    static public function requireObj($obj,$msg="value is not object")
    {/*{{{*/
        if(is_object($obj)) return $obj;
        DBC::dofailAction($msg);
    }/*}}}*/
    /** 
     * @brief 
     * 
     * @param $obj 
     * @param $msg 
     * 
     * @return $obj 
     */
    static public function requireTrue($obj,$msg="require true ,but is false")
    {/*{{{*/
        if($obj) return $obj;
        DBC::dofailAction($msg);
    } /*}}}*/
    static public function requireArray($arr,$msg="value is not  Array")
    {
        if(is_array($arr)) return $arr;
        DBC::dofailAction($msg);
    }


}/*}}}*/

/** 
 *  @}
 */
?>
