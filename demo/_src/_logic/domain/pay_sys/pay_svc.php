<?php
class AccountEvent
{/*{{{*/
    const EVENT_USER_BUY_COIN     = 'USER_BUY_COIN';
    const EVENT_USER_EXPENSE_COIN4GAME = 'USER_EXPENSE_COIN4GAME';
    //money  RMB fen
    static public function userBuyCoin($ownerid,$money,$coinNumber,$dealcls,$dealid)
    {/*{{{*/
        DBC::requireNotNull($ownerid,'Ownerid Is Null In UserByCoin');
        DBC::requireNotNull($money,'Money Is Null In UserByCoin');
        DBC::requireNotNull($coinNumber,'coinNumber Is Null In UserByCoin');
        $occurDate = date("Y-m-d H:i:s");

        $ownercls = AccountTrans::USER;
        $logger = LogerManager::getPayLoger();
        $dda= DDA::ins();
        $money_from = BR::notNull($dda->get_Account_by_ownerid_usetype($ownerid,AccountDef::USER_MONEY),"not exist user_money account id:$ownerid");
        $money_to   = BR::notNull($dda->get_Account_by_ownerid_usetype(AccountDef::GSP_OWNERID,AccountDef::GSP_MONEY), "not exist gsp_money account");
        $coin_from  = BR::notNull($dda->get_Account_by_ownerid_usetype(AccountDef::GSP_OWNERID,AccountDef::GSP_COIN), "not exist gsp_money account");
        $coin_to    = BR::notNull($dda->get_Account_by_ownerid_usetype($ownerid,AccountDef::USER_COIN), "not exist gsp_money account id :$ownerid");
        $trans = AccountTrans::createByBiz($occurDate,$ownerid,$ownercls,$dealid,$dealcls,AccountEvent::EVENT_USER_BUY_COIN);
        //USER_MONEY->GSP_MONEY
        $trans->transfer($money_from,$money_to,$money);
        //GSP_COIN->USER_COIN
        $trans->transfer($coin_from ,$coin_to ,$coinNumber);
        $logger->log("[AccountEvent] UserBuyCoin: date [$occurDate],ownerid[$ownerid] , Pay money [$money] Buy Coin [$coinNumber] SUCCESS!");
        return true;
    }/*}}}*/
    static public function userExpenseCoin4Game($ownerid,$gameKey,$coinNumber,$dealcls,$dealid)
    {/*{{{*/
        $occurDate = date("Y-m-d H:i:s");
        DBC::requireNotNull($ownerid,'Ownerid Is Null In UserDoPayment');
        DBC::requireNotNull($coinNumber,'coin num Is Null In UserDoPayment');
        DBC::requireNotNull($gameKey,'gameKey Is Null In UserDoPayment');
        $ownercls = AccountTrans::USER;
        $logger   = LogerManager::getPayLoger();
        $dda      = DDA::ins();
        $gameID = GameManager::ins()->getOwnerId($gameKey);
        if(empty($gameID))
        {
            $logger->log("[AccountEvent ERROR] userExpenseCoin4Game: GameID empty! [$occurDate],ownerid[$ownerid],orderid [$dealid],expense Coin[$coinNumber] for Game[$gameKey] ");
            BR::notNull($gameID,"GameID empty! ");
        }
        $from = $dda->get_Account_by_ownerid_usetype($ownerid,AccountDef::USER_COIN);
        if(empty($from))
        {
            $logger->log("[AccountEvent ERROR] userExpenseCoin4Game: not exist USER_COIN account id :$ownerid "); 
            BR::notNull($from,"not exist USER_COIN account id :$ownerid ");
        }
        $to   = $dda->get_Account_by_ownerid_usetype($gameID,AccountDef::GAMEDEV_COIN);
        if(empty($to))
        {
            $logger->log("[AccountEvent ERROR] userExpenseCoin4Game: not exist [$gameID] GAMEDEV_COIN account "); 
            BR::notNull($to,"not exist [$gameID] GAMEDEV_COIN account");
        }

        $r = ($from->balance>=$coinNumber)?true:false;
        if(!$r)
        {
            $logger->log("[AccountEvent ERROR] userExpenseCoin4Game: Ownerid[$ownerid] noly have [$from->balance]. Coin "); 
            BR::isTrue($r,"Ownerid[$ownerid] noly have [$from->balance]. Coin不足以进行此次消费");
        }

        $trans = AccountTrans::createByBiz($occurDate,$ownerid,$ownercls,$dealid,$dealcls,AccountEvent::EVENT_USER_EXPENSE_COIN4GAME);
        $trans->transfer($from,$to,$coinNumber);
        $logger->log("[AccountEvent] userExpenseCoin4Game: DATE [$occurDate],ownerid[$ownerid],orderid [$dealid],expense Coin[$coinNumber] for Game[$gameKey] SUCCESS!");
        return true;
    }/*}}}*/
}/*}}}*/

