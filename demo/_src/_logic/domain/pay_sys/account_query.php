<?php
class AccountQuery extends Query
{/*{{{*/
    public function listUserCoinExpense($userid,$beginTime,$endTime,$page)
    {/*{{{*/
        $cmd = "select b.createtime as paytime, c.* from accounttrans as b ,orders as c 
            where b.dealcls='OrderStore' and b.ownerid=$userid and b.usetag='USER_EXPENSE_COIN4GAME' 
            and b.dealobj=c.id and b.createtime>='$beginTime' and b.createtime<'$endTime' order by b.createtime desc";
        return  $this->listByCmdPage($cmd,$page);
   }/*}}}*/
    public function listUserBuyCoin($userid,$beginTime,$endTime,$page)
    {/*{{{*/
        $cmd = "select b.createtime as paytime, c.* from accounttrans as b ,orders as c 
            where b.dealcls='OrderStore' and b.ownerid=$userid and b.usetag='USER_BUY_COIN' 
            and b.dealobj=c.id and b.createtime>='$beginTime' and b.createtime<'$endTime' order by b.createtime desc";
        return  $this->listByCmdPage($cmd,$page);
    }/*}}}*/
    public function orderlist($userid,$status,$beginTime,$endTime,$page)
    {/*{{{*/
        $where = "";
        if($userid)
            $where = " and c.customer=$userid ";
        if($status)
        {
            $where.= $status=='succ'? " and c.status='SUCC'":" and c.status!='SUCC'";
        }
        $cmd = "select b.createtime as paytime,b.statusdeliver, c.* from orderstatus as b ,orders as c
            where c.id=b.orderid $where and c.status!='WAIT' 
            and b.createtime>='$beginTime' and b.createtime<'$endTime' order by b.createtime desc";
        return $this->listByCmd($cmd,$page);
    }/*}}}*/  
}/*}}}*/
