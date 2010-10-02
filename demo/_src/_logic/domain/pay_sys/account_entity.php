<?php
class MoneyUtls
{/*{{{*/
    const CURRENCY_CYB = "CYB";
    const CURRENCY_RMB = "RMB";

    static public function fen2yuan($fen)
    {/*{{{*/
        return number_format($fen/100,2,".","");
    }/*}}}*/
    static public function yuan2fen($yuan)
    {/*{{{*/
        return $yuan * 100;
    }/*}}}*/
    static public function getCurrencyName($key)
    {/*{{{*/
        if($key == self::CURRENCY_RMB)
            return "人民币";
        else
            return CoinDef::NAME;
    }/*}}}*/
}/*}}}*/

class CoinDef
{/*{{{*/
    const UNIT_PRICE = 10; //fen
    const NAME = "彩云币";
    const PRODUCT_KEY  = "CYB";
    const CYB_PAY = "CYB_PAY";

    static function RMB2coin($fen)
    {/*{{{*/
        return $fen/CoinDef::UNIT_PRICE;
        //$yuan = MoneyUtls::fen2yuan($fen);
        //return $yuan/MoneyUtls::fen2yuan(CoinDef::UNIT_PRICE);
    }/*}}}*/
    static function coin2RMB($num)
    {/*{{{*/
        return $num*CoinDef::UNIT_PRICE;
    }/*}}}*/
}/*}}}*/
class AccountDef
{/*{{{*/
    const GSP_OWNERID = 1;
    //MUST BE  XXX_MONEY OR XXX_COIN !!!
    CONST USER_MONEY  = "USER_MONEY"; 
    CONST USER_COIN   = "USER_COIN";
    CONST GSP_MONEY   = "GSP_MONEY";
    CONST GSP_COIN    = "GSP_COIN";
    //游戏开发商帐户
    CONST GAMEDEV_COIN    = "GAMEDEV_COIN";
    //游戏开发商帐户OWNERID
    // id >10 <1000
    CONST GAME_MHSGZ_OWNERID   = 10;
    static function gameAccountOwnerid()
    {/*{{{*/
        $games = array();
        $games[AccountDef::GAME_MHSGZ_OWNERID] = new Game_MH();
        return $games;
    }/*}}}*/
}/*}}}*/


class Account extends Entity
{/*{{{*/
    const ST_ACTIVE='active';
    const ST_FREEZE='freeze';
    static public function createByBiz($ownerid,$useType)
    {/*{{{*/
        $obj = new Account(EntityID::create("account"));
        $obj->ownerid = $ownerid;
        $obj->useType = $useType;
        $obj->balance = 0;
        $obj->credit= 0;
        $obj->status  = Account::ST_ACTIVE;
        return Entity::createByBiz($obj);
    }/*}}}*/
    static public function actOfStatus($v)
    {/*{{{*/
        $act[self::ST_ACTIVE]   = array( "key"=>self::ST_FREEZE,"desc"=>"冻洁","status"=>"已激活");
        $act[self::ST_FREEZE]   = array( "key"=>self::ST_ACTIVE,"desc"=>"激活","status"=>"已冻洁");
        return $act[$v];
    }/*}}}*/
    public function transfer($trans,$to,$money)
    {/*{{{*/
        list($w,$t) = explode("_",$this->useType);
        if($t == 'COIN')
            $currency = MoneyUtls::CURRENCY_CYB;
        else
            $currency = MoneyUtls::CURRENCY_RMB;
        $item[] = AccountItem::createByBiz($trans,$this,$money,AccountItem::T_OUT,$this->balance,$currency);
        $item[] = AccountItem::createByBiz($trans,$to,$money,AccountItem::T_IN,$to->balance,$currency);
        $this->balance -= $money;
        $to->balance   += $money;
        return $item;
    }/*}}}*/
}/*}}}*/
class AccountItem extends Entity
{/*{{{*/
    const T_IN='in';
    const T_OUT='out';
    static public function createByBiz($transid,$account,$money,$type,$prebalance,$currency)
    {/*{{{*/
        $obj = new AccountItem(EntityID::create());
        $obj->account= $account;
        $obj->money= $money;
        $obj->prebalance = $prebalance;
        $obj->useType = $type;
        $obj->transid = $transid;
        $obj->currency = $currency;
        return Entity::createByBiz($obj);
    }/*}}}*/
}/*}}}*/
class AccountTrans extends Entity
{/*{{{*/
    //dealObj 交易对象
    //trans   交易事物;
    //toAcct  
    const USER = "user";
    private $items; //not store;
    static public function createByBiz($date,$ownerid,$ownercls,$dealid,$dealcls,$usetag,$note="")
    {/*{{{*/
        DBC::requireNotNull($date);
        DBC::requireNotNull($dealid);
        $obj = new AccountTrans(EntityID::create());
        $obj->occurTime = $date;
        $obj->dealobj   = $dealid;
        $obj->dealcls   = $dealcls;
        $obj->ownerid   = $ownerid;
        $obj->ownercls  = $ownercls;
        $obj->note      = $note;
        $obj->usetag    = $usetag;
        $obj->items     = array();
        $obj->relid     = 0;
        return Entity::createByBiz($obj);
    }/*}}}*/
    public function  transfer($from,$to,$money)
    {/*{{{*/
        $items=$from->transfer($this->id(),$to,$money);
        $this->items = array_merge($this->items,$items);
    } /*}}}*/
    public function  listitem()
    {/*{{{*/
        if($this->items == null)
        {
            $this->items = DDA::ins()->list_AccountItem_by_transid($this->id());
        }
        return $this->items;
    }/*}}}*/
}/*}}}*/ 

?>
