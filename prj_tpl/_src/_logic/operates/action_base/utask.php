<?php
interface UStepCmd
{/*{{{*/
    const ST_UNDEFINE = "undefine";
    const ST_UNSTART  = "unstart";
    const ST_SAVEOVER = "saveover";
    const ST_PASS     = "pass";
    const ACS_RANDOM  = x1;
    const ACS_BACK    = x2;

    public function udom($vars,$ucontext);   //显示的udom 代码
    public function cmd();
    public function setup($vars,$xcontext);//
    public function input_validate_conf($conf);
    public function save($vars,$xcontext); //每步完成后，进行数据的保存
    public function save_over();
    public function execute($vars,$xcontext);  //任务完成时，执行的方法
    public function status();
    public function add_access($acs);
    public function del_access($acs);
    public function match_access($acs);
}/*}}}*/
class UBaseStep implements UStepCmd
{/*{{{*/
    public function __construct($cmd,$desc,$keeps=array())
    {/*{{{*/
        $this->cmd        = $cmd;
        $this->desc       = $desc;
        $this->status     =  UStepCmd::ST_UNSTART ;
        $this->vars       =  PropertyObj::create();
        $this->varsDesc   =  PropertyObj::create();
        $this->initVars   = PropertyObj::create();
        $this->vars->defaultValue(true,"");
        $this->keepvars   = $keeps;
        $this->access     = UStepCmd::ACS_BACK ;
    }/*}}}*/
    public function add_access($acs)
    {/*{{{*/
        $this->access  = $this->access | $acs;
    }/*}}}*/
    public function del_access($acs)
    {/*{{{*/
        $this->access  = $this->access & (~$acs);
    }/*}}}*/
    public function match_access($acs)
    {/*{{{*/
        return ($this->access &  $acs  )  ==  $acs ;
    }/*}}}*/
    public function cmd()
    {/*{{{*/
        return $this->cmd;
    }/*}}}*/
    public function desc()
    {/*{{{*/
        return $this->desc;
    }/*}}}*/
    public function udom($vars,$ucontext)
    {/*{{{*/
    }/*}}}*/
    public function keeps()
    {/*{{{*/
        return $this->keepvars;
    }/*}}}*/
    public function setup($vars,$xcontext)
    {/*{{{*/
    }/*}}}*/
    public function input_validate_conf($conf)
    {/*{{{*/
    }/*}}}*/
    public function save($vars,$xcontext)
    {/*{{{*/
        foreach($this->vars->getPropArray() as $i=>$v) 
        {
            $msg = $this->varsDesc->haveSet($i) ?  $this->varsDesc->$i : "没有設置[$i]项数据!";
            BR::isTrue($vars->haveSet($i)&&$vars->$i,$msg);
            $this->vars->$i = $vars->$i;
        }
    }/*}}}*/
    public function save_over()
    {/*{{{*/
        $this->status = UStepCmd::ST_SAVEOVER ;
    }/*}}}*/
    public function execute($vars,$xcontext)
    {/*{{{*/
        $vars->merge($this->vars);
    }/*}}}*/
    public function status()
    {/*{{{*/
        return $this->status;
    }/*}}}*/
}/*}}}*/

class UCondStep extends UBaseStep
{/*{{{*/
    public function __construct($cmd,$disp,$condname,$subCmds)
    {/*{{{*/
        parent::__construct($cmd,$disp);
        $this->subCmds  = $subCmds;
        $this->condname = $condname;
        $this->status   =  UStepCmd::ST_UNDEFINE;
    }/*}}}*/
    public function choiceCmd($name,$vars)
    {/*{{{*/
        $v = $vars->$name;
        BR::notNull($this->subCmds[$v],"没有符合条件的步骤,condtion:$v");
        $cmd = $this->subCmds[$v] ;
        return $cmd;
    }/*}}}*/
    public function setup($vars,$xcontext)
    {/*{{{*/
        $cmd = $this->choiceCmd($this->condname,$vars);
        $this->usecmd = $cmd;
    }/*}}}*/
    public function save($vars,$xcontext)
    {/*{{{*/
        parent::save($vars,$xcontext);
        $this->usecmd->save($vars,$xcontext);
    }/*}}}*/
    public function execute($vars,$xcontext)
    {/*{{{*/
        parent::execute($vars,$xcontext);
        $this->usecmd->execute($vars,$xcontext);
    }/*}}}*/
    public function udom($vars,$ucontext)
    {/*{{{*/
        return $this->usecmd->udom($vars,$ucontext);
    }/*}}}*/
}/*}}}*/

