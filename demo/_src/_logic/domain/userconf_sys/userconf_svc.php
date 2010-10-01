<?php
class UserconfSvc
{
    public function set($userid,$confKey,$confVal)
    {/*{{{*/
        $userConf = DDA::ins()->get_userconf_by_userid_confkey($userid,$confKey);
        if($userConf)
            $userConf->update($confVal);
        else
            $userConf = Userconf::createByBiz($userid,$confKey,$confVal);
        return $userConf;
    }/*}}}*/
    public function get($userid,$confKey)
    {/*{{{*/
        $userConf = DDA::ins()->get_userconf_by_userid_confkey($userid,$confKey);
        BR::notNull($userConf,"不存在此用户配置");
        return $userConf;
    }/*}}}*/
}

