<?php

class Action_login  extends ActionBase
{/*{{{*/
    static $_tag  = array("xxx","yyy");
    static $_name = "LOGIN" ;
    static public  function _validate_conf($conf)
    {/*{{{*/

        $conf->input("logname","用户名")->useRule('#person');
        $conf->input("passwd","密码")->useRule("#passwd");
    }/*}}}*/
    public function __construct()
    {
        parent::__construct();
        XAop::pos(XAop::ERROR)->replace_by_match_cls(__CLASS__,  new NormalErrorPoc());
    }
    public function  _run($request ,$xcontext)
    {/*{{{*/
        if($request->loginSubmit)
        {
            $dda = DDA::ins();
            $logname=$request->logname;
            $staff = BizResult::ensureNotNull(
                $dda->get_Staff_by_logname($request->logname),
                "没有此帐号");

            if($staff->login($request->passwd))
            {
                $this->sessSvc->save('staff', $staff);
                return XNext::action("main");
            }
            throw new BizException('密码不正确！');
        }
        return   XNext::useTpl("AUTO");
    }/*}}}*/
}/*}}}*/

class Action_logout  extends ActionBase
{/*{{{*/
    public function _run($request ,$xcontext)
    {/*{{{*/
        $this->sessSvc->destroy('staff');
        return XNext::action("login");
    }/*}}}*/
}/*}}}*/

class StaffAction extends AdminActionBase  
{/*{{{*/
    public function __construct()
    {/*{{{*/
        parent::__construct("loginSubmit,submit_staff_add,submit_staff_editpwd");
    }/*}}}*/
    /*-login-*/
    static public  function login_validate_conf($conf)
    {/*{{{*/
        $conf->input("logname","用户名")->useRule('#person');
        $conf->input("passwd","密码")->useRule("#passwd");
    }/*}}}*/
    public function do_login($vars,$xcontext,$dda)
    {/*{{{*/
        if($xcontext->errorMsg)
            $xcontext->remove("errorMsg");
    }/*}}}*/
    public function do_loginSubmit($vars,$xcontext,$dda)
    {/*{{{*/
        $logname=$vars->logname;
        $staff = BizResult::ensureNotNull($dda->get_Staff_by_logname($vars->logname),
            "没有此帐号");
        if($staff->login($vars->passwd))
        {
            $this->sessSvc->save('staff', $staff);
            return XConst::SUCCESS;
        }
        throw new BizException('密码不正确！');
    }/*}}}*/

    public function do_logout($vars,$xcontext,$dda)
    {/*{{{*/
        $this->sessSvc->destroy('staff');
        return XConst::SUCCESS;
    }/*}}}*/

    public function do_staff_list($vars,$xcontext,$dda)
    {/*{{{*/
        $dq= DQuery::ins();
        $table = new UTable3();
        $datas = $dq->list_Staff();
        array_walk($datas,create_function('&$item,$key','
            $item["delete"]= ('.$xcontext->staff->id().'==$item[id])?"自己":"<a href=\"\" onclick=\"if(confirm(\'确认要删除么?\')){window.location=\'/index.html?do=staff_del&id={$item[id]}\'}\">删除</a>";
        '));
        $table->datas = $datas;
        $xcontext->table = $table;
    }/*}}}*/
    public function do_staff_del($vars,$xcontext,$dda)
    {/*{{{*/
        $obj= $this->sessSvc->get('staff');
        if($obj->id()!=intval($vars->id))
            $dda->del_Staff_by_id(intval($vars->id));
        return XConst::SUCCESS;
    }/*}}}*/

    static public  function staff_add_validate_conf($conf)
    {/*{{{*/
        $conf->input("logname","用户名")->useRule('#logname');
        $conf->input("passwd","密码")->useRule("#passwd");
        $conf->input("passwd2","密码确认")->useRule("#passwd");
    }/*}}}*/
    public function do_staff_add($vars,$xcontext,$dda)
    {/*{{{*/
        $u = ApolloUI::udom();
        $items = $u->table_class("datatable border-noall",
            $u->tr($u->td("用户名"),$u->td($u->text_id_value("logname",""))),
            $u->tr($u->td("密码"),$u->td($u->password_id_value("passwd",""))),
            $u->tr($u->td("密码确认"),$u->td($u->password_id_value("passwd2",""))),
            $u->tr($u->td_colspan(2,$u->ext_std_submit("staff_add","创建")))
        );
        $xcontext->form=$u->uform("staff_add","?do=staff_add",$items);
    }/*}}}*/
    public function do_submit_staff_add($vars,$xcontext,$dda)
    {/*{{{*/
        ArgsChecker::requireTrue($vars->passwd == $vars->passwd2, "两次输入的密码不一致");
        if($dda->get_Staff_by_logname($vars->logname))
        {
            throw new BizException('已存在此运营人员');
        }

        $staff = Staff::createByBiz($vars->logname,$vars->passwd);
        return XConst::SUCCESS;
    }/*}}}*/

    static public  function staff_editpwd_validate_conf($conf)
    {/*{{{*/
        $conf->input("oldpwd","旧密码")->useRule('#passwd');
        $conf->input("newpwd","新密码")->useRule('#passwd');
        $conf->input("newpwd2","新密码确认")->useRule('#passwd');
    }/*}}}*/
    public function do_staff_editpwd($vars,$xcontext,$dda)
    {/*{{{*/
        $u = ApolloUI::udom();
        $items = $u->table_class("datatable border-noall",
            $u->tr($u->td("旧密码"),$u->td($u->password_id_value("oldpwd",""))),
            $u->tr($u->td("新密码"),$u->td($u->password_id_value("newpwd",""))),
            $u->tr($u->td("新密码确认"),$u->td($u->password_id_value("newpwd2",""))),
            $u->tr($u->td_colspan(2,$u->ext_std_submit("staff_editpwd","修改")))
        );
        $xcontext->form=$u->uform("staff_editpwd","?do=staff_editpwd",$items);
    }/*}}}*/
    public function do_submit_staff_editpwd($vars,$xcontext,$dda)
    {/*{{{*/
        ArgsChecker::requireTrue($vars->newpwd== $vars->newpwd2, "新密码两次输入不一致");
        $obj= $this->sessSvc->get('staff');
        $staff = BizResult::ensureNotNull($dda->get_Staff_by_id($obj->id()),
            "没有此帐号");
        $staff->changePasswd($vars->oldpwd,$vars->newpwd);
        return XConst::SUCCESS;
    }/*}}}*/
    public function do_pay_list($vars,$xcontext,$dda)
    {/*{{{*/
    }/*}}}*/
}/*}}}*/
