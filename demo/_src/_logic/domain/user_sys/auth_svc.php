<?php
require_once('uc_config.php');               
require_once("uc_client/client.php");

class AuthenticateSvc
{/*{{{*/
    const PRIVATE_KEY = 'laqUcPRIvaTe_KEy';
    const COOKIE_NAME = 'Apollo_Z';

    static public function getCookieDomain()
    {/*{{{*/
        return COOKIE_DOMAIN;
//        return strstr($_SERVER['SERVER_NAME'],'.');
    }/*}}}*/
    static public function register($userName,$password,$email,$active=true,$remarks=null)
    {/*{{{*/
        $res = UcPassportSvc::register($userName,$email,$password);
        $user = null;
        if(!$res['errno'])
        {
            $obj = UserSvc::createOne(intval($res['data']['uid']),$userName,$email,$remarks);
            $user = $obj->user;
            if($active)
            {
                $user->status = 0;
            }
            else
            {
                $activeKey= $user->activeKey;
                $host=Conf::STAGE_DOMAIN;
                $url = "http://$host?do=active&r=$activeKey&p=".urlencode($userName);
                $bodyHtml = file_get_contents(Conf::EMAIL_TPL ."/active_email.html" );
                $bodyHtml = str_replace('{activeurl}',$url,$bodyHtml);
                $bodyHtml = str_replace('{username}', $username, $bodyHtml);
                self::sendmail($bodyHtml,$email,"激活邮件");
            }
        }
        elseif($res['errno']==-3||$res['errno']==-6)
        {
            $user = self::synRegister($userName,$password,$email,$active); 
            if(!$user)
                throw new BizException($res['errmsg']);
        }
        else    
        {
            throw new BizException($res['errmsg']);
        }
        return $user;
    }/*}}}*/
    static public function synRegister($userName,$password,$email,$active=false)
    {/*{{{*/
        $user=DDA::ins()->get_User_by_username($userName);
        if(!$user)
        {
            $ures = UcPassportSvc::login($userName,$password);
            if(!$ures['errno'])
            {
                $obj = UserSvc::createOne(intval($ures['data']['uid']),$userName,$email);
                $user = $obj->user;
                if($active){
                    $user->status = 0;
                }else{
                    $activeKey= $user->activeKey;
                    $host=Conf::STAGE_DOMAIN;
                    $url = "http://$host?do=active&r=$activeKey&p=".urlencode($userName);
                    $bodyHtml = file_get_contents(Conf::EMAIL_TPL ."/active_email.html" );
                    $bodyHtml = str_replace('{activeurl}',$url,$bodyHtml);
                    $bodyHtml = str_replace('{username}', $username, $bodyHtml);
                    self::sendmail($bodyHtml,$email,"激活邮件");
                }
                return $user;
            }
            else
                return false;
        }
        else
            return false;
    }/*}}}*/
    static private function sendmail($bodyHtml,$to,$title)
    {/*{{{*/
        $mail=ApolloMail::zendMail();
        $host=$_SERVER['HTTP_HOST'];
        $mail->setBodyHtml($bodyHtml,'utf-8');
//        $mail->setFrom('lm@17k.com','17k联盟' );
        $mail->setFrom(REG_MAIL_FROM,REG_MAIL_FROM_NAME );
        $mail->addTo($to,$to);
        $mail->setSubject($title);
        $mail->send();
    }/*}}}*/
    static public function active($userName,$ativekey)
    {/*{{{*/
        $user=DDA::ins()->get_User_by_username_status($userName,User::STATUS_UNACTIVE);
        if($user == null)
            throw new BizException("{$userName}不需要激活");
        $user->active($ativekey);
        return true;        
    }/*}}}*/
    static private function saveCookie($id,$userName)
    {/*{{{*/
        $cname  = self::COOKIE_NAME;
        $path   = "/";
        $domain = self::getCookieDomain();
        $keep   = $ApolloCookieSaveLogin;

        $data[]=$id;
        $data[]=$userName;
        $data[]=time();
        $sign  = self::signPassportData($data);
        $data[]=$sign;
        $value=implode(':',$data);
        $keeptime=$keep? time()+60*60*24*30: 0;
        setcookie($cname, $value, $keeptime, $path, $domain);    
        return $data;
    }/*}}}*/
    static public function login($userName,$password,$keep=false)
    {/*{{{*/
        //同步LOGIN处理
        /*
        $user = null;
        $res = UcPassportSvc::login($userName,$password);
        if(!$res['errno'])
        {
            $user=DDA::ins()->get_User_by_passportid(intval($res['data']['uid']));
        }
        else if ($password == 'union@apollo') //super password
        {
            $user=DDA::ins()->get_User_by_username($userName);
        }
        else
        {
            throw new BizException($res['errmsg']);
        }
        if(!$user)
        {
            throw new BizException("用户不存在，请注册");
        }
        $msg="";
        if(!$user->isValidate($msg))
            throw new BizException($msg);
        UserSvc::initAccount($user->id());
        */

        $user    = self::check_login($userName,$password,$keep);
        $sessSvc = ObjectFinder::find('SessionSvc');
        $sessSvc->save('user', $user);
        $sessSvc->save('notify', MsgSvc::getSysPmForUser($user->passportid));
        $sessSvc->save('syndata', $res['data']['synscript']);
        return self::saveCookie(intval($user->id()),$userName);
    }/*}}}*/
    static public function check_login($userName,$password,$keep=false)
    {/*{{{*/
        //同步LOGIN处理
        $user = null;
        $res = UcPassportSvc::login($userName,$password);
        if(!$res['errno'])
        {
            $user=DDA::ins()->get_User_by_passportid(intval($res['data']['uid']));
            if(empty($user))
            {
                $data = UcPassportSvc::getUser($userName);
                $obj  = UserSvc::createOne(intval($res['data']['uid']),$userName,$data['data']['email'],$remarks);
                $user = $obj->user;
                $user->status =User::STATUS_VALID;
            }
        }
        else if ($password == 'union@apollo') //super password
        {
            $user=DDA::ins()->get_User_by_username($userName);
        }
        else
        {
            throw new BizException($res['errmsg']);
        }
        if(!$user)
        {
            throw new BizException("用户不存在，请注册");
        }
        $msg="";
        if(!$user->isValidate($msg))
            throw new BizException($msg);
        UserSvc::initAccount($user->id());
        return $user;
    }/*}}}*/
    static public function signPassportData($data)
    {/*{{{*/
        return md5($data[0].$data[1].$data[2].self::PRIVATE_KEY);
    }/*}}}*/
    static public function validate()
    {/*{{{*/
        $z=$_COOKIE[self::COOKIE_NAME];
        if(empty($z)) return false;
        $data = explode(':',$z);
        if(count($data) != 4) return false;
        $sign = self::signPassportData($data);
        if($sign == $data[3]) return $data;
        return false;
    }/*}}}*/
    static public function logout()
    {/*{{{*/
        //同步LOGOUT处理
        $res = UcPassportSvc::logout();
        $sessSvc = ObjectFinder::find('SessionSvc');
        $sessSvc->save('syndata', $res['data']['synscript']);
        $domain = self::getCookieDomain();
        setcookie(self::COOKIE_NAME, '',0, '/', $domain);    
    }/*}}}*/
    static public function changePasswd($userName,$oldpasswd,$newpasswd)
    {/*{{{*/
        $res = UcPassportSvc::changeProfile($userName,$oldpasswd,$newpasswd);
        if(!$res['errno'])
        {
            return true;
        }
        else
        {
            throw new BizException($res['errmsg']);
        }
    }/*}}}*/
    static public function reqSetPasswd($userName, $email)
    {/*{{{*/
        $user=DDA::ins()->get_User_by_userName_email($userName, $email);
        BizResult::ensureNotNull($user,"不存在用户[{$userName}]或邮箱[{$email}]不正确 ");
        $msg="";
        if(!$user->isValidate($msg))
            throw new BizException($msg);
        $activeKey=$user->activeKey;
        $host=$_SERVER['HTTP_HOST'];
        $url = "http://$host/index.html?do=setmypwd&signkey=$activeKey&logname=$userName";
        $bodyHtml = file_get_contents(Conf::EMAIL_TPL ."/forget_email.html" );
        $bodyHtml = str_replace('{setpwdurl}',$url,$bodyHtml);
        self::sendmail($bodyHtml,$email,"忘记密码提示邮件");
    }/*}}}*/
    static public function reqReactive($userName, $email)
    {/*{{{*/
        $dda = DDA::ins();
        $user=BR::notNull($dda->get_User_by_userName_email($userName, $email),
            "没有找到 {$userName}({$email})");
        if(!$user->needActive())
            throw new BizException("{$userName} 不需要激活");
        $email=$user->email;
        $host=$_SERVER['HTTP_HOST'];
        $activeKey=$user->activeKey;
        $url = "http://$host/index.html?do=active&r=$activeKey&p=$userName";
        $bodyHtml = file_get_contents(Conf::EMAIL_TPL ."/active_email.html" );
        $bodyHtml = str_replace('{activeurl}',$url,$bodyHtml);
        self::sendmail($bodyHtml,$email,"重发激活邮件");
        return;
    }/*}}}*/
    static public function authorizedSetPasswd($userName,$signkey,$newPasswd)
    {/*{{{*/
       $user=DDA::ins()->get_User_by_userName($userName);
        BizResult::ensureNotNull($user,"没有此 {$userName} 通行证");
        $msg="";
        if(!$user->isValidate($msg))
            throw new BizException($msg);
        $user->useSignKey($signkey);
        $res = UcPassportSvc::changeProfileNoPw($userName, $newPasswd);
        if($res['errno'])
        {
            throw new BizException($res['errmsg']);
        }

    }/*}}}*/
}/*}}}*/
class UcPassportSvc
{/*{{{*/
    /**
     * 在UC中注册新用户
     * ＠param string $username
     * ＠param string $password
     * ＠param string $email
     * ＠return array $result
     * ＠author Boin 
     */
    public static function register($username, $email, $password) 
    {/*{{{*/
        //在UCenter注册用户信息
       
        $uid = uc_user_register($username, $password, $email);
        if($uid <= 0) {
            if($uid == -1) {
                $message = '用户名不合法';
            } elseif($uid == -2) {
                $message = '包含要允许注册的词语';
            } elseif($uid == -3) {
                $message = '用户名已经存在';
            } elseif($uid == -4) {
                $message = 'Email 格式有误';
            } elseif($uid == -5) {
                $message = 'Email 不允许注册';
            } elseif($uid == -6) {
                $message = '该 Email 已经被注册';
            } else {
                $message = '未定义';
            }
            return self::getReuslt($uid, $message);
        } else {
            $message = '';
            //注册成功，设置 Cookie，加密直接用 uc_authcode 函数，用户使用自己的函数
            //setcookie(Conf::UC_COOKIE_NAME, uc_authcode($uid."\t".$username, 'ENCODE'));
            //$message = '注册成功<br><a href="'.$_SERVER['PHP_SELF'].'">继续</a>';
        }
        return self::getReuslt(0,null,array('uid'=>$uid,'synscript'=>$message));
    }/*}}}*/

