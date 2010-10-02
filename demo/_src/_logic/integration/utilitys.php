<?php
class StrUtls
{/*{{{*/
    static function cutStr($string,$length,$dot="")
    {/*{{{*/
        $strcut = '';
        $n = $tn = $noc = 0;
        while($n < strlen($string))
        {
            $t = ord($string[$n]);
            if($t == 9 || $t == 10 || (32 <= $t && $t <= 126)) {
                $tn = 1; $n++; $noc++;
            } elseif(194 <= $t && $t <= 223) {
                $tn = 2; $n += 2; $noc += 2;
            } elseif(224 <= $t && $t <= 239) {
                $tn = 3; $n += 3; $noc += 2;
            } elseif(240 <= $t && $t <= 247) {
                $tn = 4; $n += 4; $noc += 2;
            } elseif(248 <= $t && $t <= 251) {
                $tn = 5; $n += 5; $noc += 2;
            } elseif($t == 252 || $t == 253) {
                $tn = 6; $n += 6; $noc += 2;
            } else {
                $n++;
            }
            if($noc >= $length) {
                break;
            }
        }
        if($noc > $length) {
            $n -= $tn;
        }
        $strcut = substr($string, 0, $n);
        return $strcut.$dot;
    }/*}}}*/
}/*}}}*/

class ArrayCombiner
{/*{{{*/
    private $joinFlag;
    public function __construct($flag=',')
    {
        $this->joinFlag = $flag;
    }
    public function combin()
    {/*{{{*/
        $args =func_get_args();
        $cnt= count($args);
        if($cnt == 0 ) return array();
        if($cnt == 1 ) return $args[0];
        if($cnt >= 2 )
        {
            $A = $args[0];
            $B = $args[1];
            $C = array();
            foreach($A as $a)
            {
                foreach($B as $b)
                {
                    $C[] = $a . $this->joinFlag . $b;
                }
            }
            array_shift($args);
            array_shift($args);
            array_unshift($args,$C);
            return call_user_func_array(array($this,"combin"),$args);
        }
    }/*}}}*/
}/*}}}*/

class UtlsCaller
{/*{{{*/
    private $calledFun=null;
    private $debug=false;
    public function __construct($fun,$debug=false)
    {/*{{{*/
        $this->calledFun = $fun;
        $this->debug     = $debug;
    }/*}}}*/
    public function errLogedExec()
    {/*{{{*/
        $args = func_get_args();
        try
        {/*{{{*/
            $log=null;
            if($this->debug)
            {
                $executer =  ObjectFinder::find('SQLExecuter');
                $log = DebugUtls::sqlLogEnable(); 
            }
            return call_user_func_array($this->calledFun,$args);
        }/*}}}*/
        catch( Exception $e)
        {/*{{{*/

            if($e instanceof BizException )
            {
                $loger = LogerManager::getBizErrLoger();
            }
            else if ($e instanceof SysException )
            {
                $loger = LogerManager::getSysErrLoger();
            }
            else if ($e instanceof LogicException)
            {
                $loger = LogerManager::getDebugLoger();
            }
            else 
            {
                $loger = LogerManager::getDebugLoger();
            }

            $errorMsg = $e->getMessage();
            $errorPos = $e->getTraceAsString();
            $loger->err($errorMsg);
            $loger->err($errorPos);
            echo  "$errorMsg\n";
            echo  "$errorPos\n";

            $msgs = DiagnoseMonitor::msgs();
            foreach($msgs as $msg)
            {
                echo "$msg\n";
                $loger->err("dc:$msg");
            }
        }/*}}}*/
    }/*}}}*/
}/*}}}*/

class DateDiff
{/*{{{*/

    public static function textDiff($ts1, $ts2="")
    {/*{{{*/
        $ts2 = !$ts2 ? date('Y-m-d H:i:s') : $ts2;
        return self::date_diff_as_text($ts1, $ts2);
    }/*}}}*/

    public static function textDiffHtml($ts1, $ts2="", $class="help")
    {/*{{{*/
        $ts2 = !$ts2 ? date('Y-m-d H:i:s') : $ts2;
        $str  = self::date_diff_as_text($ts1, $ts2);
        $time = is_numeric($ts1) ? date('Y-m-d H:i:s', $ts1) : $ts1;
        return "<span title='$time'class='$class'>$str</span>";
    }/*}}}*/

    /**
     * date_diff_as_text() 实用方法，把时间显示为友好值
     * param : int starttime, int endtime
     * return : string result
     */
    private static function date_diff_as_text($ts1, $ts2) 
    {/*{{{*/
        /*
         *     $ts1 = "2007-01-05 10:30:45";
         *     $ts2 = "2007-01-06 10:31:46";
         *     echo date_diff_as_text($ts1, $ts2);
         *     => 1天前
         *              
         */
        if(!$ts1 or !$ts2) return "未知时间";
        if(!is_numeric($ts1))$ts1 = strtotime($ts1);
        if(!is_numeric($ts2))$ts2 = strtotime($ts2);
        $diff = abs($ts1-$ts2);
        $sec_min = 60;
        $sec_hour = $sec_min*60;
        $sec_dias = $sec_hour*24;
        $sec_week = $sec_dias*7;
        $sec_month = $sec_dias*30;
        $sec_year = $sec_month*12;
        $years = intval($diff/$sec_year);
        $monthes = intval($diff/$sec_month);
        $weeks = intval($diff/$sec_week);
        $dias = intval($diff/$sec_dias);
        $hours = intval($diff/$sec_hour) %24;
        $minutes = intval($diff/$sec_min) %60;
        $seconds = $diff%60;
        if ($years > 0) {
            return $result = $years ." 年前";
        }
        if ($monthes > 0) {
            return $result = $monthes ." 个月前";
        }
        if ($weeks > 0) {
            return $result = $weeks ." 周前";
        }
        if ($dias > 0) {
            return $result = "$dias 天前";
            if ($dias > 1) {
                $result.= "s";
            }
        }
        if ($hours > 0) {
            return $result.= " $hours 小时前";
            if ($hours > 1) {
                $result.= "s";
            }
        }
        if ($minutes > 0) {
            return $result.= " $minutes 分钟前";
            if ($minutes > 1) {
                $result.= "s";
            }
        }
        if ($seconds > 0) {
            return $result.= " $seconds 秒前";
            if ($seconds > 1) {
                $result.= "s";
            }
        }
        return "刚才";
        $result = explode(" ", $result);
        if (count($result) > 2) {
            end($result);
            $key1 = key($result);
            prev($result);
            $key2 = key($result);
            $aux = $result[$key2];
            $aux.= " " . $result[$key1];
            unset($result[$key1]);
            unset($result[$key2]);
            $result = implode(" ", $result);
            $result.= " y $aux";
        } else {
            $result = implode(" ", $result);
        }
        return $result;
    }/*}}}*/

}/*}}}*/
