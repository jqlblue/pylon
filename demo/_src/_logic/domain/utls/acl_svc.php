<?php
interface IAclRule
{/*{{{*/
    public function match($act);
}/*}}}*/
class AclRule
{/*{{{*/
    const ALLOW  = 'allow';
    const REFUSE = 'refuse';
    const UNKNOW = 'unknow';

    public function __construct($access)
    {/*{{{*/
        DBC::requireNotNull($access);
        $this->access = $access;
    }/*}}}*/
    public function checkAccess($result)
    {/*{{{*/
        if($this->access == self::ALLOW && $result)
            return true; 
        if($this->access == self::REFUSE && $result)
            return false;
        return self::UNKNOW;
    }/*}}}*/
    public function canAccess($act)
    {/*{{{*/
        $result = $this->match($act);
        return $this->checkAccess($result);
    }/*}}}*/
    public function match($act)
    {/*{{{*/
        return true;
    }/*}}}*/
}/*}}}*/
class AllAclRule extends AclRule implements IAclRule
{/*{{{*/
    public function __construct($access)
    {/*{{{*/
        parent::__construct($access);
    }/*}}}*/
    public function match($act)
    {/*{{{*/
        return true;
    }/*}}}*/
}/*}}}*/
class RegExpAclRule extends AclRule implements IAclRule
{/*{{{*/
    public function __construct($access,$pattern)
    {/*{{{*/
        parent::__construct($access);
        $this->pattern = $pattern;
    }/*}}}*/
    public function match($act)
    {/*{{{*/
        return preg_match($this->pattern,$act);
    }/*}}}*/
}/*}}}*/
class ListDefAclRule extends AclRule implements IAclRule
{/*{{{*/
    public function __construct($access,$listArr)
    {/*{{{*/
        parent::__construct($access);
        $this->listArr = $listArr;
    }/*}}}*/
    public function match($act)
    {/*{{{*/
        return in_array($act,$this->listArr);
    }/*}}}*/
}/*}}}*/

class RoleRule
{/*{{{*/
    public static function allActs($access)
    {/*{{{*/
        return new AllAclRule($access);
    }/*}}}*/
    public static function listDef($arr,$access)
    {/*{{{*/
        return new ListDefAclRule($access,$arr);
    }/*}}}*/
    public static function regExp($pattern,$access)
    {/*{{{*/
        return new RegExpAclRule($access,$pattern);
    }/*}}}*/
    public static function selfDefRule($cls,$access)
    {/*{{{*/
        return new $cls($access);
    }/*}}}*/
    public static function initByPri()
    {/*{{{*/
        $rules = func_get_args();
        return $rules;
    }/*}}}*/
}/*}}}*/
class RoleRuleManage
{/*{{{*/
    static $RoleDef = null;
    static public function regRoleRules()
    {/*{{{*/
        self::register('AdminSysRole');
        self::register('TestRole');
    }/*}}}*/
    static public function register($clsName)
    {/*{{{*/
        $cls     = new ReflectionClass($clsName);
        $roleObj = new $clsName();
        $app = self::getAppByObj($roleObj);
        $methods = $cls->getMethods();
        foreach($methods as $m)
        {
            $methodName=$m->getName();
            list($tag,$role)=split('_',$methodName,2);
            if(strcasecmp($tag,'role')==0)
            {
                self::$RoleDef[$app][strtolower($role)] = call_user_func(
                    array($roleObj,$methodName));
            }
        }
    }/*}}}*/
    static public function listRoles($roleObj)
    {/*{{{*/
        if(is_null(self::$RoleDef))
            self::regRoleRules();
        $roleDefs = self::$RoleDef; 
        $sys = self::getAppByObj($roleObj);
        $roles = array_keys($roleDefs[$sys]);
        return $roles;
    }/*}}}*/
    static public function findRoleRules($roleObj,$roleName)
    {/*{{{*/
        $sys = self::getAppByObj($roleObj);
        $roles = self::listRoles($roleObj);
        DBC::requireTrue(in_array($roleName,$roles),$roleName.' is not exist!');
        $roleDefs  = self::$RoleDef;
        $roleRules = $roleDefs[$sys][$roleName]; 
        return $roleRules;     
    }/*}}}*/
    protected function getAppByObj($obj)
    {/*{{{*/
        return strtolower(get_class($obj));
    }/*}}}*/
}/*}}}*/