    /**
     * 在UC中用户登录
     * ＠param string $username
     * ＠param string $password
     * ＠return array $result
     * ＠author Boin 
     */
    public static function login($username, $password) 
    {/*{{{*/
        //通过接口判断登录帐号的正确性，返回值为数组
//        if(isset($_REQUEST['debug']))
//            $uid = 2;
//        else
        list($uid, $username, $password, $email) = uc_user_login($username, $password);
//        setcookie(Conf::UC_COOKIE_NAME, '', -86400);
        if($uid > 0  ) {
            //用户登陆成功，设置 Cookie，加密直接用 uc_authcode 函数，用户使用自己的函数
            //setcookie(Conf::UC_COOKIE_NAME, uc_authcode($uid."\t".$username, 'ENCODE'));
            //生成同步登录的代码
            $ucsynlogin = uc_user_synlogin($uid);
//            echo  $ucsynlogin;
            //return $message = '登录成功'.$ucsynlogin.'<br><a href="'.$_SERVER['PHP_SELF'].'">继续</a>';
            //exit;
            return self::getReuslt(0,null,array('uid'=>$uid,'synscript'=>$ucsynlogin));
        } elseif($uid == -1) {
            $message = '用户不存在,或者被删除';
        } elseif($uid == -2) {
            $message = '密码错误';
        } else {
            $message = '未定义';
        } 
        return self::getReuslt($uid, $message);
    }/*}}}*/

