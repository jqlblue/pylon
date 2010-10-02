<?php
class User extends Entity
{/*{{{*/
    const STATUS_VALID = 0;
    const STATUS_UNACTIVE = 1;
    const STATUS_FREEZED = 2;

    const PSTATUS_DEFAULT = 0;
    const PSTATUS_ALL_OK = 1;

    //STATUS  激活状态
    //PSTATUS 审核状态
    static public function createByBiz($passportid,$userName,$email,$remarks=null)
    {/*{{{*/
        $obj = new User(EntityID::create('user'));
        $obj->passportid      = $passportid;
        $obj->username    = $userName;
        $obj->email       = $email;
        $obj->remarks     = $remarks;
        $obj->randomkey   = self::genRandom();
        $obj->activeKey   = self::genRandom();
        $obj->status      = self::STATUS_UNACTIVE;
        $obj->pstatus     = self::PSTATUS_ALL_OK;
        return Entity::createByBiz($obj);
    }/*}}}*/
    public function update($phone="", $addr="", $post="", $linkman="", $mobile="", $qq="", $msn="",$remarks="")
    {/*{{{*/
        $this->phone = $phone;
        $this->addr = $addr;
        $this->post = $post;
        $this->linkman = $linkman;
        $this->mobile = $mobile;
        $this->qq = $qq;
        $this->msn = $msn;
        $this->remarks=$remarks;
    }/*}}}*/
    public function active($activeKey)
    {/*{{{*/
        if($activeKey  != $this->activekey)
            throw new BizException("激活码不正确，激活失败");
        $this->status=User::STATUS_VALID;
        $this->activeKey = self::genRandom();
    }/*}}}*/
    public function useSignKey($key)
    {/*{{{*/
        if($key!= $this->activekey)
            throw new BizException("验证码不正确,或已经使用过!");
        $this->activeKey = self::genRandom();
    }/*}}}*/
    public function isValidate(&$msg="")
    {/*{{{*/
        $noticeMsgs[User::STATUS_VALID]    = "通行证有效!";
        $noticeMsgs[User::STATUS_FREEZED]  = "通行证被冰结，请联系页面底部QQ或者去论坛咨询客服人员";
        $noticeMsgs[User::STATUS_UNACTIVE] = "通行证未激活，请检查您的注册邮箱并且点击邮件的激活链接。";
        $msg = $noticeMsgs[$this->status];
        return $this->status == User::STATUS_VALID;
    }/*}}}*/
    public function needActive()
    {/*{{{*/
        return $this->status == User::STATUS_UNACTIVE;
    }/*}}}*/
    public function needAccount()
    {/*{{{*/
        return $this->pstatus == User::PSTATUS_DEFAULT;
    }/*}}}*/
    public function needInfo()
    {/*{{{*/
        return !($this->linkman);
    }/*}}}*/
    public static function genRandom($min=100000, $max=1000000)
    {/*{{{*/
        return rand($min,$max);
    }/*}}}*/

    public function initAccount()
    {/*{{{*/
        $dda = DDA::ins();
        $coinAcc = $dda->get_Account_by_ownerid_usetype($this->id(),AccountDef::USER_COIN);
        if(empty($coinAcc))
            $settleAcct = Account::createByBiz($this->id(),AccountDef::USER_COIN);
        $moneyAcc = $dda->get_Account_by_ownerid_usetype($this->id(),AccountDef::USER_MONEY);
        if(empty($moneyAcc))
            $cashAcct   = Account::createByBiz($this->id(),AccountDef::USER_MONEY); 
    }/*}}}*/
}/*}}}*/
