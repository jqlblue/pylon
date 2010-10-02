<?php
class ProductDef
{
    const TP_GAME = "GAME";
    const TP_CYB  = "CYB";
}

interface Order
{/*{{{*/
    public function append($order);
    public function execute();
}/*}}}*/
class OrderStore extends Entity
{/*{{{*/
    CONST TP_MUTI   = "MUTI_ORDER";
    CONST TP_SINGLE = "SINGLE_ORDER";

    const ST_WAIT         = "WAIT";
    const ST_PAY_SUCC     = "PAY_SUCC";
    const ST_PAY_FAIL     = "PAY_FAIL";
    const ST_DELIVER_SUCC = "DELIVER_SUCC";
    const ST_DELIVER_FAIL = "DELIVER_FAIL";
    const ST_SUCC         = "SUCC";
    const ST_FAIL         = "FAIL";

    static public function createByBiz($customer,$pdtType,$pdtKey,$quantity,$unitPrice,$currency,$paychannel,$total)
    {/*{{{*/
        $obj = new OrderStore(EntityID::create("order"));
        $obj->customer     = $customer;
        $obj->pdtType      = $pdtType;
        $obj->pdtKey       = $pdtKey;
        $obj->quantity     = $quantity;
        $obj->unitPrice    = $unitPrice;
        $obj->currency     = $currency ;
        $obj->paychannel   = $paychannel;
        $obj->total        = $total;
        $obj->status       = self::ST_WAIT;
        $obj->orders       = serialize(array());
        return Entity::createByBiz($obj);
    }/*}}}*/

    public function getPrdouctName()
    {/*{{{*/
        if($this->pdtType == ProductDef::TP_GAME)
        {/*{{{*/
            $pdtInfo = GameManager::ins()->getPdtInfo($this->pdtkey);
            $gameName = GameManager::ins()->getGameNameByPdtkey($this->pdtkey);
            return $gameName."-".$pdtInfo['name'];
        }/*}}}*/
        if($this->pdtType == ProductDef::TP_CYB)
        {/*{{{*/
            return CoinDef::NAME;
        }/*}}}*/
    }/*}}}*/
    public function execPaySucc()
    {/*{{{*/
        $this->status = self::ST_PAY_SUCC;
    }/*}}}*/
    public function execPayFail()
    {/*{{{*/
        $this->status = self::ST_PAY_FAIL;
    }/*}}}*/
    public function execDeliverSucc()
    {/*{{{*/
        $this->status = self::ST_SUCC;
    }/*}}}*/
    public function execDeliverFail()
    {/*{{{*/
        $this->status = self::ST_DELIVER_FAIL;
    }/*}}}*/
    public function execSucc()
    {/*{{{*/
        $this->status = self::ST_SUCC;
    }/*}}}*/
    public function execFail()
    {/*{{{*/
        $this->status = self::ST_FAIL;
    }/*}}}*/

    public function getOrders()
    {/*{{{*/
        if($this->ordertype == self::TP_SINGLE)
            return array();
        $orders = unserialize($this->orders);
        $orderObjs = array();
        if(!empty($orders))
            foreach($orders as $val)
                $orderObjs[] = DDA::ins()->get_OrderStore_by_ordertype_id(self::TP_SINGLE,$val);
        return $orderObjs;
    }/*}}}*/
    public function setSingleOrder()
    {/*{{{*/
        $this->ordertype  = self::TP_SINGLE;
    }/*}}}*/
    public function setMuitOrder()
    {/*{{{*/
        $this->ordertype = self::TP_MUTI;
    }/*}}}*/
}/*}}}*/

