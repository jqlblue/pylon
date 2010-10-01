<?php
class CommonActions //extends SpriteAction
{/*{{{*/
    public function __construct()
    {/*{{{*/
       parent::__construct("submit_fstatus,submitVisible,submitNote,submitAttr,submit_label_up");
        $this->varsLife->signSelfDefVars("common","ecls,eid");
        $this->varsLife->setSelfDefSpace("common");
    }/*}}}*/

    static public function fstauts_form_def($u,$values=array())
    {/*{{{*/
        $items = $u->ul(
            $u->li($u->radio_name_options("skey",$values)),
            $u->li($u->ext_std_submit("fstatus","设置"))
        );
        return $u->uform("chglfstatus","?do=fstatus",$items);
    }/*}}}*/
    public function do_fstatus($vars,$xcontext,$dda)
    {/*{{{*/
        $ecls  = $vars->ecls;
        $ddafun="get_{$ecls}_by_id"; 
        $obj = call_user_func(array($dda,$ddafun),$vars->eid);
        $otherOpts = LifeStatus::otherOpts($obj->lifestatus);
        $xcontext->form=self::fstauts_form_def(ApolloUI::udom(),$otherOpts);
    }/*}}}*/
    public function do_submit_fstatus($vars,$xcontext,$dda)
    {/*{{{*/
        $ecls  = $vars->ecls;
        $ddafun="get_{$ecls}_by_id"; 
        $obj   = call_user_func(array($dda,$ddafun),$vars->eid);
        self::sendEntityStatusEvent($ecls,$vars->eid);
        if( method_exists($obj,"setlifestatus"))
        {
            call_user_func(array($obj,"setlifestatus"),$vars->skey);
        }
        else
        {
            $obj->lifestatus = $vars->skey;
        }
        $lsValue  =  $vars->skey;
        $lsName   =  LifeStatus::nameOf($lsValue);
        echo "success,{$ecls},{$vars->eid},{$lsValue},{$lsName}";
        return "NULL"; 
    }/*}}}*/
    protected static function sendEntityStatusEvent($cls,$id)
    {/*{{{*/
        if($cls == 'Advert')
            ReleseEvents::notify4Advert($id,__METHOD__);
        if($cls == 'AdvPosition')
            ReleseEvents::notify4Advposition($id,__METHOD__);
    }/*}}}*/

    public function do_upnote($vars,$xcontext,$dda)
    {/*{{{*/
        $ecls  = $vars->ecls;
        $ddafun="get_{$ecls}_by_id"; 
        $obj = call_user_func(array($dda,$ddafun),$vars->eid);
        $xcontext->note = $obj->note;
    }/*}}}*/
    public function do_upattr($vars,$xcontext,$dda)
    {/*{{{*/
        $ecls  = $vars->ecls;
        $ddafun="get_{$ecls}_by_id"; 
        $obj = call_user_func(array($dda,$ddafun),$vars->eid);
        $attr = $vars->attribute;
        if( method_exists($obj,"get{$attr}"))
        {
            $xcontext->_value =  call_user_func(array($obj,"get{$attr}"));
        }
        else
            $xcontext->_value = $obj->$attr;
        $xcontext->attribute  = $attr;
    }/*}}}*/
    public function do_submitNote($vars,$xcontext,$dda)
    {/*{{{*/
        $ecls  = $vars->ecls;
        $ddafun="get_{$ecls}_by_id"; 
        $obj = call_user_func(array($dda,$ddafun),$vars->eid);
        $obj->note = $vars->note;
        return XConst::SUCCESS;
    }/*}}}*/
    public function do_submitAttr($vars,$xcontext,$dda)
    {/*{{{*/
        $ecls  = $vars->ecls;
        $ddafun="get_{$ecls}_by_id"; 
        $obj = call_user_func(array($dda,$ddafun),$vars->eid);
        $attr = $vars->attribute;
        if( method_exists($obj,"set{$attr}"))
        {
            $showattr = call_user_func(array($obj,"set{$attr}"),$vars->$attr);
        }
        else
        {
            $obj->$attr = $vars->$attr;
        }

        $lsValue = str_replace(",","&#44;",is_string($showattr)?$showattr:$obj->$attr);
        $lsKey   = $vars->attribute;
        echo "success,{$ecls},{$vars->eid},{$lsValue},{$lsKey}";
        return "NULL"; 
    }/*}}}*/

