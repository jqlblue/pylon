<?php
class Operatetype
{/*{{{*/
    const  ADD='add';
    const  MOD='mod';
    static public function nameOf($v)
    {
        $names[self::ADD] = "添加";
        $names[self::MOD] = "修改";
        return $names[$v];
    }
}/*}}}*/
class Tasktype
{/*{{{*/
    const SITE    = 'site';
    const ID_CARD = 'idcard';
    const PAY_ACC = 'payacc';
    const CONTACT = 'contact';
    const PAYMONEY = 'paymoney';
    static public function nameOf($v)
    {/*{{{*/
        $names[self::SITE] = "站点";
        $names[self::ID_CARD] = "身份信息";
        $names[self::PAY_ACC] = "支付信息";
        $names[self::CONTACT] = "联系信息";
        return $names[$v];
    }/*}}}*/
}/*}}}*/
class Task extends Entity
{/*{{{*/ 
    const ST_INIT     = 'init';
    const ST_AUDITING = 'auditing';
    const ST_AUDITED  = 'audited';
    const RT_ABORT    = 'abort'; //任务放弃,取消任务
    const RT_PASS     = 'pass';
    const RT_REFUSE   = 'refuse';
    const RT_UNKNOW   = 'unknow';
    static function createByBiz($otype,$operate,$title,$data,$userid,$refid=0,$search="",$st=self::ST_INIT,$note="")
    {/*{{{*/
        $obj = new Task(EntityID::create());
        $obj->otype    = $otype;
        $obj->operate = $operate;
        $obj->title   = $title;
        $obj->data    = serialize($data);
        $obj->userid  = $userid;
        $obj->refid   = $refid;
        $obj->search  = $search;
        $obj->status  = $st;
        $obj->result  = self::RT_UNKNOW;
        $obj->note    = $note;
        return Entity::createByBiz($obj);
    }/*}}}*/
    public function getData()
    {/*{{{*/
        return unserialize($this->data);
    }/*}}}*/
    public function submitAudit()
    {/*{{{*/
        $this->status = self::ST_AUDITING;
    }/*}}}*/
    public function compliteAudit($result,$note="")
    {/*{{{*/
        $this->result=$result;
        $this->note = $note;
        $this->status=self::ST_AUDITED;
    }/*}}}*/
    public function nameOfStatus($status)
    {/*{{{*/
        $names[self::ST_INIT]     = "初始状态";
        $names[self::ST_AUDITING] = "处理中";
        $names[self::ST_AUDITED]  = "处理完毕";
        return $names[$status];
    }/*}}}*/
    public function nameOfResult($v)
    {/*{{{*/
        $names[self::RT_PASS]    = "通过";
        $names[self::RT_REFUSE]  = "拒绝";
        $names[self::RT_AUDITED] = "已取消";
        return $names[$v];
    }/*}}}*/
}/*}}}*/