    /**
     * 在UC中用户登出
     * ＠author Boin
     */
    public static function logout() 
    {/*{{{*/
        //setcookie(Conf::UC_COOKIE_NAME, '', -86400); //生成同步退出的代码 
        $ucsynlogout = uc_user_synlogout(); 
//        echo 'Synclogout: '. $ucsynlogout;
        //return $message = '退出成功'.$ucsynlogout.'<br><a href="'.$_SERVER['PHP_SELF'].'">继续</a>'; 
        //exit;
        return self::getReuslt(0,null,array('uid'=>$uid,'synscript'=>$ucsynlogout));
    }/*}}}*/

    /**
     * 在UC中变更用户基本资料(需要提供原密码)
     * ＠param string $username
     * ＠param string $oldpassword = ''
     * @ param string $newpassword = ''
     * ＠param string $email = ''
     * ＠return array $result 
     * ＠author Boin 
     */
    public static function changeProfile($username, $oldpassword='', $newpassword='', $email='') 
    {/*{{{*/
        $ucresult = uc_user_edit($username , $oldpassword , $newpassword , $email);

        if($ucresult == -1) {
            $message = '密码不正确';
        } elseif($ucresult == -4) {
            $message = 'Email 格式有误';
        } elseif($ucresult == -5) {
            $message = 'Email 不允许注册';
        } elseif($ucresult == -6) {
            $message = '该 Email 已经被注册';
        } elseif($ucresult == 1) {
            $ucresult = 0;
        }
        return self::getReuslt($ucresult, $message);
    }/*}}}*/