    public function do_setvisible($vars,$xcontext,$dda)
    {/*{{{*/
        $ecls  = $vars->ecls;
        $ddafun="get_{$ecls}_by_id"; 
        $obj = call_user_func(array($dda,$ddafun),$vars->eid);
        $otherOpts = Visible::otherOptions($obj->visible);
        $xcontext->visiableOpt= $otherOpts;
    }/*}}}*/
    public function do_submitVisible($vars,$xcontext,$dda)
    {/*{{{*/
        $ecls  = $vars->ecls;
        $ddafun="get_{$ecls}_by_id"; 
        $obj = call_user_func(array($dda,$ddafun),$vars->eid);
        $obj->visible = $vars->skey;
        $lsValue  =  $vars->skey;
        $lsName   =  Visible::nameOf($lsValue);
        echo "success,{$ecls},{$vars->eid},{$lsValue},{$lsName}";
        return "NULL"; 
    }/*}}}*/
    public function do_preview($vars,$xcontext,$dda)
    {/*{{{*/
        $ecls   = $vars->ecls;
        $ddafun = "get_{$ecls}_by_id"; 
        $obj    = call_user_func(array($dda,$ddafun),$vars->eid);
        $domain = APPConf::ADMIN_DOMAIN_NAME;
        $xcontext->code = $obj->preview($domain);
    }/*}}}*/
    public function do_popiframe($vars,$xcontext,$dda)
    {/*{{{*/
        foreach($_GET as $k=>$var)
        {
            if($k=="do")
            {
                unset($_GET['do']);
            }
            else
            {
                if($k=="action")
                {
                    unset($_GET['action']);
                    $k = "do";
                }
                $queryString .= "&$k=$var";
            }
        }
        $url = "index.html?".$queryString;
        $xcontext->url = $url;
    }/*}}}*/

    public function do_pos_utask($vars,$xcontext,$dda)
    {/*{{{*/
        $svc = ObjectFinder::find('SessionSvc');
        $utask = $svc->get("utask");
        if(!$utask)
           header("Location:?do=main");
        $cmd = null;
        $process = "doing";
        if($vars->have("cmd"))
            $cmd = $vars->cmd;
        $xcontext->utask =$utask;
        $add_pdtid = $svc->get("pos_add_pdtid");
        if($add_pdtid>0)
        {
            $xcontext->pdtinfo = DDA::ins()->get_Product_by_id($add_pdtid);
            $xcontext->add_pdtid = $add_pdtid;
        }
        try{

            if($utask->process($cmd,$vars,$xcontext) == "END")
            {
                $this->eventMC->notKeepData();
                header("Location:?do=pos_list");
            }
        }
        catch( Exception $e)
        {
            $xcontext->process = $process;
            $vars->remove("cmdresut");
            $vars->remove("cmd");
            throw $e;
        }
        $xcontext->_vars=$utask->vars;
        $xcontext->process = $process;
        $svc->save("utask",$utask);
        $svc->del("errmsg");
        $xcontext->utask   = $utask;
        $vars->remove("cmdresut");
        $vars->remove("cmd");
    }/*}}}*/
    public function do_utask($vars,$xcontext,$dda)
    {/*{{{*/
        $svc = ObjectFinder::find('SessionSvc');
        $utask = $svc->get("utask");
        $xcontext->_vars=$utask->vars;
        $xcontext->cmdCount = count($utask->allcmds());
        BR::notNull($utask,"没有設置任务");
        $cmd = null;
        $process = "doing";
        if($vars->have("cmd"))
            $cmd = $vars->cmd;
        $xcontext->utask =$utask;
        try{
            if($utask->process($cmd,$vars,$xcontext) == "END")
            {
                $this->eventMC->notKeepData();
                header("Location:?do=utaskover");
            }
        }
        catch( Exception $e)
        {
            $xcontext->process = $process;
            $vars->remove("cmdresut");
            $vars->remove("cmd");
            throw $e;
        }
        $xcontext->process = $process;
        $svc->save("utask",$utask);
        $svc->del("errmsg");
        $xcontext->utask   = $utask;
        $vars->remove("cmdresut");
        $vars->remove("cmd");
    }/*}}}*/
    public function do_utaskover($vars,$xcontext,$dda)
    {/*{{{*/
        $svc = ObjectFinder::find('SessionSvc');
        $xcontext->curaction  = $svc->get("utask_curaction");
        $xcontext->nextaction = $svc->get("utask_nextaction");
        $xcontext->nextaction_desc = $svc->get("utask_nextaction_desc");
        $xcontext->process = "done";
        $appTplPath =  Conf::APP_ROOT . "/" . Conf::ADMIN_TPL_PATH ;
        $baseTplPath = Conf::APP_ROOT . "/" . Conf::BASE_TPL_PATH."/" ;
        $xcontext->_autoview = InterceptUtls::actionTpl("utask", $appTplPath,$baseTplPath);
    }/*}}}*/
    
