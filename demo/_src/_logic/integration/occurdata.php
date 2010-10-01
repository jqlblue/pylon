<?php
class OccurDate
{/*{{{*/
    static public function stdDate($time)
    {/*{{{*/
//        $sec = intval($time/86400) * 86400;
        return  date("Y-m-d",$time);
    }/*}}}*/
    static public function stdHourDate($time)
    {/*{{{*/
        return date("Y-m-d H:i:s",$time);
    }/*}}}*/
    static public function simpleDate($time)
    {/*{{{*/
        return date("m/d/Y",$time);
    }/*}}}*/
    static public function isStdDate($date)
    {/*{{{*/
        return preg_match('/\d{4}\-\d{2}\-\d{2}/',$date);
    }/*}}}*/
    static public function format($date)
    {/*{{{*/
        $pattern = "/(\d+\:\d+\:\d+)/i";
        $replacement = "";
        return trim(preg_replace($pattern, $replacement, $date));
    }/*}}}*/
    static public function today()
    {/*{{{*/
        return self::stdDate(time());
    }/*}}}*/
    static public function yesterday()
    {/*{{{*/
        return self::stdDate(time()- 86400);
    }/*}}}*/
    static public function beforeHour($hour)
    {/*{{{*/
        return self::stdHourDate(time()- $hour*3600);
    }/*}}}*/
    static public function  dayBefore($num,$date=null)
    {/*{{{*/
        $dateline = is_null($date)? time():strtotime($date);
        return self::stdDate($dateline - 86400 * $num);
    }/*}}}*/ 
    static public function  dayAfter($num,$date=null)
    {/*{{{*/
        $dateline = is_null($date)? time():strtotime($date);
        return self::stdDate($dateline +  86400 * $num);
    }/*}}}*/
    static public function  cutWeeks($begin,$end)
    {/*{{{*/
        $weeks   = array();
        $d = date("w",strtotime($begin));
        $cur = self::dayBefore($d,$begin); 
        while($cur<=$end)
        {
            $weeks[] = $cur;
            $cur = self::dayAfter(7,$cur); 
        }
        return $weeks;
    }/*}}}*/
    static public function dayCompare($begin,$end)
    {/*{{{*/
        $begin = strtotime($begin);
        $end = strtotime($end);
        $begin   = mktime(0,0,0,date('m',$begin),date('d',$begin),date("Y",$begin));
        $end     = mktime(0,0,0,date('m',$end),date('d',$end),date("Y",$end));
        $haveDay = ($end-$begin)/86400; 
        return $haveDay;
    }/*}}}*/
    static public function isInScope($begin,$end,$checktime)
    {/*{{{*/
        $begin = strtotime($begin);
        $end   = strtotime($end);
        $checktime = strtotime($checktime);
        if($checktime >= $begin && $checktime <= $end)
            return true;
        else
            return false;
    }/*}}}*/
    static public function intersect($fstBeg,$fstEnd,$secBeg,$secEnd)
    {
        $beg = self::dayCompare($fstBeg ,$secBeg) > 0 ? $secBeg : $fstBeg;
        $end = self::dayCompare($fstEnd ,$secEnd) > 0 ? $fstEnd: $secEnd;
        if(self::dayCompare($beg,$end) > 0 ) return array($beg,$end);
        return array();
    }
}/*}}}*/
