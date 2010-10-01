<?php
class PayChannelManager
{/*{{{*/
    private static $managerIns=null;
    private $channels = array(); 
    public static function ins()
    {/*{{{*/
        if(self::$managerIns == null)
            self::$managerIns = new PayChannelManager();
        return self::$managerIns;
    }/*}}}*/
    private function regAllChannels()
    {
        $this->register(new Pay_ALIPAY());
        $this->register(new Pay_99BILL());
    }
    public function getAll()
    {/*{{{*/
        return $this->channels; 
    }/*}}}*/

    public function getPay($payKey)
    {/*{{{*/
        return BR::notNull($this->channels[$payKey]," game not null");
    }/*}}}*/

    private function register($obj)
    {/*{{{*/
        DBC::requireNotNull($obj,' obj is null, error In PayManager');
        $this->channels[$obj->getKey()] = $obj;
    }/*}}}*/
    private function __construct()
    {/*{{{*/
        $this->regAllChannels();
    }/*}}}*/
}/*}}}*/

class RemoteUtls
{/*{{{*/
    public static function call($url,$opts=array(),$timeout=20,$mothod='get',$logger=null)
    {/*{{{*/
        $ch = curl_init();
        //        curl_setopt($ch, CURLOPT_VERBOSE, 1 );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        if(isset($opts) && count($opts)>0)
        {
            $pstring='';
            foreach($opts as $key => $val)
                $pstring .= trim($key) . '=' . urlencode(trim($val)) . "&";
            $pstring = substr($pstring,0,-1);
            if($logger)
            {
                $logger->log("call url: $url");
                $logger->log("args    : $pstring");
            }
            if(strtolower($mothod)=='post') {
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $pstring);
                curl_setopt($ch, CURLOPT_URL, $url);
            }
            else 
            {
                curl_setopt($ch, CURLOPT_HTTPGET, true);
                curl_setopt($ch, CURLOPT_URL, $url."?".$pstring);
            }
        }
        else
        { 
            if(strtolower($mothod)=='post')
                curl_setopt($ch, CURLOPT_POST, true);
            else
                curl_setopt($ch, CURLOPT_HTTPGET, true);
            curl_setopt($ch, CURLOPT_URL, $url);
        }
        $r = curl_exec($ch);
        curl_close($ch);  
        return $r; 
    }/*}}}*/
    public static function postData($url, $data) 
    {/*{{{*/
        // Get parts of URL
        $url = parse_url($url);
        if (!$url) { return "couldn't parse url"; }

        // Provide defaults for port and query string
        if (!isset($url['port']))  { $url['port'] = ""; }
        if (!isset($url['query'])) { $url['query'] = ""; }

        // Build POST string
        $encoded = "";
        foreach ($data as $k => $v) {
            $encoded .= ($encoded ? "&" : "");
            $encoded .= rawurlencode($k) . "=" . rawurlencode($v);
        }

        // Open socket on host
        $fp = @fsockopen($url['host'], $url['port'] ? $url['port'] : 80);
        if (!$fp) { return "failed to open socket to {$url['host']}"; }

        // Send HTTP 1.0 POST request to host
        fputs($fp, sprintf("POST %s%s%s HTTP/1.0\n", $url['path'], $url['query'] ? "?" : "", $url['query']));
        fputs($fp, "Host: {$url['host']}\n");
        fputs($fp, "Content-type: application/x-www-form-urlencoded\n");
        fputs($fp, "Content-length: " . strlen($encoded) . "\n");
        fputs($fp, "Connection: close\n\n");
        fputs($fp, "$encoded\n");

        // Read the first line of data, only accept if 200 OK is sent
        $line = fgets($fp, 1024);
        if (!preg_match('#^HTTP/1\\.. 200#', $line)) { return; }

        // Put everything, except the headers to $results 
        $results = ""; $inheader = TRUE;
        while(!feof($fp)) {
            $line = fgets($fp, 1024);
            if ($inheader && ($line == "\n" || $line == "\r\n")) {
                $inheader = FALSE;
            }
            elseif (!$inheader) {
                $results .= $line;
            }
        }
        fclose($fp);

        // Return with data received
        return $results;
    }/*}}}*/
    public static function goto($url)
    {/*{{{*/
        Header("Location: $url"); 
        //echo "<script language='javascript'> location='$url'; </script>";
    }/*}}}*/
}/*}}}*/

