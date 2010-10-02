<?php
class GameUtls
{/*{{{*/
    static public function makePdtKey($game,$product,$service)
    {/*{{{*/
        return $game."@".$product."@".$service;
    }/*}}}*/
    static public function anaPdtKey($pdtkey)
    {/*{{{*/
        list($game,$key,$service) = explode("@",$pdtkey);
        return array("game"=>$game,"product"=>$key,"service"=>$service);
    }/*}}}*/
}/*}}}*/
class GameManager
{/*{{{*/
    private static $gameManagerIns=null;
    private $games=null;

    private function registerAllGames()
    {
        $this->register(new Game_MH());
    }

    static function ins()
    {/*{{{*/
        if(self::$gameManagerIns == null)
            self::$gameManagerIns = new GameManager();
        return self::$gameManagerIns;
    }/*}}}*/

    private function __construct()
    {/*{{{*/
        $this->registerAllGames();
    }/*}}}*/
    private function register($gameObj)
    {/*{{{*/
        DBC::requireTrue(($gameObj->gameOwnerID >= 10 && $gameObj->gameOwnerID <=1000),' id error In GameManager');
        $this->games[$gameObj->key]=$gameObj;
    }/*}}}*/

    public function getAllGames()
    {/*{{{*/
        return $this->games;
    }/*}}}*/
    public function getGame($gameKey)
    {/*{{{*/
        $gameKey = strtoupper($gameKey);
        return $this->games[$gameKey];
    }/*}}}*/
    public function getOwnerID($gameKey)
    {/*{{{*/
        $gameKey = strtoupper($gameKey);
        $game = $this->getGame($gameKey);
        return $game->getGameOwnerID();
    }/*}}}*/
    public function getPdtInfo($pdtkey)
    {/*{{{*/
        $pdtInfo = GameUtls::anaPdtKey($pdtkey);
        $game = $this->getGame($pdtInfo['game']);
        $info = $game->getProductInfo($pdtInfo['product']);
        $info["service"] = $pdtInfo['service'];
        return $info;
    }/*}}}*/
    public function getGameByPdtkey($pdtKey)
    {/*{{{*/
        $pdtInfo = GameUtls::anaPdtKey($pdtKey);
        return $this->getGame($pdtInfo['game']);
    }/*}}}*/
    public function getGameNameByPdtkey($pdtKey)
    {/*{{{*/
        $pdtInfo = GameUtls::anaPdtKey($pdtKey);
        return $this->getGame($pdtInfo['game'])->getName();
    }/*}}}*/

    public function deliverPdt($orderid,$customer,$pdtKey,$num)
    {/*{{{*/
        $pdtInfo = GameUtls::anaPdtKey($pdtKey);
        $game = $this->getGame($pdtInfo['game']);
        return $game->deliverPdt($orderid,$customer,$pdtInfo['service'],$pdtInfo['product'],$num);
    }/*}}}*/

    public function initGameAccount()
    {/*{{{*/
        if(!empty($this->games))
        {
            foreach($this->games as $game)
            {
                $acc = null;
                $acc = DDA::ins()->get_Account_by_ownerid_usetype($game->getGameOwnerID()
                    ,AccountDef::GAMEDEV_COIN);
                if(empty($acc))
                    $acc = Account::createByBiz($game->getGameOwnerID(),AccountDef::GAMEDEV_COIN);
            }
        }
    }/*}}}*/
}/*}}}*/

abstract class GameDef
{/*{{{*/
    public $name ;
    public $key ;
    public $gameOwnerID;
    public $products;
    public $services;

    abstract public function deliverPdt($orderid,$userid,$servicekey,$productkey,$num); //交货
    abstract protected function initGameInfo();
    abstract protected function initServices();
    abstract protected function initProducts();

    public function __construct($gameOwnerid)
    {/*{{{*/
        //id must >10 && <1000;
        $this->gameOwnerID = $gameOwnerid;
        $this->initGameInfo();
        $this->initServices(); 
        $this->initProducts();
    }/*}}}*/
    public function getGameOwnerID()
    {/*{{{*/
        return $this->gameOwnerID;
    }/*}}}*/
    public function getName()
    {/*{{{*/
        return $this->name;
    }/*}}}*/
    public function getKey()
    {/*{{{*/
        return $this->key;
    }/*}}}*/
    public function getAllProducts()
    {/*{{{*/
        return $this->products;
    }/*}}}*/
    public function getAllServices()
    {/*{{{*/
        return $this->services;
    }/*}}}*/
    public function getProductPrice($key)
    {/*{{{*/
        return $this->products[$key]["price"];
    }/*}}}*/
    public function getProductInfo($key)
    {/*{{{*/
        return $this->products[$key];
    }/*}}}*/

