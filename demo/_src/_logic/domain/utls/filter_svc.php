<?php
class FilterSvc
{
    static $prop = null;

    static public function convFilterData($filterdata,$vars)
    {/*{{{*/
        self::$prop = FilterProp::create(); 
        if(!empty($filterdata))
        foreach($filterdata as $index => $data)
        {
            if($vars->haveSet($data['filter']))
            {
                $value = $vars->$data['filter'];
                if((!is_null($value)&&$value!=="")||($vars->haveSet($data['filterext'])&&$vars->$data['filterext']))
                {
                    switch($data['type'])
                    {/*{{{*/
                    case "STR":
                        self::$prop->$data['filter'] = "? like '%{$value}%'";
                        $filterdata[$index]["value"] = $value;
                        $filterdata[$index]["filter_val"] = $value;
                        break;
                    case "DATE":
                        $begindate = $value;
                        $enddate   = ($vars->haveSet($data['filterext'])&&$vars->$data['filterext'])?$vars->$data['filterext']:$begindate;
                        if($enddate<$begindate) {$tmp=$enddate;$enddate=$begindate;$begindate=$tmp;}

                        $begin     = $begindate." 00:00:00";
                        $end       = $enddate." 23:59:59";
                        self::$prop->$data['filter'] = "'{$begin}'<=? and ?<='{$end}'";
                        $filterdata[$index]["value"] = ($begindate!=$enddate)?$begindate."至".$enddate:$begindate;
                        $filterdata[$index]["filter_val"] = $begindate;
                        $filterdata[$index]["filterext_val"] = $enddate;
                        break;
                    case "NUM":
                        $min = $value;
                        $max = ($vars->haveSet($data["filterext"]))?$vars->$data['filterext']:"";
                        if($min&&$max) {$cond = "'{$min}'<=? and ?<='{$max}'";$desc="[值]>={$min} and [值]<={$max}";}
                        elseif($min)   {$cond = "'{$min}'<=?";$desc="[值]>={$min}";}
                        elseif($max)   {$cond = "?<='{$max}'"; $desc="[值]<={$max}";}
                        else {$cond = "";$desc="";}
                        self::$prop->$data['filter'] = $cond;
                        if($desc)$filterdata[$index]["value"] = $desc;
                        $filterdata[$index]["filter_val"] = $min;
                        $filterdata[$index]["filterext_val"] = $max;
                        break;
                    case "ENUM":
                        self::$prop->$data['filter'] = $value;
                        $filterdata[$index]["value"] = $filterdata[$index]["items"][$value];
                        $filterdata[$index]["filter_val"] = $value;
                        break;
                    case "INT":
                    default:
                        self::$prop->$data['filter'] = $value;
                        $filterdata[$index]["value"] = $value;
                        $filterdata[$index]["filter_val"] = $value;
                        break;
                    }/*}}}*/
                }
            }
        }
        return $filterdata;
    }/*}}}*/

    static public function loseProp($prop,$keepArr=array())
    {/*{{{*/
        $array = $prop->getPropArray();
        foreach($array as $k=>$p)
        {
            if(!in_array($k,$keepArr))
                $prop->remove($k);
        }
        return $prop;
    }/*}}}*/

    static public function loseVarsInData($filterdata,$vars,$keepArr=array())
    {/*{{{*/
        foreach($filterdata as $sgdata)
        {
            if($vars->haveSet($sgdata['filter'])&&!in_array($sgdata['filter'],$keepArr))
            {
                $vars->remove($sgdata['filter']);
            }
        }
        return $vars;
    }/*}}}*/

    static public function loseVarsByPriority($priorityArr,$vars)
    {/*{{{*/
        $foundMatch = false;
        foreach($priorityArr as $pVar)
        {
            $old_pVar = "old_".$pVar;
            if($vars->haveSet($pVar)&&$vars->haveSet($old_pVar)&&$vars->$pVar&&$vars->$pVar!=$vars->$old_pVar)
            {
                $foundMatch = true;
            }
            $vars->$old_careVar = $vars->$careVar;
        }
        foreach($priorityArr as $pVar)
        {
            if(!$foundMatch&&$vars->haveSet($pVar)&&$vars->$pVar)
                $foundMatch = true;
            else
                $vars->remove($pVar);
        }
    }/*}}}*/

    static public function getFilterProp()
    {/*{{{*/
        return self::$prop;
    }/*}}}*/

    static public function reserveFirstChange($vars,$tArr)
    {/*{{{*/
        $changeK = null;
        foreach($tArr as $k)
        {
            if($vars->haveSet($k)&&$vars->$k&&is_null($changeK))
            {
                $old_k = "old_".$k;
                if($vars->haveSet($old_k))
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
                    $vars->remove($k);
                }
            }
        }
        return $vars;
    }/*}}}*/
}
?>
