<?php

/**\addtogroup utility
 * @{
 */
function assign($array, $index, $default = '')
{
    return isset($array[$index]) ? $array[$index] : $default;
}
///@ingroup utility 
/// @brief 
/// 
class ScopeAction
{/*{{{*/
    private $_endFun;
    public function __construct($begFun,$endFun)
    {   
        $this->_endFun = $endFun;
        call_user_func($begFun); 
    }
    public function __destruct()
    {   
        call_user_func($this->_endFun);
    }
}/*}}}*/
///@ingroup utility 
/// @brief 
/// 
class ScopeExeCode
{/*{{{*/

    private $_endCode;
    public function __construct($begCode,$endCode)
    {
        $this->_endCode= $endCode;
        eval($begCode);
    }   
    public function __destruct()
    {
        eval($this->_endCode);
    }   
}/*}}}*/

class Prompt
{
    /** 
        * @brief  from keys list recommend  list same as $find;
        * 
        * @param $find 
        * @param $keys 
        * 
        * @return  array;
        * eg: 
        *    Prompt::recommend('xihu',array('xihoo','msn','google','xihooo')); 
        *    return  array('xihoo','xihooo')
     */
    public function recommend($find,$keys)
    {/*{{{*/
        $len = strlen($find);
        $wordlen =3 ;
        if($len >=13 )$wordlen=5;
        if($len >=9 )$wordlen=4;
        $finds = str_split($find,$wordlen);
        $recommend = array();
        foreach($finds as $f)
        {
            if(strlen($f)< 2) continue;
            $recommend = array_merge($recommend,self::match($f,$keys));
        }
        return array_unique($recommend);
    }/*}}}*/
    static private function match($find,$keys)
    {/*{{{*/
        $match=array();
        if(!empty($find))
        foreach($keys as $key)
        {
            if(eregi($find,$key))
                $match[]=$key;
        }
        return $match;
    }/*}}}*/
}

/** 
 *  @}
 */
?>