    protected function setGameInfo($gamename,$gamekey)
    {/*{{{*/
        $this->name = $gamename;
        $this->key = $gamekey;
    }/*}}}*/
    protected function addProduct($key,$name,$alias,$price,$unit="个",$buyunits=array())
    {/*{{{*/
        $this->products[$key] = array("name"=>$name,"alias"=>$alias,"key"=>$key
            ,"price"=>$price,"unit"=>$unit,"buyunits"=>$buyunits);
    }/*}}}*/
    protected function addService($key,$name,$status)
    {/*{{{*/
        $this->services[$key] = array("key"=>$key,"name"=>$name,"status"=>$status);
    }/*}}}*/
}/*}}}*/
class Game_MH extends GameDef
{/*{{{*/
    const GAME_NAME ="魔幻三国志";
    const GAME_KEY  = "MHSGZ";

    const GAME_LOGINKEY =  "cy@mhsgz@cy";
    const GAME_BILLKEY = "cy^*^#%-!^*^$#cy";

    public function __construct()
    {/*{{{*/
        parent::__construct(AccountDef::GAME_MHSGZ_OWNERID);
    }/*}}}*/
    public function loginUrl($userid,$username,$serviceKey)
    {/*{{{*/
        //MD5(ID&username&KEY）
        $sign = md5($userid."&".$username."&".self::GAME_LOGINKEY);
        if($serviceKey == "201")
            $url = "http://211.154.165.156:86/index_cy.htm?uip=211.154.165.162&port=6010&uid=$userid&uname=$username&sign=$sign";
        return $url;
    }/*}}}*/
    public function deliverPdt($orderid,$userid,$servicekey,$productkey,$num) //交货
    {/*{{{*/
        //http://211.154.165.157:81/cybill.aspx?Saccount=username&AreaID=10001&OrderNo=123&Point=100& Value= 0fb4a5122107847a9a4e225d9b03fadb
        //MD5(Saccount+AreaID+OrderNo+key）
        $order = BR::notNull(DDA::ins()->get_OrderStore_by_id($orderid),"订单不存在"); 
        $user  = BR::notNull(DDA::ins()->get_User_by_id($userid),"用户不存在"); 
        $username = $user->username;
        $sign = md5($username."|".$servicekey."|".$orderid."|".self::GAME_BILLKEY);
//        $url = "http://211.154.165.157:81/cybill.aspx?Saccount=$username&AreaID=$servicekey&OrderNo=$orderid&Point=$num&Value=$sign";
        $url = "http://bill.mh.caiyun.cc/cybill.aspx?Saccount=$username&AreaID=$servicekey&OrderNo=$orderid&Point=$num&Value=$sign";
        $logger = LogerManager::getPayLoger();
        $logger->log("[Deliverpdt BEGIN]:OrderID[$orderid],Game[MHSG],userid [$userid],servicekey[$servicekey],productkey[$productkey],number[$num] ");
        $logger->log("[Deliverpdt URL]:$url");
        //call to game interface
        $r = RemoteUtls::call($url);
        $resNum = substr($r,0,1);
        if($resNum == 1)
        {
            $logger->log("[Deliverpdt END]:OrderID[$orderid],Game[MHSG],userid [$userid],servicekey[$servicekey],productkey[$productkey],number[$num]  SUCCESS!");
            return true;
        }
        else
        {
            $logger->log("[Deliverpdt END]:OrderID[$orderid],Game[MHSG],userid [$userid],servicekey[$servicekey],productkey[$productkey],number[$num]  FAIL ,return [$r]!");
            return false;
        }
    }/*}}}*/
    protected function initGameInfo()
    {/*{{{*/
        $this->setGameInfo(self::GAME_NAME,self::GAME_KEY);
    }/*}}}*/
    protected function initServices()
    {
        //STATUS 1 正常 2维护 3繁忙
        $this->addService("201","S1天下无双","1");
    }
    protected function initProducts()
    {
        //单位是 彩云币 >= 1
        $this->addProduct("MHB","魔幻三国志金币","金币",10,"个",array(100,200,500,1000,2000,5000));
    }
}/*}}}*/

?>
