<?php

function simpleNavInfoDisp($urls)
{/*{{{*/
    $data="";
    for($i=0;$i<count($urls);$i++)
    {
        $name=$urls[$i]["name"];
        $action=$urls[$i]["action"];
        $args=$urls[$i]["args"];
        $url="/Main.php?do=$action";

        if($args!="" && $args!=null)
            $url.="&$args";
        $data.="<a href='$url'>$name</a>&nbsp;&nbsp;&nbsp;";
    }
    return $data;
}/*}}}*/
class ActionRelation
{/*{{{*/
    private $urls_success=array();
    private $urls_failure=array();
    private $urls_self=array();
    private $urls_all=array();
    private static $displayFun = "simpleNavInfoDisp";
    public function __construct()
    {
    }
    static public function actionName()
    {
        return "test";
    }
    static public function regDisplayFun($fun)
    {
        self::$displayFun=$fun;
    }
    public function clearSelf()
    {
        $this->urls_self = array();
    }
    public function clearSuccess()
    {
        $this->urls_success = array();
    }
    public function clearFail()
    {
        $this->urls_failure = array();
    }
    public function clearAll()
    {/*{{{*/
        $this->clearSelf();
        $this->clearSuccess();
        $this->clearFail();
    }/*}}}*/
    public function setSelf($action,$args="",$name="")
    {/*{{{*/
        $this->urls_self[]=array("name"=>$name,"action"=>$action,"args"=>$args);
    }/*}}}*/
    public function add4Success($action,$args="",$name="")
    {/*{{{*/
        $this->addUrl($action,$args,$name,$this->urls_success);
    }/*}}}*/
    public function add4Fail($action,$args="",$name="")
    {/*{{{*/
        $this->addUrl($action,$args,$name,$this->urls_failure);
    }/*}}}*/
    public function add4All($action,$args="",$name="")
    {/*{{{*/
        $this->addUrl($action,$args,$name,$this->urls_all);
    }/*}}}*/
    private function addUrl($action,$args,$name,&$urls)
    {/*{{{*/
        if($name=="")
        {
            $name = $action;
        }
        $urls[]=array("name"=>$name,"action"=>$action,"args"=>$args);
    }/*}}}*/
    public function successNav()
    {/*{{{*/
        $urls = array_merge($this->urls_success,$this->urls_all);
        return $this->getData($urls);
    } /*}}}*/
    public function failureNav()
    {/*{{{*/
        $urls = array_merge($this->urls_failure,$this->urls_all);
        return $this->getData($urls);
    } /*}}}*/
    private function getData($urls)
    {/*{{{*/
        $fun =self::$displayFun;
        return $fun($urls);
    }/*}}}*/
    public function selfNav()
    {/*{{{*/
        return $this->getData($this->urls_self);
    }/*}}}*/
}/*}}}*/
?>