class OrderSvc
{/*{{{*/
    const ORDER_TYPE_PAY = "充值"；
    const ORDER_TYPE_EXPENSE = "消费";
    const ORDER_TYPE_DRICT = "直购";
    public static function createCYBOrder($customer,$quantity,$paychannel)
    {/*{{{*/
        BR::notNull($customer," customer not null");
        BR::isTrue(($quantity > 0)," buy number is err");
        BR::notNull($paychannel," paychannel is null");
        $logger = LogerManager::getPayLoger();
        $logger->log("================ [OrderSvc::createCYBOrder] ===========");
        $unitPrice = CoinDef::UNIT_PRICE;
        $total = $unitPrice*$quantity;
        $currency= MoneyUtls::CURRENCY_RMB;
        $obj = SingleOrder::createbyBiz($customer,ProductDef::TP_CYB
            ,CoinDef::PRODUCT_KEY,$quantity,$unitPrice,$currency,$paychannel,$total);
        $orderstatus = OrderStatus::createByBiz($obj);
        $logger->log("[OrderSvc::createCYBOrder]:customer[$customer],quantity[$quantity],paychannel[$paychannel] SUCCESS!");
        return $obj;
    }/*}}}*/
    public static function createGameOrder($customer,$gameKey,$product,$service,$quantity,$paychannel)
    {/*{{{*/
        $pdtKey  = GameUtls::makePdtKey($gameKey,$product,$service);
        $pdtInfo = GameManager::ins()->getPdtInfo($pdtKey);

        $pdtUnitPrice4CYB  = $pdtInfo["price"];
        $pdtUnitPrice4RMB  = CoinDef::coin2RMB($pdtInfo["price"]);
        $coinUnitPrice4RMB = CoinDef::UNIT_PRICE;

        $currencyCYB = MoneyUtls::CURRENCY_CYB;
        $currencyRMB = MoneyUtls::CURRENCY_RMB;
        
        $logger = LogerManager::getPayLoger();
        $logger->log("================ [OrderSvc::createCYBOrder] ===========");
        //CYB Payment
        if($paychannel == CoinDef::CYB_PAY)
        {
            $gameOrder = SingleOrder::createbyBiz($customer,ProductDef::TP_GAME,$pdtKey
                ,$quantity,$pdtUnitPrice4CYB,$currencyCYB,$paychannel,$pdtUnitPrice4CYB*$quantity);
        }
        else
        {
            //RMB payment
            $pdtTotal4RMB = $pdtUnitPrice4RMB*$quantity;
            $buyCYBNum = round($pdtTotal4RMB /$coinUnitPrice4RMB,2);
            // buy CYB
            $buyCYBOrder = SingleOrder::createbyBiz($customer,ProductDef::TP_CYB,CoinDef::PRODUCT_KEY
                ,$buyCYBNum,$coinUnitPrice4RMB,$currencyRMB,$paychannel,$buyCYBNum*$coinUnitPrice4RMB);
            // buy product by CYB
            $coinBuyGamePdtOrder = SingleOrder::createbyBiz($customer,ProductDef::TP_GAME,$pdtKey
                ,$quantity,$pdtUnitPrice4CYB,$currencyCYB,CoinDef::CYB_PAY,$buyCYBNum);
            // create mutiOrder
            $gameOrder =  MutiOrder::createByBiz($customer,ProductDef::TP_GAME,$pdtKey,$quantity
                ,$pdtUnitPrice4RMB,$currencyRMB,$paychannel,$pdtUnitPrice4RMB*$quantity);
            $gameOrder->append($buyCYBOrder);
            $gameOrder->append($coinBuyGamePdtOrder);
        }
        $orderstatus = OrderStatus::createByBiz($gameOrder);
        $logger->log("[OrderSvc::createGameOrder]:customer[$customer],game[$gameKey],product[$product],service[$service], quantity [$quantity],paychannel[$paychannel] SUCCESS!");
        return $gameOrder;
    }/*}}}*/