class TaskPayMoney
{
    //所有钱款参数单位均为元
    const OTYPE = Tasktype::PAYMONEY;
    static function listAuditing($pageObj)
    {/*{{{*/
        $dq = Dquery::ins();
        $otype = TaskPayMoney::OTYPE;
        $res = $dq->list_task_by_otype_status($otype,TASK::ST_AUDITING,$pageObj,"id","desc");
        if($res)
            array_walk($res, create_function('&$item,$key','$item["data"] = unserialize($item["data"]);
        $item["data"]["summoney"] = MoneyUtls::fen2yuan($item["data"]["summoney"]);
        $item["data"]["gagemoney"]= MoneyUtls::fen2yuan($item["data"]["gagemoney"]);
        '));
        return $res;
    }/*}}}*/
    static function listAuditingBySowner($sownerid)
    {/*{{{*/
        $dq = Dquery::ins();
        $otype = TaskPayMoney::OTYPE;
        $res = $dq->list_task_by_otype_status_userid($otype,TASK::ST_AUDITING,$sownerid);
        if($res)
            array_walk($res, create_function('&$item,$key','$item["data"] = unserialize($item["data"]);
        $item["data"]["summoney"] = MoneyUtls::fen2yuan($item["data"]["summoney"]);
        $item["data"]["gagemoney"]= MoneyUtls::fen2yuan($item["data"]["gagemoney"]);
        '));
        return $res;
    }/*}}}*/ 
    static function listAudited($pageObj)
    {/*{{{*/
        $dq = Dquery::ins();
        $otype = TaskPayMoney::OTYPE;
        $res = $dq->list_task_by_otype_status($otype,TASK::ST_AUDITED,$pageObj,"updatetime","desc");
        if($res)
            array_walk($res, create_function('&$item,$key','$item["data"] = unserialize($item["data"]);
        $item["data"]["summoney"] = MoneyUtls::fen2yuan($item["data"]["summoney"]);
        $item["data"]["gagemoney"]= MoneyUtls::fen2yuan($item["data"]["gagemoney"]);
        $item["data"]["taxmoney"] = MoneyUtls::fen2yuan($item["data"]["taxmoney"]);
        '));
        return $res;
    }/*}}}*/
    static function listAuditedBySowner($sownerid)
    {/*{{{*/
        $dq = Dquery::ins();
        $otype = TaskPayMoney::OTYPE;
        $res = $dq->list_task_by_otype_status_userid($otype,TASK::ST_AUDITED,$sownerid);
        if($res)
            array_walk($res, create_function('&$item,$key','$item["data"] = unserialize($item["data"]);
        $item["data"]["summoney"] = MoneyUtls::fen2yuan($item["data"]["summoney"]);
        $item["data"]["gagemoney"]= MoneyUtls::fen2yuan($item["data"]["gagemoney"]);
        $item["data"]["taxmoney"] = MoneyUtls::fen2yuan($item["data"]["taxmoney"]);
        '));
        return $res;
    }/*}}}*/
    static function findByTransidSownerid($transid,$sownerid)
    {/*{{{*/
        $res = DaoFinder::query('AcctQuery')->getPaymoneyTaskByTransidSownerid($transid,$sownerid);
        $res['data'] = unserialize($res['data']);
        $res['data']['summoney'] = MoneyUtls::fen2yuan($res['data']['summoney']);
        $res['data']['gagemoney'] = MoneyUtls::fen2yuan($res['data']['gagemoney']);
        $res['data']['taxmoney'] = MoneyUtls::fen2yuan($res['data']['taxmoney']);
        return $res;
    }/*}}}*/
    static function isExistByTransidSownerid($transid,$sownerid)
    {/*{{{*/
        $res = DaoFinder::query('AcctQuery')->getPaymoneyTaskByTransidSownerid($transid,$sownerid);
        return ($res)?true:false;
    }/*}}}*/
    static function findByTaskidSownerid($taskid,$sownerid)
    {/*{{{*/ 
        $dq = Dquery::ins();
        $otype = TaskPayMoney::OTYPE;
        $res = $dq->get_task_by_otype_id_userid($otype,$taskid,$sownerid);
        $res["data"] = unserialize($res["data"]);
        $res["data"]["summoney"] = MoneyUtls::fen2yuan($res["data"]["summoney"]);
        $res["data"]["gagemoney"]= MoneyUtls::fen2yuan($res["data"]["gagemoney"]);
        $res["data"]["taxmoney"] = MoneyUtls::fen2yuan($res["data"]["taxmoney"]);
        return $res;
    }/*}}}*/
    static function addBySowner($sownerid,$transids,$summoney,$gagemoney,$gagenote)
    {/*{{{*/
        $search = implode(",",$transids).",";
        $data = array("setTransids"=>$transids,
                      "payTransid"=>0,
                      "summoney"=>MoneyUtls::yuan2fen($summoney),
                      "gagemoney"=>MoneyUtls::yuan2fen($gagemoney),
                      "taxmoney"=>0
                      );
        $userid = $sownerid;
        $note   = $gagenote;
        $title  = "支付任务 sownerid:{$sownerid} 支付总额:{$summoney}元 暂留金:{$gagemoney}元";
        $otype  = Tasktype::PAYMONEY;
        $operate = "none";
        return Task::createByBiz($otype,$operate,$title,$data,$userid,0,$search,Task::ST_AUDITING,$note); 
    }/*}}}*/  
    static function auditedOnTaskid($taskid,$taxmoney,$gagenote="")
    {/*{{{*/
        $taxmoney = MoneyUtls::yuan2fen($taxmoney);
        
        $dda   = DDA::ins();
        $otype = TaskPayMoney::OTYPE;
        $task  = $dda->get_task_by_otype_status_id($otype,TASK::ST_AUDITING,$taskid); 
        $taskdata = unserialize($task->data);
        $transids = $taskdata['setTransids'];
        $gagemoney= $taskdata['gagemoney'];
        $date  = OccurDate::today();
        $payTransid = AcctEvents::adminPayMoney($task->userid,0,null,$transids,$gagemoney,$taxmoney,$date);       
        $taskdata['payTransid'] = $payTransid;
        $taskdata['taxmoney']   = $taxmoney;
        $task->data = serialize($taskdata);
        $task->status = Task::ST_AUDITED;
        $task->search = $task->search.$payTransid.",";
        $task->note = $gagenote;
    }/*}}}*/
    static function cancelTask($taskid)
    {/*{{{*/
        Dwriter::ins()->del_task_by_id($taskid);
    }/*}}}*/
}
