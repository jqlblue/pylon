<?php
interface IRule
{/*{{{*/
    public function validate($v,&$msg);
    public function needContinue();
}/*}}}*/
class RuleBase 
{/*{{{*/
    public function needContinue()
    {/*{{{*/
        return true;
    }/*}}}*/
}/*}}}*/

class RegexRule  extends RuleBase implements IRule
{/*{{{*/
    public $regex;
    public $msg;
    public function __construct($reg,$msg)
    {/*{{{*/
        DBC::requireNotNull($msg,"msg 不能为空");
        $this->regex= $reg;
        $this->msg  = $msg;
    }/*}}}*/
    public function validate($v,&$msg)
    {/*{{{*/
        if(preg_match($this->regex,$v)) 
        {
            return true;
        }
        $msg = $this->msg;
        return false;
    }/*}}}*/

}/*}}}*/
class IllegalCharRule  extends RuleBase
{/*{{{*/
    public function validate($v,&$msg)
    {/*{{{*/
        if(preg_match("/^[^\'\<\>]*+$/",$v)) 
        {
            return true;
        }
        $msg="[name] 包括非法字符!";
        return false;
    }/*}}}*/
}/*}}}*/
class MustValueRule implements IRule
{/*{{{*/
    public $must;
    public $contu = true;
    public function __construct($must)
    {/*{{{*/
        $this->must =$must;
    }/*}}}*/
    public function validate($v,&$msg)
    {/*{{{*/
        if($this->must)
        {
            if(!is_null($v)&&$v!="") return true;
        }
        else
        {
            if(is_null($v)||$v=="")
            {
                $this->contu=false;
                return true;
            }
            else
            {
                $this->contu=true;
                return true;
            }
        }
        $msg = "请输入\"[name]\"";
        return false;
    }/*}}}*/
    public function needContinue()
    {/*{{{*/
        return $this->contu;
    }/*}}}*/
}/*}}}*/
class DigitScopeRule extends RuleBase implements IRule
{/*{{{*/
    public $max;
    public $min;

    public function __construct($min,$max)
    {
        $this->max=$max;
        $this->min=$min;
    }
    public function validate($v,&$msg)
    {
        if($v >= $this->min && $v <= $this->max)
            return true;
        $msg = "[name] 应在{$this->min}到{$this->max}之间";
        return false;
    }
}/*}}}*/
class LengthRule extends RuleBase implements IRule
{/*{{{*/
    public $max;
    public $min;
    public function __construct($min,$max)
    {
        $this->max=$max;
        $this->min=$min;
    }
    public function validate($v,&$msg)
    {
        $len= strlen($v);
        if($len >= $this->min && $len <= $this->max)
            return true;
        $hzlen=round($len/3,1);
        $hzmax=intval($this->max/3);
        $msg = "[name] 长度限定:{$this->min}-{$this->max}(字符)";
        return false;
    }
}/*}}}*/
class CombinRule extends RuleBase implements IRule
{/*{{{*/
    public $rules;
    public $msg;
    public function __construct($rules,$msg=null)
    {/*{{{*/
        DBC::requireTrue(is_array($rules));
        $this->rules = $rules;
        $this->msg   = $msg;
    }/*}}}*/
    public function validate($v,&$msg)
    {/*{{{*/
        foreach($this->rules as $r)
        {
            if(!$r->validate($v,$msg))
            {
                if($this->msg !=null ) $msg=$this->msg;
                return false;
            }
            if(!$r->needContinue()) return true;
        }
        return true;
    }/*}}}*/
}/*}}}*/

class InputRuleLib
{/*{{{*/
    static $rules=array();
    