abstract class PayChannel
{/*{{{*/
    public $name = null;
    public $key  = null;
    public function __construct()
    {/*{{{*/
        $this->name = $this->setName();    
        $this->key  = $this->setKey();
    }/*}}}*/
    public function getName()
    {/*{{{*/
        return $this->name;
    }/*}}}*/
    public function getKey()
    {/*{{{*/
        return $this->key;
    }/*}}}*/
    abstract public function getResult();

    abstract public function setName();
    abstract public function setKey();
    //提交支付
    abstract public function submit($order);
    //接收結果
    abstract public function receive();
}/*}}}*/
class Pay_ALIPAY extends PayChannel
{/*{{{*/
    const PARTNER_KEY = "2088201858405126";
    const ALIPAY_EMAIL = "caiyunhr@163.com";
    const SECURITY_CODE = "v74g5v0q8w4eh86926tkws5htv38xam3";
    const NOTIFY_URL = "alipay_notify.php";
    const SIGN_TYPE = "MD5";

    const NAME = "支付宝";
    const CHANNEL_KEY = "ALIPAY";
    public function setName()
    {/*{{{*/
        return $this->name = self::NAME;
    }/*}}}*/
    public function setKey()
    {/*{{{*/
        return $this->key = self::CHANNEL_KEY;
    }/*}}}*/

    public function getResult()
    {/*{{{*/
        if($_REQUEST['trade_status'] == 'TRADE_FINISHED') 
        {
            $orderStatus = DDA::ins()->get_OrderStatus_by_orderid($_REQUEST['out_trade_no']);
            if($orderStatus->statusDeliver == OrderStatus::ST_SUCC)
                return array("ST"=>"SUCCESS","KEY"=>"SUCCESS","MSG"=>"支付成功,交易完毕"); 
            else
                return array("ST"=>"SUCCESS","KEY"=>"SUCCESS","MSG"=>"支付成功，配送中,请稍后..."); 
        }
        else
        {
            return array("ST"=>"FAIL","KEY"=>"PAY_ERROR","MSG"=>"支付错误,请于客户人员联系"); 
        }
    }/*}}}*/