class SingleOrder extends OrderStore implements Order
{/*{{{*/
    static public function createByBiz($customer,$pdtType,$pdtKey,$quantity,$unitPrice,$currency,$paychannel,$total)
    {/*{{{*/
        $obj = new SingleOrder(EntityID::create("order"));
        $obj->customer     = $customer;
        $obj->pdtType      = $pdtType;
        $obj->pdtKey       = $pdtKey;
        $obj->quantity     = $quantity;
        $obj->unitPrice    = $unitPrice;
        $obj->currency     = $currency ;
        $obj->paychannel   = $paychannel;
        $obj->total        = $total;
        $obj->status       = self::ST_WAIT;
        $obj->orders       = serialize(array());
        $obj->setSingleOrder();
        return Entity::createByBiz($obj);
    }/*}}}*/

    public function append($order)
    {/*{{{*/
        return false;
    }/*}}}*/
    public function execute()
    {/*{{{*/
        return true;
    }/*}}}*/
}/*}}}*/

class MutiOrder extends OrderStore implements  Order 
{/*{{{*/
    static public function createByBiz($customer,$pdtType,$pdtKey,$quantity,$unitPrice,$currency,$paychannel,$total)
    {/*{{{*/
        $obj = new MutiOrder(EntityID::create("order"));
        $obj->customer     = $customer;
        $obj->pdtType      = $pdtType;
        $obj->pdtKey       = $pdtKey;
        $obj->quantity     = $quantity;
        $obj->unitPrice    = $unitPrice;
        $obj->currency     = $currency ;
        $obj->paychannel   = $paychannel;
        $obj->total        = $total;
        $obj->status       = self::ST_WAIT;
        $obj->orders       = serialize(array());
        $obj->setMuitOrder();
        return Entity::createByBiz($obj);
    }/*}}}*/

    public function append($singleOrder)
    {/*{{{*/
        $orders = unserialize($this->orders);
        array_push($orders,$singleOrder->id());
        $this->orders = serialize($orders);
        return true;
    }/*}}}*/
    public function execute()
    {/*{{{*/
        
    }/*}}}*/
}/*}}}*/

class PayRecord extends Entity
{/*{{{*/
    static public function createByBiz($orderid,$payid,$paychannel,$paytime,$data=array())
    {/*{{{*/
        $obj = new PayRecord(EntityID::create());
        $obj->orderid    = $orderid;
        $obj->payid      = $payid;
        $obj->paychannel = $paychannel;
        $obj->paytime    = $paytime;
        $obj->data    = serialize($data);
        return Entity::createByBiz($obj);
    }/*}}}*/
    public function getReceive()
    {/*{{{*/
        return unserialize($this->data);
    }/*}}}*/

}/*}}}*/

class OrderStatus extends Entity
{/*{{{*/
    const ST_WAIT = "WAIT";
    const ST_SUCC = "SUCC";
    const ST_FAIL = "FAIL";
    static public function createByBiz($order)
    {/*{{{*/
        $obj = new OrderStatus(EntityID::create());
        $obj->orderid    = $order->id();
        $obj->paychannel = $order->paychannel;

        $obj->statusPay      = self::ST_WAIT;
        $obj->statusAccount  = self::ST_WAIT;
        $obj->statusDeliver  = self::ST_WAIT;
        return Entity::createByBiz($obj);
    }/*}}}*/
    public function paySucc()
    {/*{{{*/
        $this->statusPay = self::ST_SUCC;
    }/*}}}*/
    public function payFail()
    {/*{{{*/
        $this->statusPay = self::ST_FAIL;
    }/*}}}*/
    public function deliverSucc()
    {/*{{{*/
        $this->statusDeliver = self::ST_SUCC;
    }/*}}}*/
    public function deliverFail()
    {/*{{{*/
        $this->statusDeliver = self::ST_FAIL;
    }/*}}}*/
    public function accountTransSucc()
    {/*{{{*/
        $this->statusAccount = self::ST_SUCC;
    }/*}}}*/
    public function accountTransFail()
    {/*{{{*/
        $this->statusAccount = self::ST_FAIL;
    }/*}}}*/
}/*}}}*/
?>