    static public function register($name,$rule)
    {/*{{{*/
        self::$rules[$name]  = $rule;
    }/*}}}*/
    static public function register2($name,$rule1,$rule2)
    {/*{{{*/
        self::$rules[$name] = new CombinRule( array($rule1,$rule2));
    }/*}}}*/
    static public function  setup()
    {/*{{{*/
        self::register("#email",new RegexRule( '/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/',
            '[name] 格式错误!例如: "user@gmail.com"'));
        self::register("#post",new RegexRule( '/^\d{6}$/','[name]邮编应是六位数字!'));
        self::register("#date",new RegexRule( '/^\d{4}-\d{2}-\d{2}$/','[name]错误! 例如:"2007-10-10"'));
        self::register("#url",new RegexRule( '/^https?:\/\/[A-Za-z0-9]+\.[A-Za-z0-9]+[\/=\?%\-&_~`@[\]\':+!]*([^\"\"])*$/',
            '[name]格式错误! 例如:"http://www.g.cn" '));
        self::register("#digit",new RegexRule( '/^\d{1,13}$/','[name]要求是数字1-13位！'));
        self::register("#idcard",new RegexRule( '/^\d{15}(\d{2}[A-Za-z0-9])?$/','[name]不是身份证号'));
        self::register("#person",new LengthRule(3,30 ));
        self::register("#money",new RegexRule('/^\d+(\.\d{2})?$/','[name]错误 例如56.00 或 56'));
        self::register("#phone",new RegexRule('/^((\(\d{3}\))|(\d{3}\-))?(\(0\d{2,3}\)|0\d{2,3}-)?[1-9]\d{6,10}$/','[name]不是电话号码'));
        self::register("#mobile",new RegexRule('/^((\(\d{3}\))|(\d{3}\-))?\d{11}$/','[name] 不是11位移动电话'));
        self::register("#QQ",new RegexRule('/^[1-9]\d{4,12}$/','QQ为4以上的数字'));
        self::register("#ip",new RegexRule('/\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/','[name] 格式错误!  例如: "192.168.1.1"'));
        self::register("#winpath",new RegexRule('/^\w\:\\.*/','[name] 格式错误! 例如: "c:\windows"'));
        self::register("#nospace",new RegexRule('/^\S+$/','[name]包含有空格或TAB'));
        self::register("#chinese",new RegexRule('/^([\xE4-\xE9][\x80-\xBF][\x80-\xBF])+$/','[name] 必须为中文字符'));
        self::register("#english",new RegexRule('/^[A-Za-z]+$/','[name] 必须为英文'));
        self::register("#objname",new RegexRule("/^[^\"']{2,15}$/",'[name] 必需是1~15个字，并且不包括非法字符'));
        self::register("#note"   ,new RegexRule("/^[^\"']{1,255}$/",'[name] 必需是1~255个字，并且不包括非法字符'));
    }/*}}}*/
    static public function ref($name)
    {/*{{{*/
        $v = self::$rules[$name];
        if($v ==  null)
        {
            $msgs = Prompt::recommend($name,array_keys(self::$rules));

            $info= "<br> ** no [$name]  input validate conf !, it maybe list:  <br>\n";
            $index=$_SERVER['PHP_SELF'];
            foreach($msgs as $key)
            {
                $info .= "<font color='blue'><b>[$key]</b></font><br>\n";
            }
            DBC::unImplement($info);
        }
        return  $v;
    }/*}}}*/
}/*}}}*/
class IValidator
{/*{{{*/
    public $name;
    public $msg;
    public $rules;
    public $dispname;
    public function __construct($name,$dispname,$must,$msg)
    {/*{{{*/
        $this->rules=array();
        $this->rules[] = new MustValueRule($must);
        $this->msg = $msg;
        $this->dispname = $dispname;
    }/*}}}*/
    public function useRule($ruleName,$illeage=true)
    {/*{{{*/
        $rule = InputRuleLib::ref($ruleName);
        $this->rules[] =     $rule;
        if($illeage)
            $this->rules[] =     new IllegalCharRule();
        return $this;
    }/*}}}*/
    public function msg($msg)
    {
        $this->msg =$msg;
    }
    public function validate($value)
    {/*{{{*/
        $msg="";
        $rule = $this->getRule();
        if(!$rule->validate($value,$msg))
        {
            if(!is_null($this->msg)) 
                $msg= $this->msg;
            else if (!is_null($this->dispname))
                $msg=str_replace('[name]',"{$this->dispname}",$msg);
            throw new UserInputException($msg);
        }
    }/*}}}*/
    private function getRule()
    {/*{{{*/
        return new CombinRule($this->rules ,$this->msg);        
    }/*}}}*/
}/*}}}*/
class BatchValidater
{/*{{{*/
    public $inputVars;
    public $xcontext;
    public $confCls;
    public $confs;
    public function __construct($vars,$xcontext,$confcls)
    {/*{{{*/
        $this->inputVars = $vars;
        $this->xcontext  = $xcontext;
        $this->confCls = $confcls;
        $this->confs    = new InputConfs();
    }/*}}}*/
    public function validateByConf($confFun)
    {/*{{{*/
        ValidateUtls::batchValidate($this->confs,$this->confCls,$confFun,$this->inputVars,$this->xcontext);
    }/*}}}*/
    public function validateByConfFun($confFun)
    {/*{{{*/
        ValidateUtls::batchValidate2($this->confs,$confFun,$this->inputVars,$this->xcontext);
    }/*}}}*/
}/*}}}*/
class ValidateUtls
{/*{{{*/
    static public function batchValidate($conf,$obj,$method,$vars,$xcontext=null)
    {/*{{{*/
        if(!is_null($xcontext))
            $xcontext->mergeArray($vars->getPropArray());
        call_user_func(array($obj,$method),$conf);
        $conf->batchValidate($vars);
    }/*}}}*/

    static public function batchValidate2($conf,$conf_init,$vars,$xcontext=null)
    {/*{{{*/
        if(!is_null($xcontext))
            $xcontext->mergeArray($vars->getPropArray());
        call_user_func($conf_init,$conf);
        $conf->batchValidate($vars);
    }/*}}}*/

