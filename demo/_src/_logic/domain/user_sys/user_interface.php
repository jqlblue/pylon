<?php
interface IUserSvc
{
    public function userReg($username,$password,$email);
    public function userLogin($username,$password);
    public function userSave($username,$password,$email,$linkman="",$remarks="");
}
class UserImpl implements IUserSvc
{
    private static function formatUser($userObj)
    {/*{{{*/
        $user['id'] = (int)$userObj->id();
        $user['username'] = $userObj->username;
        $user['email']    = $userObj->email;
        $user['create']   = $userObj->createTime();
        return $user;
    }/*}}}*/
    public function userReg($username,$password,$email)
    {/*{{{*/
        $user = self::formatUser(AuthenticateSvc::register($username,$password,$email,true));
        return $user;
    }/*}}}*/
    public function userSave($username,$password,$email,$linkman="",$remarks="")
    {/*{{{*/
        $user =  DDA::ins()->get_User_by_username($username);
        if(!empty($user))
        {
            return self::formatUser($user);
        }
        else
        {
            $user = AuthenticateSvc::register($username,$password,$email,true,$remarks);
            $user->update("", "", "", $linkman);
            return  self::formatUser($user);
        }
    }/*}}}*/
    public function userLogin($username,$password)
    {/*{{{*/
        //list($userid,$username) = AuthenticateSvc::login($username,$password);
        $user = AuthenticateSvc::check_login($username,$password);
        if(!empty($user))
        {
            return self::formatUser($user);
        }
        else
        {
            return NULL;
        }
    }/*}}}*/
    public function userChgpwd($username,$password,$newpassword)
    {/*{{{*/
        AuthenticateSvc::changePasswd($username,$password,$newpassword);
        return "ok";
    }/*}}}*/
}
