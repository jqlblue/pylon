<?php
class ActionUtls
{/*{{{*/
    static public function logError($e,$loger)
    {/*{{{*/
        $errorMsg = $e->getMessage();
        $errorPos = $e->getTraceAsString();
        $loger->err($errorMsg);
        $loger->err($errorPos);
        return $errorMsg;
    }/*}}}*/
    static public function getReqData($request)
    {/*{{{*/
        $uri = '?do='.$request->getAttribute('ACTION_DO_PATH');
        $getVars = $request->getGetVars();
        $postVars = $request->getPostVars();

        $data[EventMachine::URI]=$uri;
        $data[EventMachine::GETVARS]=$getVars;
        $data[EventMachine::POSTVARS]=$postVars;
        return $data;

    }/*}}}*/
    static public function needNewPage($vars)
    {/*{{{*/
        $snap= clone $vars;
        $snap->pageObj="";
        $snap->pageno="";
        $snap->snapKey="";
        $md5 = md5(serialize($snap));
        if( $vars->have('snapKey') && $md5== $vars->snapKey)
            return false;
        $vars->snapKey=$md5;
        return true;
    }/*}}}*/
    static public function dateDiff($dateTimeBegin,$dateTimeEnd,$interval="d")
    {/*{{{*/
        $dateTimeBegin=strtotime($dateTimeBegin);
        if($dateTimeBegin === -1)
        {
            return("..begin date Invalid");
        }

        $dateTimeEnd=strtotime($dateTimeEnd);
        if($dateTimeEnd === -1)
        {
            return("..end date Invalid");
        }

        $dif=$dateTimeEnd - $dateTimeBegin;

        switch($interval)
        {
        case "s"://seconds
            return($dif);

        case "n"://minutes
            return(floor($dif/60)); //60s=1m

        case "h"://hours
            return(floor($dif/3600)); //3600s=1h

        case "d"://days
            return(floor($dif/86400)); //86400s=1d

        case "ww"://Week
            return(floor($dif/604800)); //604800s=1week=1semana

        case "m": //similar result "m" dateDiff Microsoft
            $monthBegin=(date("Y",$dateTimeBegin)*12)+
                date("n",$dateTimeBegin);
            $monthEnd=(date("Y",$dateTimeEnd)*12)+
                date("n",$dateTimeEnd);
            $monthDiff=$monthEnd-$monthBegin;
            return($monthDiff);

        case "yyyy": //similar result "yyyy" dateDiff Microsoft
            return(date("Y",$dateTimeEnd) - date("Y",$dateTimeBegin));

        default:
            return(floor($dif/86400)); //86400s=1d
        }
    }/*}}}*/

    static public function defaultValue($vars,$name,$defaultv)
    {/*{{{*/
        if(!$vars->have($name))
        {
            $vars->$name = $defaultv;
        }
    }/*}}}*/
    static public function dropUseless($vars,$children=array())
    {/*{{{*/
        foreach($children as $child)
        {
            $vars->remove($child);
        }
    }/*}}}*/
    static public function keepUniqueChange($vars,$tArr)
    {/*{{{*/
        $changeK = null;
        foreach($tArr as $k)
        {
            if($vars->have($k)&&$vars->$k&&is_null($changeK))
            {
                $old_k = "old_".$k;
                if($vars->have($old_k))
                {
                    if($vars->$old_k != $vars->$k)
                    {
                        $changeK = $k;
                        $vars->$old_k = $vars->$k;
                    }
                }
                else
                {
                    $changeK = $k;
                    $vars->$old_k = $vars->$k;
                }
            }
        }
        if($changeK)
        {
            foreach($tArr as $k)
            {
                if($k!=$changeK)
                {
                    $old_k = "old_".$k;
                    $vars->remove($k);
                    $vars->remove($old_k);
                }
            }
        }
        return $changeK;
    }/*}}}*/
}/*}}}*/