class UTask 
{/*{{{*/
    public $curcmd    = null;
    public $vars      = null;
    public $leftCmds  = array();
    public $rightCmds = array();
    public function __construct()
    {
        $this->vars = PropertyObj::create();

    }
    public function defineSteps()
    {/*{{{*/
        $objs = func_get_args();
        foreach($objs as $obj)
        {
            array_push($this->rightCmds, $obj);
        }
        $this->curcmd = array_shift($this->rightCmds);
    }/*}}}*/
    public function nextStep($vars,$xcontext)
    {/*{{{*/
        do{
            array_push($this->leftCmds,$this->curcmd);
            $this->curcmd = array_shift($this->rightCmds);
            $this->curcmd->setup($vars,$xcontext);
        }
        while($this->curcmd->status == UStepCmd::ST_PASS) ;
    }/*}}}*/
    public function prevStep($vars,$xcontext)
    {/*{{{*/
        do{
            array_unshift($this->rightCmds,$this->curcmd);
            $this->curcmd = array_pop($this->leftCmds);
            $this->curcmd->setup($vars,$xcontext);
        }
        while($this->curcmd->status == UStepCmd::ST_PASS) ;
    }/*}}}*/
    public function stepSave($xcontext)
    {/*{{{*/
        $conf = new InputConfs();
        $this->curcmd->input_validate_conf($conf);
        $conf->batchValidate($this->vars);
        $this->curcmd->save($this->vars,$xcontext);
        $this->curcmd->save_over();
    }/*}}}*/
    public function choseStep($step)
    {/*{{{*/
        if($this->curcmd->cmd()  == $step) return ;
        $found = false;
        $oriSaved = $this->leftCmds;
        $this->leftCmds=array();
        $moveCmds= array();
        array_unshift($this->rightCmds,$this->curcmd);

        foreach ($oriSaved as $cmd)
        {/*{{{*/
            if($cmd->cmd() == $step)
            {
                $found = true;
            }
            if($found)
            {
                array_push($moveCmds,$cmd);
            }
            else
            {
                array_push($this->leftCmds,$cmd);
            }
        }/*}}}*/
        $this->rightCmds = array_merge($moveCmds ,$this->rightCmds);
        $this->curcmd = array_shift($this->rightCmds);

    }/*}}}*/
    public function executSteps($xcontext)
    {/*{{{*/
        $allcmds = $this->allcmds();
        foreach($allcmds as $cmd)
        {
            if ($cmd->status  == UStepCmd::ST_PASS) continue;
            $cmd->execute($this->vars,$xcontext);
        }
    }/*}}}*/
    public function process($nextcmd,$vars,$xcontext)
    {/*{{{*/
        $this->vars->merge($vars);
        $this->_action = $xcontext->_action;

        if ($nextcmd == "cancel") return "END";
        if ($nextcmd == "end")
        {/*{{{*/
            if ($vars->have("cmdresut") && $vars->cmdresut=="over")
            {
                $this->stepSave($xcontext);
                $this->executSteps($xcontext);
                return "END" ;
            }
        }/*}}}*/
        if ($nextcmd == "chose")   $this->choseStep($vars->step);
        if ($nextcmd == "prev" )   $this->prevStep($vars,$xcontext);
        if ($nextcmd == "next")
        {
            if( $vars->have("cmdresut") && $vars->cmdresut=="over")
            {
                //NOT F5 ;
                if($vars->have("curcmd") && $vars->curcmd===$this->curcmd->cmd() )
                {
                    $this->stepSave($xcontext);
                    $this->nextStep($vars,$xcontext);
                }


            }
//            {
//                if ($vars->have("cmdresut") && $vars->cmdresut=="over") $this->stepSave($xcontext);
//                if ($nextcmd == "next" )   $this->nextStep($vars,$xcontext);
//            }
        }
//        do { 
//            BR::notNull($this->curcmd,"不存在curcmd");
//            $this->curcmd->setup($vars,$xcontext);
//            if($this->curcmd->status  != UStepCmd::ST_PASS) break;
//            $this->nextStep();
//        }while(1);
    }/*}}}*/


