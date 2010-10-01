<?php
interface IUserconf
{
    public function setUserConf($userid,$confkey);
}
class UserconfImpl implements IUserconf
{
    public function setUserConf($userid,$confKey)
    {/*{{{*/
        $user = DDA::ins()->get_User_by_id($userid);
        BR::notNull($user,"不存在该用户[userid:{$userid}]");
        $confVal  = BR::isTrue($_REQUEST['confVal'],"不存在配置内容");        
        $userConf = UserconfSvc::set($userid,$confKey,$confVal);
        return $userConf->id();
    }/*}}}*/
    public function getUserConf($userid,$confKey)
    {
        $user = DDA::ins()->get_User_by_id($userid);
        BR::notNull($user,"不存在该用户[userid:{$userid}]");
        $userConf = UserconfSvc::get($userid,$confKey,$confVal);
        return $userConf->getval();
    }
}