class UPageCtrl extends PropertyObj
{/*{{{*/
    public function __construct($dataPager)
    {/*{{{*/
        $this->haveLeft  = $dataPager->curPage!=1? true:false;
        $this->haveRight = $dataPager->curPage!= $dataPager->totalPages ? true:false;
        $this->nextPage   = $dataPager->curPage + 1 ;
        $this->prePage   = $dataPager->curPage -1 ;
        $this->total     = $dataPager->totalPages;
        $this->begin= $dataPager->curPage-5 > 0 ? $dataPager->curPage -4 : 1;
        $this->end  = $dataPager->totalPages >  $dataPager->curPage +5 ? $dataPager->curPage + 5  : $dataPager->totalPages;
        $lableList=array();

        for($i=$this->begin;$i<=$this->end;$i++)
        {
            $lableList[]=$i;
        }
        $this->lableList=$lableList;
    }/*}}}*/
}/*}}}*/

class  MiniOPs
{/*{{{*/
    static public function lifeStatus($lifeStatus,$ecls,$eid)
    {/*{{{*/
        $lsValue  = $lifeStatus;
        $lsName   =  LifeStatus::nameOf($lsValue);
        $op = "<font class='sys-miniop' miniop='lifestatus' style='display:none;'>{$ecls},{$eid},{$lsValue},{$lsName}</font>";
        return $op;
    }/*}}}*/
    static public function visible($visiable,$ecls,$eid)
    {/*{{{*/
        $lsValue = $visiable;
        $lsName  = Visible::nameOf($visiable);
        $op = "<font class='sys-miniop' miniop='visible' style='display:none;'>{$ecls},{$eid},{$lsValue},{$lsName}</font>";
//        $op = "<font style='color:#$color;'>$name</font> <a href='index.html?do=setvisible&ecls=$ecls&eid={$eid}' rel='facebox'  >转换</a>";
        return $op;
    }/*}}}*/
    static public function editNote($els,$eid)
    {/*{{{*/
        $op = "<a href=\"index.html?do=upnote&ecls={$els}&eid={$eid}\" rel=\"facebox\">修改备注</a>";
        return $op;
    }/*}}}*/
    static public function upAttribute($value,$ecls,$eid,$attr,$desc=null)
    {/*{{{*/
        $lsTitle = $desc;
        $lsKey   = $attr;
        $lsValue = $value;
        $lsValue = str_replace(",","，",$lsValue);
        $op = "<font class='sys-miniop' miniop='upattr' style='display:none;'>{$ecls},{$eid},{$lsValue},{$lsKey},{$lsTitle}</font>";
        return $op;
    }/*}}}*/
    static public function upObjAttr($obj,$attr,$desc="",$fun=null)
    {/*{{{*/
        $value =  $fun ? call_user_func($fun,$obj->$attr):$obj->$attr;
        return self::upAttribute($value,get_class($obj),$obj->id(),$attr,$desc);
    }/*}}}*/
    static public function upTags($tagsStr,$advid)
    {/*{{{*/
        $op = "<font class='sys-miniop' miniop='tags' advid='{$advid}'>{$tagsStr}</font>";
        return $op;
    }/*}}}*/
    static public function upLabel($label,$ecls,$eid)
    {/*{{{*/
        $label = $label . " 更新";
        $op = "<a href=\"?do=label_up&ecls={$ecls}&eid={$eid}\" rel=\"facebox\">$label</a>";
        return $op;
    }/*}}}*/
}/*}}}*/

class Options
{/*{{{*/
    static public function normalOpts($table)
    {/*{{{*/
        $dq = DQuery::ins();
        $table = strtolower($table);
//        array("lifestatus");
        $prop = FilterProp::create();   
        $prop->lifestatus = "normal";
        $data = DaoFinder::query("{$table}Query")->listByProp($table,null,'*',$prop);
        $opts = array();
        foreach($data as $i)
        {
            $opts[$i['id']] = $i['name'];
        }
        return $opts;
    }/*}}}*/
    static public function allOpts($table)
    {/*{{{*/
        $dq = DQuery::ins();
        $table = strtolower($table);
        $prop = FilterProp::create();   
        $data = DaoFinder::query("{$table}Query")->listByProp($table,null,'*',$prop);
        $opts = array();
        foreach($data as $i)
        {
            $opts[$i['id']] = $i['name'];
        }
        return $opts;
    }/*}}}*/
}/*}}}*/
?>
