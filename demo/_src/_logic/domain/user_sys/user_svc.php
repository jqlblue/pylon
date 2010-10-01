<?php
class UserSvc
{/*{{{*/
    static public function  createOne($passportid,$userName,$email,$remarks=null)
    {/*{{{*/
        $user = User::createByBiz($passportid,$userName,$email,$remarks);
        $user->initAccount();
        $objs=PropertyObj::create();
        $objs->user  = $user;
        return $objs;
    }/*}}}*/
    static public function initAccount($userid)
    {/*{{{*/
        $user = BR::notNull(DDA::ins()->get_User_by_id($userid),"不存在该用户");
        $user->initAccount();
    }/*}}}*/
}/*}}}*/
