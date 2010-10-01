<?php
class WebsiteAction extends AdminBaseAction
{
    public function __construct()
    {/*{{{*/
        parent::__construct("submit_website_add,submit_website_edit");
    }/*}}}*/
    public function do_website_list($vars,$xcontext,$dda)
    {/*{{{*/
        $websites = Dquery::ins()->list_website($vars->pageObj,"rank","desc");
        $table = new UTable3();
        $table->setCurData(WebsiteSvc::extendArrDatas($websites));
        $xcontext->table = $table;
    }/*}}}*/
    public function do_website_add($vars,$xcontext,$dda)
    {
        $xcontext->roletags = WebsiteTagDef::listRoleTags();
    }
    public function do_website_edit($vars,$xcontext,$dda)
    {/*{{{*/
        $id = BR::isTrue($vars->id,"请传递网站id!");
        $website = Dquery::ins()->get_website_by_id($id);
        $website = WebsiteSvc::extendSingleData($website);
        $xcontext->website = $website;
        $xcontext->roletags = WebsiteTagDef::listRoleTags();
    }/*}}}*/
    public function do_submit_website_add($vars,$xcontext,$dda)
    {/*{{{*/
        $domain      = BR::isTrue($vars->domain,"请输入网站域名!");
        if(!WebsiteSvc::isExistDomain($domain))
        {
            $name    = BR::isTrue($vars->name,"请输入网站名称");
            $role     = BR::isTrue($vars->role,"请设定网站所属角色");
            $tags     = BR::isTrue($vars->tags,"请设定网站所属分类");
            $searchkey  = BR::isTrue($vars->searchkey,"请设定网站搜索key");
            $desc    = $vars->desc;
            $searchkey = $vars->searchkey;
            WebsiteSvc::add($name,$domain,$desc,$searchkey,$tags,$role);
            return XConst::SUCCESS;
        }
        throw new BizException("网站已存在!");
    }/*}}}*/
    public function do_submit_website_edit($vars,$xcontext,$dda)
    {/*{{{*/
        $domain   = BR::isTrue($vars->domain,"请输入网站域名!");
        $name     = BR::isTrue($vars->name,"请输入网站名称");
        $role     = BR::isTrue($vars->role,"请设定网站所属角色");
        $tags     = BR::isTrue($vars->tags,"请设定网站所属分类");
        $searchkey  = BR::isTrue($vars->searchkey,"请设定网站搜索key");
        $desc    = $vars->desc;
        $searchkey = $vars->searchkey;
        WebsiteSvc::editById($vars->siteid,$name,$domain,$desc,$searchkey,$tags,$role);
        return XConst::SUCCESS;
    }/*}}}*/
    public function do_website_del($vars,$xcontext,$dda)
    {
        $dda->del_website_by_id($vars->id);
        return XConst::SUCCESS;
    }
}
