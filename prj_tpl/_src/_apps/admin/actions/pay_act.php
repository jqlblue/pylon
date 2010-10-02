<?php
class PayAction extends AdminBaseAction {
    public function __construct()
    {/*{{{*/
        parent::__construct("");
    }/*}}}*/
    public function do_order_list($vars,$xcontext,$dda)
    {/*{{{*/
        $userid   = ($vars->have("id")) && $vars->id?$vars->id : 0;
        $username = ($vars->have("name")) && $vars->name?$vars->name :'';
        $status = ($vars->have("status")) && $vars->status?$vars->status :'';
        if($username)
        {
            $user = $dda->get_User_by_userName($username);
            if($user)
            $userid = $user->id();
        }
        $endtime = ($vars->have("endtime")) && $vars->endtime?$vars->endtime : date('Y-m-d H:i:s');
        $starttime = ($vars->have("starttime")) && $vars->starttime?$vars->starttime : '2009-10-1 12:00:00';
        $order = OrderSvc::orderlist($userid,$status,$starttime,$endtime,$vars->pageObj);
        $orderinfo = array();
        foreach($order as $key=>$val)
        {
            $user = $dda->get_User_by_id($val['customer']);
            if($user)
            $username = $user->username;
            $val['name'] = $username;
            if($val['pdtkey']=='CYB')
            {
                $val['pdtname']=  CoinDef::NAME;
            }else{
                $pdtname = GameManager::ins()->getPdtInfo($val['pdtkey']);
                $val['pdtname']= $pdtname['name'];
            }
            $val['rmb'] = MoneyUtls::fen2yuan(intval($val['quantity'])*intval($val['unitprice']));
            $orderinfo[$key] = $val;
        }
        $xcontext->change = $orderinfo;
        $xcontext->status = $status;
    }/*}}}*/
    public function do_order_detail($vars,$xcontext,$dda)
    {/*{{{*/
        if( $vars->have("orderid"))
            $orderid = $vars->orderid;
        $order = $dda->get_Order_by_id($orderid);
        if($order->)
        $orderdetail = $dda->get_Payrecord_by_orderid($orderid);
        $xcontext->orderdetail = $orderdetail;
        /*$data = $orderdetail->data;
        $data = unserialize(stripslashes($data));
        var_dump($data);*/

    }/*}}}*/
}
