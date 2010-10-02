<?php
require_once('uc_config.php');  
require_once('uc_client/client.php');  

class MsgSvc 
{/*{{{*/
    const UC_SENDER_SYSTEM = 0;
    const UC_RECIVER_ALL = 0;
    public static function sendPm($msgto, $subject, $message, $fromid=0)
    {/*{{{*/
        return uc_pm_send($fromuid, $msgto, $subject, $message);
    }/*}}}*/

    public static function getALLPmForUser($uid, $page=1, $pagesize=100, $filter='newpm', $folder = 'inbox')
    {/*{{{*/
        //$folder = 'newbox';outbox|newbox|inbox
        $msglen = 1000;
        return uc_pm_list($uid, $page, $pagesize, $folder, $filter, $msglen);
    }/*}}}*/

    public static function getSysPmForUser($uid, $page=1, $pagesize=100)
    {/*{{{*/
        $folder = 'newbox';
        $filter = 'systempm';//systempm|announcepm|newpm
        $pms = self::getALLPmForUser($uid, $page, $pagesize, $filter, $folder);

        if($pms['count']>0){
            $info_notices = self::filterMsg($pms['data'], 'MsgSvc::filterInfoMsg');
            $acc_notices = self::filterMsg($pms['data'], 'MsgSvc::filterAccMsg');
            $last_flux =  self::filterMsg($pms['data'], 'MsgSvc::filterFluxMsg');
            $last_settle = self::filterMsg($pms['data'], 'MsgSvc::filterSettleMsg');
            
            $info_notices = self::mergePm($info_notices, MsgFilter::RULE_ONLY_LAST);
            $acc_notices = self::mergePm($acc_notices, MsgFilter::RULE_ONLY_LAST);
            $last_flux = self::mergePm($last_flux, MsgFilter::RULE_ONLY_LAST);
            $last_settle = self::mergePm($last_settle, MsgFilter::RULE_SUM_ALL, '/￥(.*)元/u');
            
            $unchange = self::mergePm($pms['data'], MsgFilter::RULE_DEFAULT);
        }
        if($last_flux|| $last_settle|| $info_notices|| $acc_notices|| $unchange)
        $result = array_merge($last_flux, $last_settle, $info_notices, $acc_notices, $unchange);
        $count = count($result);
        return array('data'=>$result, 'count'=>$count);
    }/*}}}*/

    public static function getSysAnnouceForUser($uid, $page=1, $pagesize=100)
    {/*{{{*/
        $filter = 'announcepm';//systempm|announcepm|newpm
        return self::getALLPmForUser($uid, $page, $pagesize, $filter);
    }/*}}}*/

    public static function getPmById($uid, $pmid)
    {/*{{{*/
        return uc_pm_view($uid, $pmid);
    }/*}}}*/

    public static function markRead($pmid)
    {/*{{{*/
        self::getPmById(self::UC_SENDER_SYSTEM, $pmid); 
    }/*}}}*/

    public static function deletePm($uid, $pmid)
    {/*{{{*/
        return uc_pm_delete($uid, 'inbox', $pmid);
    }/*}}}*/

    /*
     * 合并某个用户的消息
     * $filter 过滤器
     */
    public static function mergePm($msg, $filter, $regx=null)
    {/*{{{*/
       return MsgFilter::merge($msg, $filter, $regx); 
    }/*}}}*/
    
    private static function filterInfoMsg($msg)
    {/*{{{*/
        $msg['level'] = 'notify';
        return $msg['subject'] == 'NOTICE INFO';
    }/*}}}*/
 
    private static function filterAccMsg($msg)
    {/*{{{*/
        $msg['level'] = 'notify';
        return $msg['subject'] == 'NOTICE ACCOUNT';
    }/*}}}*/

    private static function filterFluxMsg($msg)
    {/*{{{*/
        return $msg['subject'] == 'LAST FLUX UPDATE TIME';
    }/*}}}*/

    private static function filterSettleMsg($msg)
    {/*{{{*/
        return $msg['subject'] == 'LAST SETTLE TIME';
    }/*}}}*/

    private static function filterMsg(&$msgs, $func, $deleteFlag=true)
    {/*{{{*/
        $arr = array();
        foreach($msgs as $key => $msg){
            if(call_user_func($func, &$msg)){
                $arr[] = $msg;
                unset($msgs[$key]);
            }
        }
        return $arr;
    }/*}}}*/

}/*}}}*/

/*
 * 合并某个用户的消息
 * $filter 过滤器
 *
 * $rule 规则数组
 *   title: 需要处理的类型(短消息标题)
 *   rule:处理方式：last｜sum
 *   regex:当处理方式为sum时，用来过滤求和内容的正则表达式
 *
 * func：
 *   last: 只显示最后一条消息
 *   sum: 对消息内容进行合并
 */

class MsgFilter
{/*{{{*/
    const RULE_ONLY_FIRST = 'first';     //保留第一条
    const RULE_ONLY_LAST = 'last';       //保留最后一条
    const RULE_COMBINE_ALL = 'combine';  //合并通知
    const RULE_SUM_ALL = 'sum';          //合并通知并且累加结果
    const RULE_DEFAULT = 'default';

    public static function merge($msgs, $rule=MsgFilter::RULE_DEFAULT, $regx=null)
    {/*{{{*/
        if(!$msgs)return array();
        array_walk($msgs, 'MsgFilter::debase64');
        return call_user_func("MsgFilter::do_$rule", $msgs, $regx); 
    }/*}}}*/

    private static function do_first($msgs)
    {/*{{{*/
        self::delete($msgs);
        $var = array(array_pop($msgs));
        return $var;
    }/*}}}*/

    private static function do_last($msgs)
    {/*{{{*/
        self::delete($msgs);
        $var = array(array_shift($msgs));
        return $var;
   }/*}}}*/

    private static function do_combine($msgs)
    {/*{{{*/
        return $msgs;
    }/*}}}*/

    private static function do_default($msgs)
    {/*{{{*/
        return $msgs;
    }/*}}}*/

    private static function do_sum($msgs, $regx)
    {/*{{{*/
        $value = 0;
        foreach($msgs as $msg){
           preg_match($regx, $msg['message'], $money);
           $value = $value + $money[1];
           MsgSvc::markRead($msg['pmid']);
        }
        $result = array_shift($msgs);
        $result['message'] = preg_replace($regx, $value, $result['message']).'元';
        return array($result);
    }/*}}}*/

    private static function debase64(&$msg)
    {/*{{{*/
        $msg['message'] = base64_decode($msg['message']);
    }/*}}}*/

    private static function delete($msgs)
    {/*{{{*/
        foreach($msgs as $msg){
            MsgSvc::deletePm($msg['msgtoid'], $msg['pmid']);
        }
    }/*}}}*/
}/*}}}*/

