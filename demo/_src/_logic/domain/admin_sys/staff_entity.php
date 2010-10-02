<?php
class StaffRole
{/*{{{*/
    const ADMIN  = 'admin';
}/*}}}*/

class Staff extends Entity
{/*{{{*/
    const SKEY="bannerlab";
    static public function createByBiz($name,$passwd,$role=StaffRole::ADMIN)
    {/*{{{*/
        $obj = new Staff(EntityID::create());  
        $obj->logname=$name;
        $passwd=trim($passwd);
        $obj->passwd=md5($passwd.Staff::SKEY);
        $obj->role=$role;
        return Entity::createByBiz($obj);
    }/*}}}*/
    public function login($passwd)
    {/*{{{*/
        $passwd=trim($passwd);
        return $this->passwd == md5($passwd.Staff::SKEY);
    }/*}}}*/
    public function changePasswd($oldPasswd,$newPasswd)
    {/*{{{*/
        if($this->passwd !=md5($oldPasswd.Staff::SKEY))
            throw new BizException('旧密码不对!');
        $this->passwd=md5($newPasswd.Staff::SKEY);
    }/*}}}*/
}/*}}}*/
?>