    public static function execute($orderID)
    {/*{{{*/
        $logger = LogerManager::getPayLoger();
        $logger->log("[OrderSvc::execute] orderID[$orderID] Begin");
        $payRes = self::execPay($orderID);
        if($payRes)
        {
            $logger->log("[OrderSvc::execute] orderID[$orderID] AccountTrans SUCCESS !");
            $delRes = self::execDeliver($orderID);
            $apps = AppSession::begin();
            $order = DDA::ins()->get_OrderStore_by_id($orderID);
            if($delRes)
            {
                $logger->log("[OrderSvc::execute] orderID[$orderID] Deliver SUCCESS !");
                $order->execDeliverSucc();
            }
            else
            {
                $logger->log("[OrderSvc::execute] orderID[$orderID] Deliver FAIL !");
                $order->execDeliverFail();
            }
            $apps->commit();
            $apps = null;
            $logger->log("[OrderSvc::execute] orderID[$orderID] END");
            return $delRes;
        }
        else
        {
            $logger->log("[OrderSvc::execute] orderID[$orderID] AccountTrans FAIL !");
            $apps = AppSession::begin();
            $order = DDA::ins()->get_OrderStore_by_id($orderID);
            $order->execPayFail();
            $apps->commit();
            $apps = null;
            $logger->log("[OrderSvc::execute] orderID[$orderID] END");
            return false;
        }
    }/*}}}*/