    static public function validate($conf,$callback,$name,$value)
    {/*{{{*/
        call_user_func($callback,$conf);
        $conf->validate($name,$value);
    }/*}}}*/
}/*}}}*/
class InputConfs
{/*{{{*/
    public $inputs=array();
    public $matchstg;
    public $ignoreNames=array();
    const  MATCH_ALL=1;
    const  MATCH_GIVE=2;
    public function __construct($libsetupcode='AppInputRuleLib::setup();')
    {/*{{{*/
        $fun = create_function('',$libsetupcode);
        $fun();
        $this->matchstg = self::MATCH_GIVE;
    }/*}}}*/
    public function setMatchStg($stg)
    {
        $this->matchstg = $stg;
    }
    public function addIgnoreNames($names)
    {
        $this->ignoreNames = $names;
    }
    public function input($name,$dispname=null,$must=true,$msg=null)
    {/*{{{*/
        $this->inputs[$name] = new IValidator($name,$dispname,$must,$msg);
        $i= $this->inputs[$name];
        return $i;
    }/*}}}*/
    public function batchValidate($vars)
    {/*{{{*/
        $msg="";
        foreach($this->inputs as $key => $validater)
        {
            if($vars->haveSet($key))
                $validater->validate($vars->$key);
            else
                $validater->validate(null);
        }
    }/*}}}*/
    public function validate($name,$value)
    {/*{{{*/
        if(isset($this->inputs[$name]))
        {/*{{{*/
            $validater = $this->inputs[$name];
            $validater->validate($value);
        }/*}}}*/
        else
        {
            if (isset($this->ignoreNames[$name])) return ;

            if (isset($this->inputs['*']))
            {
                $name = '*';
                $validater = $this->inputs[$name];
                $validater->validate($value);
            }
            else if( $this->matchstg ==  self::MATCH_ALL)
            {
                $msgs = Prompt::recommend($name,array_keys($this->inputs));
                $info= "<br> ** no [$name]  input validate rule  !, it maybe list:  <br>\n";
                $index=$_SERVER['PHP_SELF'];
                foreach($msgs as $key)
                {
                    $info .= "<font color='blue'><b>[$key]</b></font><br>\n";
                }
                DBC::unImplement($info);
            }
        }

    }/*}}}*/
}/*}}}*/


class InputCheckSvc
{/*{{{*/
    static public function getinput_value ($name)
    {/*{{{*/
        if(isset($_POST[$name]))  return  $_POST[$name];
        if(isset($_GET[$name]))  return  $_GET[$name];
        return null;
    }/*}}}*/
    static public function get_conf_fun($cls,$action,$form="")
    {/*{{{*/
        $conf= "_validate_conf"; //简单
        $conf_method = null;
        if(method_exists($cls,$conf))
        {
            $conf_method = array($cls,$conf);
        }
        return $conf_method;
    }/*}}}*/
    public function validate($loader)
    {/*{{{*/
        $action = self::getinput_value('action');
        $name   = self::getinput_value('name');
        $value  = self::getinput_value('value');
        $form   = self::getinput_value('form');
        $rule   = self::getinput_value('rule');
        $orule  = self::getinput_value('orule');
        $found = $loader->_find($action);
        $conf = new ActionConf($found);
        $cls =  $conf->getCls();

        $REV['RET']="OK";
        try
        {
            if ($rule != null)
            {
                $validator = new IValidator($name,"",true,null);
                $validator->useRule($rule);
                $validator->validate($value);
            }
            elseif($orule)
            {
                if($value&&$value!="")
                {
                    $validator = new IValidator($name,"",true,null);
                    $validator->useRule($orule);
                    $validator->validate($value);
                }
            }
            else
            {
                $conf = new InputConfs();
                $method = InputCheckSvc::get_conf_fun($cls,$action,$form);
                if($method != null)
                    ValidateUtls::validate($conf,$method,$name,$value);
            }
        } 
        catch( UserInputException $e)
        {
            $REV['RET']="ERR";
            $REV['ERRMSG']=$e->getMessage();
        }
        catch(Exception $e)
        {
            $REV['RET']="ERR";
            $REV['ERRMSG']=$e->getMessage(). $e->getTraceAsString();
        }
        return $REV;
    }/*}}}*/

    public function validate_bak($loader)
    {/*{{{*/
        $action = self::getinput_value('action');
        $name   = self::getinput_value('name');
        $value  = self::getinput_value('value');
        $form   = self::getinput_value('form');
        $rule   = self::getinput_value('rule');
        $orule  = self::getinput_value('orule');
        $found = $loader->find($action);
        $cls = $found->getActionCls();

        $REV['RET']="OK";
        try
        {
            if ($rule != null)
            {
                $validator = new IValidator($name,"",true,null);
                $validator->useRule($rule);
                $validator->validate($value);
            }
            elseif($orule&&$value&&$value!="")
            {
                $validator = new IValidator($name,"",true,null);
                $validator->useRule($orule);
                $validator->validate($value);
            }
            else
            {
                $conf = new InputConfs();
                $method = InputCheckSvc::get_conf_fun($cls,$action,$form);
                if($method != null)
                    ValidateUtls::validate($conf,$method,$name,$value);
            }
        }
        catch( UserInputException $e)
        {
            $REV['RET']="ERR";
            $REV['ERRMSG']=$e->getMessage();
        }
        catch(Exception $e)
        {
            $REV['RET']="ERR";
            $REV['ERRMSG']=$e->getMessage(). $e->getTraceAsString();
        }
        return $REV;
    }/*}}}*/
}/*}}}*/
?>
