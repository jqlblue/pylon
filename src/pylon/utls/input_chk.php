<?php
class Validater extends PropertyObj
{/*{{{*/
    const INPUT_TXT=1;
    const INPUT_DIG=2;
    static public function buildText($errmsg,$reg,$minlen=-1,$maxlen=-1)
    {/*{{{*/
        $obj = new Validater();
        $obj->errmsg = $errmsg;
        $obj->regex = $reg;
        $obj->minlen = $minlen;
        $obj->maxlen = $maxlen;
        $obj->type=Validater::INPUT_TXT;
        return $obj;
    }/*}}}*/
    static public function buildDigit($errmsg,$minval=-1,$maxval=-1)
    {/*{{{*/

        $obj = new Validater();
        $obj->errmsg = $errmsg;
        $obj->regex = "/\d+/";
        $obj->minval = $minval;
        $obj->maxval= $maxval;
        $obj->type=Validater::INPUT_DIG;
        return $obj;
    }/*}}}*/
    public function _scopeValidate($value,$must,$name=null)
    {/*{{{*/

        $errmsg= $this->errmsg;
        if(! $must && ($value==null || $value==""))
            return $value;
        if( $name != null)
            $errmsg = " $name  $errmsg";
        if( $must && ($value==null || $value == ""))
                throw  new UserInputException($errmsg);
        $regex= $this->regex;
        if(!empty($regex ))
        {
            if(!preg_match($this->regex,$value))
                throw  new UserInputException($errmsg);
        }
        if( $this->type == Validater::INPUT_DIG)
        {
            if($this->minval !=-1 && $value < $this->minval)
                throw  new UserInputException($errmsg);

            if($this->maxval !=-1 && $value < $this->maxval)
                throw  new UserInputException($errmsg);
        }

        if( $this->type == Validater::INPUT_TXT)
        {
            
            if($this->minlen !=-1 && strlen($value)< $this->minlen)
                throw  new UserInputException($errmsg);

            if($this->maxlen !=-1 && strlen($value) > $this->maxlen)
                throw  new UserInputException($errmsg);

        }
    }/*}}}*/
    public function _optValidate($value,$need,$valopt,$name=null)
    {/*{{{*/

        if( $name == null)
            $this->errmsg = "$name ".$this->errmsg;
        if(! $need && empty($value))
            return $value;
        $value = trim($value);
        foreach($valopt as $item)
        {
            if( $value == $value) 
                return $value;
        }
        throw  new UserInputException($this->errmsg);
    }/*}}}*/


    public function callValidate($value)
    {/*{{{*/
        call_user_func($this->validateFun,$value,$this);
    }/*}}}*/


    public function match($argName,$must,$nameDesc=null)
    {/*{{{*/
        $this->argName = $argName;
        $must = $must ? "true":"false";
        if(is_string($nameDesc))
            $nameDesc = "'$nameDesc'";
        if($nameDesc == null)
            $nameDesc = "null";
        $this->validateFun=create_function('$value,$obj', 
            'return $obj->_scopeValidate($value,'."$must , $nameDesc".');' );
        return clone $this;
    }/*}}}*/
    public function optMatch($argName,$must,$valopt,$name=null)
    {/*{{{*/
        $this->argName = $argName;
        $this->validateFun = create_function('$value,$obj', 
            'return $obj->_optValidate($value,'."$must,$valopt,$name".');' );
        return $this;
    }/*}}}*/

    public function limitScope($argName,$must,$nameDesc=null)
    {/*{{{*/
        return $this->match($argName,$must,$nameDesc);
    }/*}}}*/
    public function limitValue($argName,$must,$valopt,$name=null)
    {/*{{{*/
        return $this->optMatch($argName,$must,$valopt,$name);
    }/*}}}*/
}/*}}}*/

abstract class InputChecker extends PropertyObj
{/*{{{*/
    public function __construct()
    {
        $this->setupInputType();
    }
    private $needValidates=array();
    abstract public function setupInputType();
    public function validates($vars,$xcontext,$name,$value)
    {/*{{{*/
        if($vars == null)
        {
            $this->needValidates[$name]->callValidate($value);
        }
        else
        {
            //bind input data to xcontext;
            if($xcontext !=null)
            {
                foreach( $this->needValidates as $argName => $validater)
                {
                    $val=null;
                    if($vars->haveSet($argName))
                        $val = $vars->$argName;
                    $xcontext->$argName = $val;
                }
            }
            foreach( $this->needValidates as $argName => $validater)
            {
                $val=null;
                if($vars->haveSet($argName))
                    $val = $vars->$argName;
                $validater->callValidate($val);
            }
        }
    }/*}}}*/
    public function need($validater)
    {/*{{{*/
        $this->needValidates[$validater->argName]=$validater;
    }/*}}}*/
    
}/*}}}*/
?>