class RoleSvc
{/*{{{*/
    public static function init($roleObj)
    {/*{{{*/
        DBC::requireNotNull($roleObj);
        $cls = __CLASS__;
        return new $cls($roleObj);
    }/*}}}*/
    public function __construct($roleObj)
    {/*{{{*/
        $this->roleObj = $roleObj;
    }/*}}}*/

    public function accessAct($roleName,$act)
    {/*{{{*/
        $roleRuleObj = $this->findRoleRule($roleName);
        return $this->checkRoleAuth($act,$roleRuleObj);
    }/*}}}*/
    public function accessSys($roleName,$curSysRole)
    {/*{{{*/
        $allowSys = $this->getSysRole($roleName);
        if(!empty($allowSys) && in_array($curSysRole,$allowSys))
            return true;
        else
            return false;
    }/*}}}*/
    public function getSysRole($roleName)
    {/*{{{*/
        $allowSys = $this->roleObj->getSysRole($roleName);
        return $allowSys;
    }/*}}}*/

    public function listRole()
    {/*{{{*/
        $roleNames = RoleRuleManage::listRoles($this->roleObj);
        return $roleNames;     
    }/*}}}*/

    /*{{{*/
    protected function findRoleRule($roleName)
    {/*{{{*/
        $roleRuleObj = RoleRuleManage::findRoleRules($this->roleObj,$roleName);
        return $roleRuleObj;
    }/*}}}*/
    protected function checkRoleAuth($act,$roleRules)
    {/*{{{*/
        if(empty($roleRules))
            return false;
        foreach($roleRules as $ruleObj)
        {
            $r = $ruleObj->canAccess($act);
            if(is_bool($r))
            {
                return $r;
            }
        }
    }/*}}}*/
    /*}}}*/

}/*}}}*/

/*********** How To Use ************
 * $roleSvc = RoleSvc::init($roleObj);
 * $roleSvc->accessAct($roleName,$act); // return true/false
 * $roleSvc->listRole(); // return array
 ************************************/

############### APP Adminsys ##############
class AdminSysRole
{/*{{{*/
    const R_SYS_ADMIN  = 'sys_admin';
    const R_BIZ_OP     = 'biz_op';
    const R_BD         = 'widen';
    const R_BD_LEADER  = 'bd_leader';
    const R_FLUXVIEWER = 'fluxviewer';