    public function submit($order)
    {/*{{{*/
        $logger = LogerManager::getPayLoger();
        BR::isTrue(($order->status == OrderStore::ST_WAIT),"订单状态错误");
        $price     = MoneyUtls::fen2yuan($order->unitPrice); //元
        $total_fee = MoneyUtls::fen2yuan($order->total); //元
        $gameName  = iconv("UTF-8","GBK",$order->getPrdouctName()); //默认GBK

        $rtnUrl="http://".Conf::PAY_DOMAIN."/index.html?do=payment_status&paychannel=ALIPAY&orderid=".$order->id();
        $parameter = array(
            "service"        => "create_direct_pay_by_user",
            "partner"        => self::PARTNER_KEY, //合作商户号
            //            "_input_charset" => self::CHARSET,
            "return_url"     => $rtnUrl,  //同步返回
            "notify_url"     => "http://".Conf::PAY_DOMAIN."/".self::NOTIFY_URL,  //异步返回
            "subject"        => $gameName,//商品名称，必填
            "body"           => $gameName,
            "show_url"       => "http://".Conf::PAY_DOMAIN,
            "out_trade_no"   => $order->id(),
            //          "total_fee"      => $total_fee,
            "price"          => $price,
            "quantity"       => $order->quantity,
            "payment_type"   => "1",
            "seller_email"   => self::ALIPAY_EMAIL
        );
        $alipay = new alipay_service($parameter,self::SECURITY_CODE,self::SIGN_TYPE,"GBK","http");
        $link   = $alipay->create_url();
        $logger->log("[ALIPAY SUBMIT]:orderID[".$order->id()."] , $link");
        RemoteUtls::goto($link);
    }/*}}}*/
    public function receive()
    {/*{{{*/
        $alipay = new alipay_notify(self::PARTNER_KEY,self::SECURITY_CODE,self::SIGN_TYPE,"GBK","http");
        $verify_result = $alipay->notify_verify();
        $orderId = $_POST['out_trade_no'];
        $logger = LogerManager::getPayLoger();
        $receiveUrl = "";
        foreach($_POST as $key=>$val)
            $receiveUrl.= "&".$key."=".$val;
        $logger->log("[ALIPAY RECEIVE]:orderID[$orderId] RECEVIE DATA [$receiveUrl]");
        if($verify_result) 
        {
            $logger->log("[ALIPAY RECEIVE]:orderID[$orderId] verify TRUE!");
            // save notify
            $orderId = $_POST['out_trade_no'];    //获取支付宝传递过来的订单号
            $payid   = $_POST["trade_no"];//通知id
            $trade_status = $_POST['trade_status'];    
            $notify_time  = $_POST["notify_time"];

            $apps = AppSession::begin();
            $record = DDA::ins()->get_PayRecord_by_orderid($orderId);
            $orderStatus = DDA::ins()->get_OrderStatus_by_orderid($orderId);
            if(!empty($record))
            {
                $logger->log("[ALIPAY RECEIVE]:orderID[$orderId] is exist! RETURN;");
                echo "fail";
                return false;
            }
            $payRecord = PayRecord::createByBiz($orderId,$payid,self::CHANNEL_KEY,Date("Y-m-d H:i:s"),$_POST);
            $logger->log("[ALIPAY RECEIVE]:orderID[$orderId] [trade_status]:".$_POST['trade_status']." ");
            if($_POST['trade_status'] == 'TRADE_FINISHED') 
                $orderStatus->paySucc();
            else
                $orderStatus->payFail();
            $apps->commit();
            $apps = null;
            // WAIT_BUYER_PAY(表示等待买家付款);
            // WAIT_SELLER_SEND_GOODS(表示买家付款成功,等待卖家发货);
            // WAIT_BUYER_CONFIRM_GOODS(卖家已经发货等待买家确认);
            // TRADE_FINISHED(表示交易已经成功结束)
            if($_POST['trade_status'] == 'TRADE_FINISHED') 
            {
                $logger->log("[ALIPAY RECEIVE]:orderID[$orderId] PAY SUCCESS!");
                echo "success";
                if(OrderSvc::execute($orderId))
                    $logger->log("[ALIPAY RECEIVE] orderId[$orderId], PAY SUCCESS and [OrderSvc::execute] SUCCESS!");
                else
                    $logger->log("[WARNING] [ALIPAY RECEIVE] orderId[$orderId], PAY SUCCESS but [OrderSvc::execute] FAIL!");
            }
        }
        else  
        {
            $logger->log("[ALIPAY RECEIVE]:ERROR verify error, receive:[$receiveUrl]");
            echo "fail";
        }
    }/*}}}*/
}/*}}}*/
class Pay_99BILL extends PayChannel
{/*{{{*/
    const ACCTID = "1001846934001";
    const ACCTKEY = "J9IYDCBEHLQ7HWK3";
    const ACTION_URL = "https://www.99bill.com/gateway/recvMerchantInfoAction.htm";
    const BG_URL = "99bill_receive.php";
    const NAME = "快钱";
    const CHANNEL_KEY = "99BILL";
    private $errors = array();

    public function __construct()
    {/*{{{*/
        parent::__construct();
        $this->setErrorNumber();
    }/*}}}*/

    public function getResult()
    {/*{{{*/
        $errkey = $_REQUEST["st"];
        $key = strtoupper($errkey);
        return $this->errors[$key];
    }/*}}}*/
    public function setName()
    {/*{{{*/
        return $this->name = self::NAME;
    }/*}}}*/
    public function setKey()
    {/*{{{*/
        return $this->key = self::CHANNEL_KEY;
    }/*}}}*/

