<?php
class PassportType
{
    const EMAIL = "email";
}

class Autopassport extends Entity
{
    static public function createPrivKey($userid)
    {/*{{{*/
        return md5($userid.time().rand(1000,9999));
    }/*}}}*/
    static public function createByBiz($userid,$isp,$passtype,$passport,$password)
    {/*{{{*/
        $obj = new Autopassport(EntityID::create('autoemail'));
        $obj->userid = $userid;
        $obj->isp = $isp;
        $obj->passtype = $passtype;
        $obj->passport = $passport;
        $obj->privatekey = self::createPrivkey($userid);
        $obj->password   = $password; //暂时明文
        $obj->lifestatus = LifeStatus::NORMAL;
        return Entity::createByBiz($obj);
    }/*}}}*/

    

}