    public function allcmds()
    {/*{{{*/
        $all_cmds = array_merge($this->leftCmds,array($this->curcmd),$this->rightCmds);
        return $all_cmds;
    }/*}}}*/
    public function naviDom()
    {/*{{{*/
        $u = new UModuleDom();
        $all_cmds = $this->allcmds();
        $stepindex = count($all_cmds) ;
        $canAccess= true;
        foreach(array_reverse($all_cmds) as  $cmd)
        {/*{{{*/
            $desc = $cmd->desc();
            $step = $cmd->cmd();
            if(!$cmd->match_access(UStepCmd::ACS_BACK))
            {
                //前一步骤不能回退后，之前所有步骤都不可访问!!
                $canAccess = false;
            }
            $cls="";
            if($cmd   == $this->curcmd)
            {
                $item = $u->a_href("?do={$this->_action}&cmd=chose&step=$step",$desc);
                $cls="cur";

            }
            else
            {
                if($canAccess)
                {
                    switch($cmd->status)
                    {
                    case UStepCmd::ST_SAVEOVER:
                        $item = $u->a_href("?do={$this->_action}&cmd=chose&step={$step}",
                            $desc." <img src=\"/images/admin/correct.gif\" height=\"13\">" );
                        break;
                    case UStepCmd::ST_UNSTART:
                        $item = $u->font_title("请完成之前的步骤",$desc);
                        break;
                    case UStepCmd::ST_UNDEFINE:
                        $item = $u->font_title("请完成之前的步骤",$desc);
                    case UStepCmd::ST_PASS:
                        $item = $u->font_title("已跳过",$desc."(已跳过)");
                    }
                }
                else
                {
                    $item = $u->a_href("#",
                        $desc." <img src=\"/images/admin/correct.gif\" height=\"13\">" );
                }
            }
            $steps[] = $u->li_class($cls,$u->span($u->strong($stepindex),$item));
            $stepindex --;
        }/*}}}*/
        $steps = array_reverse($steps);
        $dom = $u->div_class("utasknavi",$u->ul_class("steps",$steps),
                    $u->ul_class("steps-arrow",$stepsarrow),$u->div_class("clear"));
        return $dom;
    }/*}}}*/
    public function show($vars)
    {/*{{{*/
        $ucontext = PropertyObj::create();
        $u = new UModuleDom();
        $navi = $this->naviDom();
        if(count($this->leftCmds)  >0 )
        {/*{{{*/
            $prev = array_pop($this->leftCmds);
            array_push($this->leftCmds,$prev);
            if($prev->match_access(UStepCmd::ACS_BACK))
            {
                $but[] = $u->span($u->button_onclick_class_value("window.location='?do={$this->_action}&cmd=prev'","button","上一步"));
            }

        }/*}}}*/
        if (count($this->rightCmds) > 0)
        {/*{{{*/
            $but[] = $u->hidden_id_value("curcmd",$this->curcmd->cmd);
            $but[] = $u->hidden_id_value("cmd","next");
            $but[] = $u->hidden_id_value("cmdresut","over");
            $but[] = $u->span($u->submit_id_value_class("next","下一步","button"));
        }/*}}}*/
        else
        {/*{{{*/
            $but[] = $u->hidden_id_value("cmd","end");
            $but[] = $u->hidden_id_value("cmdresut","over");
            $but[] = $u->span($u->submit_id_value_class("end","完成","button"));
        }/*}}}*/
        $item = $this->curcmd->udom($vars,$ucontext);
        $content =
            $u-> div_class("utask",
                $navi,
                $u->div_class("utaskcontent",$item),
                $u->div_class("utaskbottom",$but)
        );
        $form = $u->form_id_action_method("utask","?do=".$this->_action,"post",$content);
        $form->show();
    }/*}}}*/
}/*}}}*/

class SmartyUtls
{/*{{{*/
    public function udom_tpl($tpl,$vars,$ucontext)
    {/*{{{*/
        XView::$viewer->smarty->assign("_vars",$vars);
        if($ucontext)
            foreach($ucontext->getPropArray() as $k => $v) 
            {
                XView::$viewer->smarty->assign($k,$v);
            }
        $u = new UModuleDom();
        $theme = ApolloCtrl::adminTheme();
        $file= $theme->tpl($tpl);
        $content= XView::$viewer->smarty->fetch($file);
        return $u->html($content);
    }/*}}}*/
}/*}}}*/

class ApolloStep extends UBaseStep
{/*{{{*/
    public function udom4SmartyTpl($tpl,$ucontext=null)
    {/*{{{*/
        foreach($this->initVars->getPropArray() as $k => $v) 
        {
            XView::$viewer->smarty->assign($k,$v);
        }
        foreach($this->vars->getPropArray() as $k => $v) 
        {
            XView::$viewer->smarty->assign($k,$v);
        }
        if($ucontext)
        foreach($ucontext->getPropArray() as $k => $v) 
        {
            XView::$viewer->smarty->assign($k,$v);
        }
        $u = new UModuleDom();
        $theme = ApolloCtrl::adminTheme();
        $file= $theme->tpl($tpl);
        $content= XView::$viewer->smarty->fetch($file);
        return $u->html($content);
    }/*}}}*/
    public function udom4PHPTpl($tpl)
    {/*{{{*/
        $u = new UModuleDom();
        $content = file_get_contents($tpl);
        return $u->html($content);
    }/*}}}*/
}/*}}}*/
