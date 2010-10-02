<?php
class actionNaviSvc
{/*{{{*/
    public $actionTags;
    public $actionDescs;
    public $tagActions;
    public $curPark = null;
    static $ins = array();
    static $originTag = "origin";
    static $param    = "pa";
    static $parkTags = null;   

    public function __construct()
    {/*{{{*/ 
        include_once(Conf::APP_ROOT.'/appsys/gate/admin/admin_tags_data.php');
        self::$parkTags = Conf::getParkTags();
        $this->actionTags  = $actionTags;
        $this->actionDescs = $actionDescs;
        $this->tagActions  = $tagActions;
    }/*}}}*/
    public function ins($mode="admin")
    {/*{{{*/
        if(self::$ins[$mode] == null)
            self::$ins[$mode] = new actionNaviSvc($mode);
        return self::$ins[$mode];
    }/*}}}*/

    public function opActionsByMytags($mytags)
    {/*{{{*/
        $opActions = array();
        foreach($mytags as $mytag)
        {
            if(in_array($mytag,actionNaviSvc::$parkTags))
            {
                $actionArr = $this->tagActions[$mytag];
                foreach($actionArr as $action) 
                {
                    $opActions[$action] = $this->actionTags[$action];
                }       
            }
        }
        return $opActions;
    }/*}}}*/

    public function getBreadCrumb($curAction,$vars)
    {/*{{{*/
        if(!$vars->haveSet(self::$param))
        {
            $breadCrumb = array($curAction => array(
                'desc' =>$this->actionDescs[$curAction], 
                'param'=>$this->combineParam($curAction,array($curAction)),
                'bcparam'=>$this->combineParam(null,array($curAction))
                ));
        }
        else
        {
            $param = self::$param;
            $paramStr = $vars->$param.",".$curAction;
            $paramArr = array_unique(explode(",",$paramStr));
            foreach($paramArr as $action)
            {
                $breadCrumb[$action] = array(
                    'desc'=>$this->actionDescs[$action],
                    'param'=>$this->combineParam($action,$paramArr),
                    'bcparam'=>$this->combineParam(null,$paramArr)
                    );
            }
        }
        return $breadCrumb;
    }/*}}}*/
    private function combineParam($lastParam=null,$paramArr)
    {/*{{{*/
        $i = 0;
        $paramStr = "";
        foreach($paramArr as $param)
        {
            $paramStr.=($i!=0)?",".$param:$param;
            if($lastParam&&$param == $lastParam)
                break;
            $i++;
        }
        return $paramStr;
    }/*}}}*/

    public function actionDescs()
    {/*{{{*/
        return $this->actionDescs;
    }/*}}}*/
    public function isRealParkTag($tag,$tagsArr)
    {/*{{{*/
        if(in_array($tag,self::$parkTags)&&in_array($tag,$tagsArr))
        {
            $this->curPark = $tag;
            return true;
        }
        else
            return false;
    }/*}}}*/
    public function originActionsByCurPark($curPark=null)
    {/*{{{*/
        if(is_null($curPark)) $curPark = $this->curPark;
        $opActions = array();
        $actionArr = array_intersect($this->tagActions[$curPark],$this->tagActions[self::$originTag]);
        foreach($actionArr as $action) 
        {
            $opActions[$action] = $this->actionTags[$action];
        }       
        return $opActions;
    }/*}}}*/
    public function originActionsByAllPark()
    {/*{{{*/
        $allOpActions = array();
        if(self::$parkTags)
        foreach(self::$parkTags as $curPark)
        {
            if($this->tagActions[$curPark]&&$this->tagActions[self::$originTag])
            {
                $actionArr = array_intersect($this->tagActions[$curPark],$this->tagActions[self::$originTag]);
                if($actionArr)
                    foreach($actionArr as $action)
                    {
                        $allOpActions[$curPark][$action] = $this->actionTags[$action];
                    }
            }       
        }
        return $allOpActions;
    }/*}}}*/
    public function findCurPark($tagsArr=null)
    {/*{{{*/
        if(is_array($tagsArr))
            foreach($tagsArr as $tag)
            {
                if(in_array($tag,self::$parkTags))
                {
                    $this->curPark = $tag;
                    return $tag;
                }
            }
        return null;
    }/*}}}*/
} /*}}}*/

?> 