    public function submit($order)
    {/*{{{*/
        $merchantAcctId=self::ACCTID;
        $key=self::ACCTKEY;
        $inputCharset="2";
        $bgUrl="http://".Conf::PAY_DOMAIN."/".self::BG_URL;
        $version="v2.0";
        $language="1";
        $signType="1";	
        $payerName="";
        $payerContactType="1";	
        $payerContact="";
        $orderId=$order->id();
        $orderAmount=$order->total;
        $orderTime=date('YmdHis');
        $productName =iconv("UTF-8","GBK",$order->getPrdouctName());
        $productNum=$order->quantity;
        $productId=$order->pdtkey;
        $productDesc="";
        $ext1="";
        $ext2="";
        $payType="00";
        $redoFlag="1";
        $pid=""; 

        $signMsgVal=$this->appendParam($signMsgVal,"inputCharset",$inputCharset);
        $signMsgVal=$this->appendParam($signMsgVal,"bgUrl",$bgUrl);
        $signMsgVal=$this->appendParam($signMsgVal,"version",$version);
        $signMsgVal=$this->appendParam($signMsgVal,"language",$language);
        $signMsgVal=$this->appendParam($signMsgVal,"signType",$signType);
        $signMsgVal=$this->appendParam($signMsgVal,"merchantAcctId",$merchantAcctId);
        $signMsgVal=$this->appendParam($signMsgVal,"payerName",$payerName);
        $signMsgVal=$this->appendParam($signMsgVal,"payerContactType",$payerContactType);
        $signMsgVal=$this->appendParam($signMsgVal,"payerContact",$payerContact);
        $signMsgVal=$this->appendParam($signMsgVal,"orderId",$orderId);
        $signMsgVal=$this->appendParam($signMsgVal,"orderAmount",$orderAmount);
        $signMsgVal=$this->appendParam($signMsgVal,"orderTime",$orderTime);
        $signMsgVal=$this->appendParam($signMsgVal,"productName",$productName);
        $signMsgVal=$this->appendParam($signMsgVal,"productNum",$productNum);
        $signMsgVal=$this->appendParam($signMsgVal,"productId",$productId);
        $signMsgVal=$this->appendParam($signMsgVal,"productDesc",$productDesc);
        $signMsgVal=$this->appendParam($signMsgVal,"ext1",$ext1);
        $signMsgVal=$this->appendParam($signMsgVal,"ext2",$ext2);
        $signMsgVal=$this->appendParam($signMsgVal,"payType",$payType);	
        $signMsgVal=$this->appendParam($signMsgVal,"redoFlag",$redoFlag);
        $signMsgVal=$this->appendParam($signMsgVal,"pid",$pid);
        $signMsgVal=$this->appendParam($signMsgVal,"key",$key);

        $logger = LogerManager::getPayLoger();

        $signMsg= strtoupper(md5($signMsgVal));
        $url = self::ACTION_URL;
        $logger->log("[99BILL SUBMIT]:post url[$url] ,data[$signMsgVal]");
        echo '<!doctype html public "-//w3c//dtd html 4.0 transitional//en" >';
        echo '   <html>';
        echo '  <head>';
        echo '  <title></title>';
        echo '  <meta http-equiv="content-type" content="text/html; charset=gbk" >';
        echo '  </head>';
        echo '  <BODY>';
        echo '<form id="kqPay" name="kqPay" method="post" action="'.$url.'" target="_self">';
        echo '<input type="hidden" name="inputCharset" value="'.$inputCharset.'"/>';
        echo '<input type="hidden" name="bgUrl" value="'.$bgUrl.'"/>';
        echo '<input type="hidden" name="version" value="'.$version.'"/>';
        echo '<input type="hidden" name="language" value="'.$language.'"/>';
        echo '<input type="hidden" name="signType" value="'.$signType.'"/>';
        echo '<input type="hidden" name="signMsg" value="'.$signMsg.'"/>';
        echo '<input type="hidden" name="merchantAcctId" value="'.$merchantAcctId.'"/>';
        echo '<input type="hidden" name="payerName" value="'.$payerName.'"/>';
        echo '<input type="hidden" name="payerContactType" value="'.$payerContactType.'"/>';
        echo '<input type="hidden" name="payerContact" value="'.$payerContact.'"/>';
        echo '<input type="hidden" name="orderId" value="'.$orderId.'"/>';
        echo '<input type="hidden" name="orderAmount" value="'.$orderAmount.'"/>';
        echo '<input type="hidden" name="orderTime" value="'.$orderTime.'"/>';
        echo '<input type="hidden" name="productName" value="'.$productName.'"/>';
        echo '<input type="hidden" name="productNum" value="'.$productNum.'"/>';
        echo '<input type="hidden" name="productId" value="'.$productId.'"/>';
        echo '<input type="hidden" name="productDesc" value="'.$productDesc.'"/>';
        echo '<input type="hidden" name="ext1" value="'.$ext1.'"/>';
        echo '<input type="hidden" name="ext2" value="'.$ext2.'"/>';
        echo '<input type="hidden" name="payType" value="'.$payType.'"/>';
        echo '<input type="hidden" name="redoFlag" value="'.$redoFlag.'"/>';
        echo '<input type="hidden" name="pid" value="'.$pid.'"/>';
//        echo '<input type="submit" name="pay_submit" value="">';
        echo'</form>';
        echo "<script>";
        echo "document.getElementById('kqPay').submit();";
        echo "</script>";
        echo '  </BODY>';
        echo '  </HTML>';
    }/*}}}*/
    public function receive()
    {/*{{{*/
        $merchantAcctId=trim($_REQUEST['merchantAcctId']);
        $key=self::ACCTKEY;
        $version=trim($_REQUEST['version']);
        $language=trim($_REQUEST['language']);
        $signType=trim($_REQUEST['signType']);
        $payType=trim($_REQUEST['payType']);
        $bankId=trim($_REQUEST['bankId']);
        $orderId=trim($_REQUEST['orderId']);
        $orderTime=trim($_REQUEST['orderTime']);
        $orderAmount=trim($_REQUEST['orderAmount']);
        $dealId=trim($_REQUEST['dealId']);
        $bankDealId=trim($_REQUEST['bankDealId']);
        $dealTime=trim($_REQUEST['dealTime']);
        $payAmount=trim($_REQUEST['payAmount']);
        $fee=trim($_REQUEST['fee']);
        $ext1=trim($_REQUEST['ext1']);
        $ext2=trim($_REQUEST['ext2']);
        $payResult=trim($_REQUEST['payResult']);
        $errCode=trim($_REQUEST['errCode']);
        $signMsg=trim($_REQUEST['signMsg']);

        $logger = LogerManager::getPayLoger();
        $receiveUrl = "";
        $merchantSignMsgVal=$this->appendParam($merchantSignMsgVal,"merchantAcctId",$merchantAcctId);
        $merchantSignMsgVal=$this->appendParam($merchantSignMsgVal,"version",$version);
        $merchantSignMsgVal=$this->appendParam($merchantSignMsgVal,"language",$language);
        $merchantSignMsgVal=$this->appendParam($merchantSignMsgVal,"signType",$signType);
        $merchantSignMsgVal=$this->appendParam($merchantSignMsgVal,"payType",$payType);
        $merchantSignMsgVal=$this->appendParam($merchantSignMsgVal,"bankId",$bankId);
        $merchantSignMsgVal=$this->appendParam($merchantSignMsgVal,"orderId",$orderId);
        $merchantSignMsgVal=$this->appendParam($merchantSignMsgVal,"orderTime",$orderTime);
        $merchantSignMsgVal=$this->appendParam($merchantSignMsgVal,"orderAmount",$orderAmount);
        $merchantSignMsgVal=$this->appendParam($merchantSignMsgVal,"dealId",$dealId);
        $merchantSignMsgVal=$this->appendParam($merchantSignMsgVal,"bankDealId",$bankDealId);
        $merchantSignMsgVal=$this->appendParam($merchantSignMsgVal,"dealTime",$dealTime);
        $merchantSignMsgVal=$this->appendParam($merchantSignMsgVal,"payAmount",$payAmount);
        $merchantSignMsgVal=$this->appendParam($merchantSignMsgVal,"fee",$fee);
        $merchantSignMsgVal=$this->appendParam($merchantSignMsgVal,"ext1",$ext1);
        $merchantSignMsgVal=$this->appendParam($merchantSignMsgVal,"ext2",$ext2);
        $merchantSignMsgVal=$this->appendParam($merchantSignMsgVal,"payResult",$payResult);
        $merchantSignMsgVal=$this->appendParam($merchantSignMsgVal,"errCode",$errCode);
        $merchantSignMsgVal=$this->appendParam($merchantSignMsgVal,"key",$key);
        $merchantSignMsg= md5($merchantSignMsgVal);
        $rtnOk=0;
        $rtnUrl="";

        $logger->log("[99BILL RECEIVE]:$merchantSignMsgVal");
        if(empty($orderId))
        {
            $logger->log("[99BILL RECEIVE]:orderId error");
            return false;
        }

        if(strtoupper($signMsg)==strtoupper($merchantSignMsg))
        {
            $rtnUrl="http://".Conf::PAY_DOMAIN."/index.html?do=payment_status&paychannel=99BILL&orderid=".$orderId;
            $apps = AppSession::begin();
            $record = DDA::ins()->get_PayRecord_by_orderid($orderId);
            $orderStatus = DDA::ins()->get_OrderStatus_by_orderid($orderId);
            if(!empty($record))
            {
                $logger->log("[99BILL RECEIVE]:orderId[$orderId] is exist!");
                if($payResult == 10)
                {
                    $orderStatus->paySucc();
                    $rtnOk=1;
                    $rtnUrl.="&st=SUCCESS";
                }
                else
                {
                    $orderStatus->payFail();
                    $rtnOk=1;
                    $rtnUrl.="&st=PAY_ERROR";
                }
                echo "<result>$rtnOk</result><redirecturl>$rtnUrl</redirecturl>";
                $apps->commit();
                return true;
            }
            $payRecord = PayRecord::createByBiz($orderId,$dealId,$this->key,Date("Y-m-d H:i:s"),$_REQUEST);
            if($payResult == 10)
                $orderStatus->paySucc();
            else
                $orderStatus->payFail();
            $apps->commit();
            $apps=null;

            switch($payResult)
            {
            case "10":
                $logger->log("[99BILL receive] orderId[$orderId] PAY SUCCESS!");
                if(OrderSvc::execute($orderId))
                {
                    $logger->log("[99BILL receive] orderId[$orderId], PAY SUCCESS and [OrderSvc::execute] SUCCESS!");
                    $rtnUrl.="&st=SUCCESS";
                }
                else
                {
                    $logger->log("[WARNING] [99BILL receive] orderId[$orderId] PAY SUCCESS,but [OrderSvc::execute] FAIL !");
                    $rtnUrl.="&st=EXEC_ORDER_ERROR";
                }
                $rtnOk=1;
                break;
            default:
                $logger->log("[99BILL receive] orderId[$orderId] PAY Fail! return PayResult[$payResult]");
                $rtnOk=1;
                $rtnUrl.="&st=PAY_ERROR";
                break;
            }
        }
        else
        {
            $logger->log("[99BILL receive] orderId[$orderId] sign error SignMsgVal[$merchantSignMsgVal] !");
            $rtnOk=1;
            $rtnUrl.="&st=PAY_SIGN_ERROR";
        } 
        echo "<result>$rtnOk</result><redirecturl>$rtnUrl</redirecturl>";
    }/*}}}*/

    private function appendParam($returnStr,$paramId,$paramValue)
    {/*{{{*/
        if($returnStr!="")
        {
            if($paramValue!="")
            {
                $returnStr.="&".$paramId."=".$paramValue;
            }
        }
        else
        {
            if($paramValue!="")
            {
                $returnStr=$paramId."=".$paramValue;
            }
        }
        return $returnStr;
    }/*}}}*/
    private function setErrorNumber()
    {/*{{{*/
        $this->errors["SUCCESS"] = array("ST"=>"SUCCESS","KEY"=>"SUCCESS","MSG"=>"购买成功,交易完成"); 
        $this->errors["PAY_ERROR"] = array("ST"=>"FAIL","KEY"=>"PAY_ERROR","MSG"=>"支付失败,请和客户联系"); 
        $this->errors["PAY_SIGN_ERROR"] = array("ST"=>"FAIL","KEY"=>"PAY_SIGN_ERROR","MSG"=>"链接非法，签名错误"); 
        $this->errors["EXEC_ORDER_ERROR"] = array("ST"=>"FAIL","KEY"=>"EXEC_ORDER_ERROR","MSG"=>"支付成功，配送中，请稍后"); 
    }/*}}}*/
}/*}}}*/
?>
