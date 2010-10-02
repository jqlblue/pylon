<?php
class UserActionUtls
{/*{{{*/
}/*}}}*/
class UserAction4Admin extends AdminBaseAction
{/*{{{*/
    public function __construct()
    {/*{{{*/
        parent::__construct("");
        $this->varsLife->signSelfDefVars("userlist","id,username,createtime,email");
        $this->varsLife->setSelfDefSpace("userlist");
    }/*}}}*/
    public function do_user_list($vars,$xcontext,$dda)
    {/*{{{*/
        $filter = new Ufilter("?do=".$xcontext->_action);
        $filter->prototype->form->ajaxmode="false";
        $filter->group("sownerid","媒体id"     ,PanelBoxTpl::text("id",$vars,"#digit"));
        $filter->group("username","媒体名"     ,PanelBoxTpl::string("username",$vars,"#objname"));
        $filter->group("occurscope","注册时间" ,PanelBoxTpl::timescope($vars));
        $filter->group("email","注册邮箱"      ,PanelBoxTpl::string("email",$vars));
        $filterProp = $filter->getFilterProp();
        $filterProp->toscopeVal("begintime","endtime","createtime");
        $xcontext->filter= $filter;

        $table = new UTable3();
        $table->setDataFunc(array(__CLASS__,'listUserData'),array($filterProp,$vars->pageObj));
        $xcontext->table = $table;
    }/*}}}*/
    public function listUserData($filterProp,$pageObj)
    {/*{{{*/
        $dq    = Dquery::ins();
        $users = $dq->list_user_by_Prop($filterProp,$pageObj,'id','desc');
        if(empty($users))
            return array();
        array_walk($users,create_function('&$user,$key','
            $active_user = "<a href=\"/index.html?do=user_mactive&userid={$user[id]}\" onclick=\"if(!confirm(\'请确认要手动激活?\')){return false;}\">手动激活</a>";
            $user[status]= ($user[status]==User::STATUS_UNACTIVE)?
                "<font style=\"color:gray;\">未激活</font> ".$active_user:"<font style=\"color:orange\">已激活</font>";
        '));
        return $users;
    }/*}}}*/
    public function do_user_mactive($vars,$xcontext,$dda)
    {/*{{{*/
        #手动激活
        $userid = $vars->userid;
        $user   = BR::notNull($dda->get_user_by_id($userid),
            "没有此用户数据");
        if($user->needActive())
            $user->active($user->activekey);
        return XConst::SUCCESS;
    }/*}}}*/
}/*}}}*/
class UserAction4Stage extends StageBaseAction 
{/*{{{*/
    public function __construct()
    {/*{{{*/
        parent::__construct("submitWebreg,submitRegister,submitLogin,submitForgetpwd,submitChgMyContact,submitChgPasswd,submitReactive,submitSetMypwd,submitChgemail");
        $this->varsLife->signSelfDefVars("register","logname,passwd");
        $this->varsLife->setSelfDefSpace("register");
    }/*}}}*/
    //若在页面表单加入rule属性 则action_(form_)validate_conf检测配置无效
    public function do_main($vars,$xcontext,$dda)
    {/*{{{*/
        $user= $xcontext->user;
        $xcontext->emails = AutoemailSvc::getEmailByUserid($user->id());
        $xcontext->emailisp = EmailIsp::getAll();
    }/*}}}*/
    /*-register-*/ 
    static public function register_validate_conf($conf)
    {/*{{{*/
        $conf->input("logname","用户名")->useRule('#person');
        $conf->input("email","邮箱")->useRule('#email');
        $conf->input("passwd","密码")->useRule("#passwd");
        $conf->input("passwd2","密码确认")->useRule("#passwd");
    }/*}}}*/
    public function do_register($vars,$xcontext,$dda)
    {/*{{{*/
        $xcontext->email='';
        $xcontext->passwd='';
        $xcontext->passwd2='';
    }/*}}}*/
    static public function web_register_validate_conf($conf)
    {/*{{{*/
        $conf->input("logname","用户名")->useRule('#person');
        $conf->input("email","邮箱")->useRule('#email');
        $conf->input("passwd","密码")->useRule("#passwd");
        $conf->input("passwd2","密码确认")->useRule("#passwd");
    }/*}}}*/
    public function do_web_register($vars,$xcontext,$dda)
    {/*{{{*/
        $xcontext->email='';
        $xcontext->passwd='';
        $xcontext->passwd2='';
        $xcontext->phone='';
    }/*}}}*/
    public function do_submitWebreg($vars,$xcontext,$dda)
    {/*{{{*/
        ArgsChecker::requireTrue($vars->passwd == $vars->passwd2,'两次输入的密码不一致！');
        $user=AuthSvc::register($vars->logname,$vars->passwd,$vars->email);
        setcookie('_reg_email', $vars->email);
        $xcontext->status = "suc";
        return XConst::SUCCESS;
    }/*}}}*/
    public function do_submitRegister($vars,$xcontext,$dda)
    {/*{{{*/
        ArgsChecker::requireTrue($vars->passwd == $vars->passwd2,'两次输入的密码不一致！');
        $user=AuthSvc::register($vars->logname,$vars->passwd,$vars->email);
        setcookie('_reg_email', $vars->email);
        return XConst::SUCCESS;
    }/*}}}*/
    public function do_register_suc($vars,$xcontext,$dda)
    {/*{{{*/
        if($email = $_COOKIE['_reg_email']);
        $xcontext->logname = ($vars->haveSet("logname"))?$vars->logname:"";
        $xcontext->passwd  = ($vars->haveSet("passwd"))?$vars->passwd:"";
        $xcontext->domain = ApolloMail::getMailDomain($email);
    }/*}}}*/
    /*-forgetpasswd-*/ 
    static public function forgetpasswd_validate_conf($conf)
    {/*{{{*/
        $conf->input("username","用户名")->useRule('#person');
        $conf->input("email","邮箱")->useRule('#email');
    }/*}}}*/
    public function do_forgetpasswd($vars,$xcontext,$dda)
    {/*{{{*/
        $xcontext->err = 0;
        $xcontext->errmsg = '';
    }/*}}}*/
    public function do_submitForgetpwd($vars,$xcontext,$dda)
    {/*{{{*/
        $passpord=AuthSvc::reqSetPasswd($vars->username, $vars->email);
        $xcontext->status = "suc";
        return XConst::SUCCESS;
    }/*}}}*/
    /*-chgemail-*/
    static public function chgemail_validate_conf($conf)
    {/*{{{*/
        $conf->input("username","用户名")->useRule('#person');
        $conf->input("email","邮箱")->useRule('#email');
        $conf->input("password","密码")->useRule("#passwd");
    }/*}}}*/
    public function do_chgemail($vars,$xcontext,$dda)
    {/*{{{*/
    }/*}}}*/
    public function do_submitChgemail($vars,$xcontext,$dda)
    {/*{{{*/
//        ValidateUtls::batchValidate($this,'chgemail_validate_conf',$vars,$xcontext); 
        $sowner = $dda->get_User_by_username($vars->username);
        BR::notNull($sowner, "此用户不存在");
//        if(!$sowner->needActive())
//            throw new BizException("您[{$vars->username}]已激活,可以登录后再修改邮件!");
        $res=UcPassportSvc::changeProfile($vars->username, $vars->password, null, $vars->email);
        if(!$res['errno'])
        {
            $sowner->email = $vars->email;
//            $xcontext->_autocommit->commitAndBegin();
//            return self::do_submitReactive($vars,$xcontext,$dda);
        }
        else
        {
            throw new BizException($res['errmsg']);
        }
        $xcontext->status = "suc";
        return XConst::SUCCESS;
    }/*}}}*/
    /*active&reactive*/
    public function do_active($vars,$xcontext,$dda)
    {/*{{{*/
        $passport=$vars->p; 
        $activekey=$vars->r; 
        $res=AuthSvc::active($passport,$activekey);
        if($res) 
            $xcontext->status = "suc";
        else
            $xcontext->status = "fail";
        return XConst::SUCCESS;
    }/*}}}*/
    public function do_reactive($vars,$xcontext,$dda)
    {/*{{{*/
        $xcontext->err = 0;
        $xcontext->errmsg = '';
    }/*}}}*/
    public function do_submitReactive($vars,$xcontext,$dda)
    {/*{{{*/
        $email = $vars->email;
        $username = $vars->username;
        AuthSvc::reqReactive($username, $email);
        $xcontext->status = "suc";
        return XConst::SUCCESS;
    }/*}}}*/
    /*-setmypasswd-*/
    static public function setmypwd_validate_conf($conf)
    {/*{{{*/
        $conf->input("newpasswd","新密码")->useRule("#passwd");
        $conf->input("newpasswd2","新密码确认")->useRule("#passwd");
    }/*}}}*/
    public function do_setmypwd($vars,$xcontext,$dda)
    {/*{{{*/
        $xcontext->err = 0;
        $xcontext->errmsg = '';
    }/*}}}*/
    public function do_submitSetMypwd($vars,$xcontext,$dda)
    {/*{{{*/
        ArgsChecker::requireTrue($vars->newpasswd== $vars->newpasswd2,'两次输入的密码不一致！');
        AuthSvc::authorizedSetPasswd($vars->logname,$vars->signkey,$vars->newpasswd);
        $vars->passwd = $vars->newpasswd;
//        $xcontext->_autocommit->commitAndBegin();
//        return self::do_submitLogin($vars,$xcontext,$dda);
        $xcontext->status = "suc";
        return XConst::SUCCESS;
    }/*}}}*/
    /*-login/out-*/
    static public function login_validate_conf($conf)
    {/*{{{*/
        $conf->input("logname","登录名")->useRule('#person');
        $conf->input("passwd","密码")->useRule("#passwd");
    }/*}}}*/
    public function do_login($vars,$xcontext,$dda)
    {/*{{{*/
        $res = AuthSvc::check_login();
        if(!$res)
        {/*{{{*/
            $xcontext->logname= '';
            $xcontext->passwd = '';
        }/*}}}*/
        else
        {/*{{{*/
            $user=DDA::ins()->get_User_by_id($res[0]);
            if(!$user)
            {
                $this->sessSvc->destroy('user');
                AuthSvc::logout();
                throw new BizException("用户不存在，请注册");
            }
            $sessSvc = ObjectFinder::find('SessionSvc');
            $sessSvc->save('user',$user);
            return XConst::SUCCESS;
        }/*}}}*/
    }/*}}}*/
    public function do_relogin($vars,$xcontext,$dda)
    {/*{{{*/
        $res = AuthSvc::check_login();
        if(!$res)
        {
            return XConst::FAILURE;
        }
        else
        {
            $user=DDA::ins()->get_User_by_id($res[0]);
            if(!$user)
            {
                return XConst::FAILURE;
            }
            $sessSvc = ObjectFinder::find('SessionSvc');
            $sessSvc->save('user',$user);
            $sessSvc->save('notify', MsgSvc::getSysPmForUser($user->passportid));
            $thisurl=urldecode($_GET['thisurl']);
            if($thisurl)
            {
                header("Location:".$thisurl);
                exit;
            }
            else
            {
                return XConst::FAILURE;
            }
        }
    }/*}}}*/
    public function do_submitLogin($vars,$xcontext,$dda)
    {/*{{{*/
        try
        {/*{{{*/
            list($userid,$name)=AuthSvc::login($vars->logname,$vars->passwd,false);
        }/*}}}*/
        catch(Exception $exception)
        {/*{{{*/
            throw $exception;
        }/*}}}*/
        return XConst::SUCCESS;
    }/*}}}*/
    public function do_synlogin($vars,$xcontext,$dda)
    {/*{{{*/
//        $code = $vars->code;
        $code = $vars->auth;
        
        parse_str(uc_authcode($code, 'DECODE', UC_KEY), $get);

        if(time() - $get['time'] > 3600) {
            exit('Authracation has expiried');
        }
        if(empty($get)) {
            exit('Invalid Request');
        }
        $action = $get['action'];
        $uid = intval($get['uid']);

        $res = UcPassportSvc::getUser($uid, 1);

        if($res['data']['username']){
            AuthSvc::setCookie($uid, $res['data']['username']);
            header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
        }
        return XConst::SUCCESS;
    }/*}}}*/
    public function do_logout($vars,$xcontext,$dda)
    {/*{{{*/
        $this->sessSvc->destroy('user');
        AuthSvc::logout();
        return XConst::SUCCESS;
    }/*}}}*/ 
    /*-myinfo-*/
    public function do_myinfo($vars,$xcontext,$dda)
    {/*{{{*/
        $user0 = $this->sessSvc->get('user');
        $user  = BR::notNull( $dda->get_User_by_passportid($user0->passportid),"no found this user!");
        $xcontext->addr  = $user->addr;
        $xcontext->email = $user->email;
        $xcontext->post = $user->post;
        $xcontext->linkman = $user->linkman;
        $xcontext->gender = $user->gender;
        $xcontext->phone = $user->phone;
        $xcontext->mobile = $user->mobile;
        $xcontext->qq = $user->qq;
        $xcontext->msn = $user->msn;
    }/*}}}*/
    /*-chpmycontact-*/
    public function do_chgMycontact($vars,$xcontext,$dda)
    {/*{{{*/
        $user0 = $this->sessSvc->get('user');
        $user = BizResult::ensureNotNull(
            $dda->get_User_by_passportid($user0->passportid), 
            "no found this user!");
        $xcontext->addr = $user->addr;
        $xcontext->post = $user->post;
        $xcontext->linkman = $user->linkman;
        $xcontext->gender = $user->gender;
        $xcontext->mobile = $user->mobile;
        $xcontext->qq = $user->qq;
        $xcontext->msn = $user->msn;
        $xcontext->email = $user->email;
        $xcontext->bakemail = $user->bakemail;
        $xcontext->phone = $user->phone;
        $xcontext->genders = array('男', '女');
        if($msgs = $this->sessSvc->get('notify')){
            $msgs['data'] = @array_values($msgs['data']);
            for($i=0;$i<count($msgs['data']);$i++){
                $msg = $msgs['data'][$i];
                if($msg['subject'] == 'NOTICE INFO'){
                    unset($msgs['data'][$i]);
                }
            }
            $this->sessSvc->save('notify', $msgs);
        }
    }/*}}}*/
    public function do_submitChgMyContact($vars,$xcontext,$dda)
    {/*{{{*/
        //session&&dda必须同步更新
        $xcontext->genders = array('男', '女');
        $user = $this->sessSvc->get('user');
        $user->update($vars->phone, $vars->addr, $vars->post, $vars->linkman, $vars->mobile, 
            $vars->qq, $vars->msn);

        $user1 = BizResult::ensureNotNull($dda->get_User_by_passportid($user->passportid), "no found this user!");
        $user1->update($vars->phone, $vars->addr, $vars->post, $vars->linkman, $vars->mobile, 
                 $vars->qq, $vars->msn);
        return XConst::SUCCESS;
    }/*}}}*/
    /*-chpasswd-*/
    static public function chgPasswd_validate_conf($conf)
    {/*{{{*/
        $conf->input("oldpasswd","原密码")->useRule("#passwd");
        $conf->input("newpasswd","新密码")->useRule("#passwd");
        $conf->input("newpasswd2","新密码确认")->useRule("#passwd");
    }/*}}}*/
    public function do_chgPasswd($vars,$xcontext,$dda)
    {/*{{{*/
    }/*}}}*/
    public function do_submitChgPasswd($vars,$xcontext,$dda)
    {/*{{{*/
        if($vars->newpasswd != $vars->newpasswd2)
        {
            throw new BizException('输入密码不一致');
        }
        AuthSvc::changePasswd($xcontext->passportName,$vars->oldpasswd,$vars->newpasswd);
        $this->successMsg="修改密码成功";
        return XConst::SUCCESS;
    }/*}}}*/
}/*}}}*/
?>