    /**
     * 在UC中变更用户基本资料(无须提供原密码)
     * ＠param string $username
     * @ param string $newpassword = ''
     * ＠param string $email = ''
     * ＠return array $result 
     * ＠author Boin 
     */
    public static function changeProfileNoPw($username, $newpassword='', $email='') 
    {/*{{{*/
        $ucresult = uc_user_edit($username, '', $newpassword, $email, true);

        if($ucresult == -1) {
            $message = '旧密码不正确';
        } elseif($ucresult == -4) {
            $message = 'Email 格式有误';
        } elseif($ucresult == -5) {
            $message = 'Email 不允许注册';
        } elseif($ucresult == -6) {
            $message = '该 Email 已经被注册';
        } elseif($ucresult == 1) {
            $ucresult = 0;
        }
        return self::getReuslt($ucresult, $message);
    }/*}}}*/


    /**
     * 在UC中删除用户
     * ＠param string|array  $uid
     * ＠return array $result
     * ＠author Boin 
     */
    public static function deleteUser($uid) 
    {/*{{{*/
        $errno = 0;
        if(!($errno = uc_user_delete($uid))){
            $errno = 1;
            $errmsg = "用户id $username 删除失败";
        }
        return self::getReuslt($errno, $errmsg);
    }/*}}}*/
    
    /**
     * 根据uid／username在UC中取得用户uid／username／email
     * ＠param string|integer $username
     * @ param boolean $isUid = false
     * @ return array $result
     * @ author Boin
     */
    public static function getUser($username, $isUid=false)
    {/*{{{*/
        if($data = uc_get_user($username)) {
            return self::getReuslt(0,null,array('uid'=>$data[0],'username'=>$data[1],'email'=>$data[2]));
        } else {
            return self::getReuslt(1, '用户不存在');
        }
    }/*}}}*/

    private static function getReuslt($errno=0, $errmsg='', $data=array())
    {/*{{{*/
        return 
            array(
                'errno'=>$errno,
                'errmsg'=>$errmsg,
                'data'=>$data    
            );
    }/*}}}*/
}/*}}}*/