    public function do_label_up($vars,$xcontext,$dda)
    {/*{{{*/

        $ecls  = $vars->ecls;
        $ddafun="get_{$ecls}_by_id"; 
        $obj = call_user_func(array($dda,$ddafun),$vars->eid);
        BR::notNull($obj, "没有找到此实体对象: [$ecls]");
        $tagObj = ZMarkProxy::enable($obj,new DiyTagDef()); 
        $xcontext->label = implode(" ", $tagObj->getTags(DiyTagDef::LABEL));
    }/*}}}*/

    public function do_submit_label_up($vars,$xcontext,$dda)
    {/*{{{*/
        $ecls  = $vars->ecls;
        $ddafun="get_{$ecls}_by_id"; 
        $obj = call_user_func(array($dda,$ddafun),$vars->eid);
        BR::notNull($obj, "没有找到此实体对象: [$ecls]");
        $tagObj = ZMarkProxy::enable($obj,new DiyTagDef()); 
        $tagObj->replaceTags(DiyTagDef::LABEL,$vars->label);
        return XConst::SUCCESS;
    }/*}}}*/
    public function do_tags_show($vars,$xcontext,$dda)
    {/*{{{*/
        $def = new DiyTagDef();
        $zmark = ZMarkQuery::ins($def);
        $tags = $zmark->listTags("Material",DiyTagDef::LABEL);
        $xcontext->convdata= $tags;
    }/*}}}*/
}/*}}}*/

class Action_fstatus extends AdminActionBase
{/*{{{*/
    protected static function sendEntityStatusEvent($cls,$id)
    {/*{{{*/
        if($cls == 'Advert')
            ReleseEvents::notify4Advert($id,__METHOD__);
        if($cls == 'AdvPosition')
            ReleseEvents::notify4Advposition($id,__METHOD__);
    }/*}}}*/
    static public function fstatus_form_def($u,$ecls,$eid,$values=array())
    {/*{{{*/
        $items = $u->ul(
            $u->li($u->radio_name_options("skey",$values)),
            $u->li($u->hidden_id_value("ecls",$ecls)),
            $u->li($u->hidden_id_value("eid",$eid)),
            $u->li($u->ext_std_submit("fstatus","设置"))
        );
        return $u->uform("chglfstatus","?do=fstatus",$items);
    }/*}}}*/
    public function  _run($request ,$xcontext)
    {/*{{{*/
        $dda = DDA::ins();
        if($request->submit_fstatus)
        {
            $ecls  = $request->ecls;
            $ddafun="get_{$ecls}_by_id"; 
            $obj   = call_user_func(array($dda,$ddafun),$request->eid);
            self::sendEntityStatusEvent($ecls,$request->eid);
            if( method_exists($obj,"setlifestatus"))
            {
                call_user_func(array($obj,"setlifestatus"),$request->skey);
            }
            else
            {
                $obj->lifestatus = $request->skey;
            }
            $lsValue  =  $request->skey;
            $lsName   =  LifeStatus::nameOf($lsValue);
            return XNext::nothing();
        }
        else
        {
            $ecls  = $request->ecls;
            $ddafun="get_{$ecls}_by_id"; 
            $obj = call_user_func(array($dda,$ddafun),$request->eid);
            $otherOpts = LifeStatus::otherOpts($obj->lifestatus);
            $xcontext->form=self::fstatus_form_def(ApolloUI::udom(),$ecls,$request->eid,$otherOpts);
            return XNext::useTpl("pop_admin.html");
        }
    }/*}}}*/
}/*}}}*/