    static public function name_of($role)
    {/*{{{*/
       $names[self::R_SYS_ADMIN]  = "管理员"; 
       $names[self::R_BIZ_OP]     = "运营专员";
       $names[self::R_BD]         = "扩展专员";
       $names[self::R_BD_LEADER]  = "扩展总负责人";
       $names[self::R_FLUXVIEWER] = "数据查看专员";
       return $names[$role];
    }/*}}}*/
    public function getSysRole($role)
    {/*{{{*/
       $names[self::R_SYS_ADMIN]  = array(SysRole::ADMIN,SysRole::BD); 
       $names[self::R_BIZ_OP]     = array(SysRole::ADMIN,SysRole::BD);
       $names[self::R_BD]         = array(SysRole::BD);
       $names[self::R_FLUXVIEWER] = array(SysRole::ADMIN,SysRole::BD);
       return $names[$role];
    }/*}}}*/
    public function role_sys_admin()
    {/*{{{*/
        $allowAll = RoleRule::AllActs('allow');
        $rules    = RoleRule::initByPri($allowAll);
        return $rules;
    }/*}}}*/
    public function role_biz_op()
    {/*{{{*/
        $adminsysRule  = RoleRule::listDef(array('crtacct','chrole'),'refuse');
        $allRule       = RoleRule::allActs('allow');
        $rules         = RoleRule::initByPri($adminsysRule,$allRule);
        return $rules;
    }/*}}}*/
    public function role_widen()
    {/*{{{*/
        $commonActs = array('header','menu','navi','logout','main','savehis','backto','welcome');
        $widenActs = array('lastclk','sownerlist','sownerinfo','pdtinfo'
            ,'statcode','playerlist','playctrls','advlist','preview','rdlist');

        $commonRule      = RoleRule::listDef($commonActs,AclRule::ALLOW);
        $singleDateRule  = RoleRule::regExp("/singledate/i",AclRule::ALLOW);
        $comparefluxRule = RoleRule::regExp("/compareflux/i",AclRule::ALLOW);
        $singlefulxRule  = RoleRule::regExp("/single(.*)flux/i",AclRule::ALLOW);
        $widenRule       = RoleRule::listDef($widenActs,AclRule::ALLOW);
        $refuseAllRule   = RoleRule::allActs(AclRule::REFUSE);

        $rules = RoleRule::initByPri($commonRule,$singleDateRule
            ,$comparefluxRule,$singlefulxRule,$widenRule,$refuseAllRule);
        return $rules;
    }/*}}}*/
    public function role_bd_leader()
    {/*{{{*/
        $commonActs = array('header','menu','navi','logout','main','savehis','backto','welcome');
        $widenActs = array('lastclk','sownerlist','sownerinfo','pdtinfo'
            ,'statcode','playerlist','playctrls','advlist','preview','rdlist');

        $commonRule      = RoleRule::listDef($commonActs,AclRule::ALLOW);
        $singleDateRule  = RoleRule::regExp("/singledate/i",AclRule::ALLOW);
        $comparefluxRule = RoleRule::regExp("/compareflux/i",AclRule::ALLOW);
        $singlefulxRule  = RoleRule::regExp("/single(.*)flux/i",AclRule::ALLOW);
        $widenRule       = RoleRule::listDef($widenActs,AclRule::ALLOW);
        $refuseAllRule   = RoleRule::allActs(AclRule::REFUSE);

        $rules = RoleRule::initByPri($commonRule,$singleDateRule
            ,$comparefluxRule,$singlefulxRule,$widenRule,$refuseAllRule);
        return $rules;
    }/*}}}*/
    public function role_fluxviewer()
    {/*{{{*/
        $commonActs = array('header','menu','navi','logout','main','savehis','backto','welcome');
        $viewerActs = array('settlesingledateflux','settlecompareflux','singlesettleflux');

        $commonRule = RoleRule::listDef($commonActs,AclRule::ALLOW);
        $viewerRule = RoleRule::listDef($viewerActs,AclRule::ALLOW);
        $refuseAllRule = RoleRule::allActs(AclRule::REFUSE);
        $rules = RoleRule::initByPri($commonRule,$viewerRule,$refuseAllRule);
        return $rules;
    }/*}}}*/
}/*}}}*/

### Just For Test ###
class TestRole
{/*{{{*/
    const R_GOLD   = 'gold';
    const R_SILVER = 'silver';
    const R_COPPER = 'copper';

    public function role_gold()
    {/*{{{*/
        $allowAll = RoleRule::AllActs(AclRule::ALLOW); //allow all Actions
        $rules    = RoleRule::initByPri($allowAll); // init Rules order by PRI.
        return $rules;
    }/*}}}*/
    public function role_silver()
    {/*{{{*/
        $obj1 = RoleRule::AllActs(AclRule::ALLOW); //allow all Actions
        $obj2  = RoleRule::listDef(array('create','del','edit'),AclRule::REFUSE);//refuse array acts
        $rules = RoleRule::initByPri($obj2,$obj1); //Rule will check obj2 first 
        return $rules;
    }/*}}}*/
    public function role_copper()
    {/*{{{*/
        $obj2  = RoleRule::listDef(array('create','del','edit'),AclRule::REFUSE);//refuse array acts
        $obj3  = RoleRule::regExp("/gold/i",AclRule::REFUSE);//refuse by Exp
        $obj4  = RoleRule::selfDefRule('FluxRoleRule',AclRule::REFUSE);
        $obj1  = RoleRule::AllActs(AclRule::ALLOW); //allow all Actions
        $rules = RoleRule::initByPri($obj2,$obj3,$obj4,$obj1);
        return $rules;
    }/*}}}*/
}/*}}}*/

class FluxRoleRule extends AclRule implements IAclRule
{/*{{{*/
    public function __construct($access)
    {/*{{{*/
        parent::__construct($access);
    }/*}}}*/
    public function match($act)
    {/*{{{*/
        if(stripos($act,'flux')===false)
            return false;
        else
            return true;
    }/*}}}*/
}/*}}}*/
/** eg: **********************
 * $roleSvc = RoleSvc::init(new TestRole());
 * // Check Access Act
 * $roleSvc->accessAct('silver','del'); // return false
 * // list All Role
 * $roleSvc->listRole(); // return array
 ************************************/
?>