    private static function execPay($orderID)
    {/*{{{*/
        $apps = AppSession::begin();
        $logger = LogerManager::getPayLoger();
        $dda   = DDA::ins();
        $order = BR::notNull($dda->get_OrderStore_by_id_status($orderID,OrderStore::ST_WAIT),"not exist order account id:$orderID");
        $apps->commit();
        $apps = null;

        if($order->ordertype ==OrderStore::TP_SINGLE)
        {
            $apps = AppSession::begin();
            $rel = self::execSingleOrderPay($order->id());
            $apps->commit();
            $apps = null;
        }
        if($order->ordertype ==OrderStore::TP_MUTI && $order->pdtType == ProductDef::TP_GAME) //game muti order
        {
            $rel = false;
            $childOrders = $order->getOrders();
            if(!empty($childOrders))
            {
                foreach($childOrders as $childOrder)
                {
                    $apps = AppSession::begin();
                    $rel = self::execSingleOrderPay($childOrder->id());
                    $apps->commit();
                    $apps = null;
                    if(!$rel)
                        break;
                }
            }
        }

        $apps = AppSession::begin();
        $orderStatus = DDA::ins()->get_OrderStatus_by_orderid($orderID);
        if($rel)
            $orderStatus->accountTransSucc();
        else
            $orderStatus->accountTransFail();
        $apps->commit();
        $apps = null;
        return $rel;
    }/*}}}*/
    private static function execDeliver($orderID)
    {/*{{{*/
        $apps = AppSession::begin();
        $logger = LogerManager::getPayLoger();
        $dda   = DDA::ins();
        $order = BR::notNull($dda->get_OrderStore_by_id($orderID),"not exist order id:$orderID");
        $apps->commit();
        $apps = null;

        if($order->ordertype ==OrderStore::TP_SINGLE)
        {
            $apps = AppSession::begin();
            $rel = self::execSingleDeliver($order->id());
            $apps->commit();
            $apps = null;
        }
        if($order->ordertype ==OrderStore::TP_MUTI && $order->pdtType == ProductDef::TP_GAME) //game muti order
        {
            $rel = false;
            $childOrders = $order->getOrders();
            if(!empty($childOrders))
            {
                foreach($childOrders as $childOrder)
                {
                    $apps = AppSession::begin();
                    $rel = self::execSingleDeliver($childOrder->id());
                    $apps->commit();
                    $apps = null;
                    if(!$rel)
                        break;
                }
            }
        }
        $apps = AppSession::begin();
        $orderStatus = $dda->get_OrderStatus_by_orderid($orderID);
        if($rel)
            $orderStatus->deliverSucc();
        else
            $orderStatus->deliverFail();
        $apps->commit();
        $apps = null;
        return $rel;
    }/*}}}*/
    private static function execSingleOrderPay($orderID)
    {/*{{{*/
        $order = DDA::ins()->get_OrderStore_by_id($orderID);
        if($order->status != OrderStore::ST_WAIT)
            return false;
        $logger = LogerManager::getPayLoger();
        if($order->pdtkey == CoinDef::PRODUCT_KEY) // user buy coin
        {
            $res = AccountEvent::userBuyCoin($order->customer,$order->total,$order->quantity,'OrderStore',$orderID);
            if($res)
            {
                $order->execPaySucc();
                return true;
            }
            else
            {
                $order->execPayFail();
                return true;
            }
        }
        elseif($order->pdtType == ProductDef::TP_GAME && $order->paychannel = CoinDef::CYB_PAY) //user buy game prop by CYB
        {
            $game = GameManager::ins()->getGameByPdtkey($order->pdtKey);
            $res  = AccountEvent::userExpenseCoin4Game($order->customer,$game->getKey(),$order->total,'OrderStore',$orderID);

            if($res)
            {
                $order->execPaySucc();
                return true;
            }
            else
            {
                $order->execPayFail();
                return false;
            }
        }
    }/*}}}*/
    private static function execSingleDeliver($orderID)
    {/*{{{*/
        $order = DDA::ins()->get_OrderStore_by_id($orderID);
        if($order->status != OrderStore::ST_PAY_SUCC || $order->ordertype != OrderStore::TP_SINGLE)
            return false;
        $logger = LogerManager::getPayLoger();
        if($order->pdtType == ProductDef::TP_GAME)
        {
            $delRes = GameManager::ins()->deliverPdt($order->id(),$order->customer,$order->pdtKey,$order->quantity);
            if($delRes)
            {
                $order->execDeliverSucc();
                return true;
            }
            else
            {
                $order->execDeliverFail();
                return false;
            }
        }
        elseif($order->pdtKey == ProductDef::TP_CYB)
        {
            $logger->log("[OrderSvc::execute] >> [DeliverCYB] order[$orderID], product[$order->pdtKey] SUCCESS!");
            $order->execDeliverSucc();
            return true;
        }
    }/*}}}*/
    public static function orderlist($userid,$status,$beginTime,$endTime,$page)
    {/*{{{*/
        $orders = DaoFinder::query("AccountQuery")->orderlist($userid,$status,$beginTime,$endTime,$page);
        return $orders;
    }/*}}}*/
    public static function ordertype($orderid)
    {/*{{{*/
        $orderinfo = get_Order_by_id($orderid);
        if()

    }/*}}}*/
}?>
