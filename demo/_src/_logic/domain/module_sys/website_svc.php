<?php
class WebsiteSvc
{
    static function add($name,$domain,$desc,$searchkey,$tags,$role)
    {/*{{{*/
        $website = Website::createByBiz($name,$desc,$domain);
        $website = ZMarkProxy::enable($website,new WebsiteTagDef());
        $website->addTags(WebsiteTagDef::SEARCHKEY,$searchkey);
        $website->addTags(WebsiteTagDef::TAGS,$tags);
        $website->addTags(WebsiteTagDef::ROLE,$role);
        return true;
    }/*}}}*/
    static function editById($siteid,$name,$domain,$desc,$searchkey,$tags,$role)
    {/*{{{*/
        $dda    = DDA::ins();
        $websiteObj  = $dda->get_website_by_id($siteid);
        if($websiteObj->domain!=$domain&&WebsiteSvc::isExistDomain($domain))
            throw new BizException("已存在相同域名的网站!");
        $websiteObj->update($name,$desc,$domain);
        $websiteMark = ZMarkProxy::enable($websiteObj,new WebsiteTagDef());
        $websiteMark->replaceTags(WebsiteTagDef::TAGS,$tags);
        $websiteMark->replaceTags(WebsiteTagDef::SEARCHKEY,$searchkey);
        $websiteMark->replaceTags(WebsiteTagDef::ROLE,$role);
        return true;
    }/*}}}*/
    static function isExistDomain($domain)
    {/*{{{*/
        $dq      = Dquery::ins();
        $website = $dq->get_website_by_domain($domain);
        return is_null($website)?false:true;
    } /*}}}*/
    static function extendArrDatas($websites)
    {/*{{{*/
        $dda    = DDA::ins();
        foreach($websites as $k=>$website)
        {
            $websites[$k]['key']  = $website['skey'];
            $websites[$k]['desc'] = $website['sdesc'];
            $websiteObj  = $dda->get_website_by_id($website['id']);
            $websiteMark = ZMarkProxy::enable($websiteObj,new WebsiteTagDef());
            $websites[$k]['tags'] = $websiteMark->getTags(WebsiteTagDef::TAGS);
            $websites[$k]['tags_str'] = implode(",",$websites[$k]['tags']);
            $websites[$k]['searchkey'] = $websiteMark->getTags(WebsiteTagDef::SEARCHKEY);
            $websites[$k]['searchkey_str'] = implode(",",$websites[$k]['searchkey']);
            $websites[$k]['role'] = $websiteMark->getTags(WebsiteTagDef::ROLE);
            $websites[$k]['role_str'] = implode(",",$websites[$k]['role']);
        }
        return $websites;
    }/*}}}*/
    static function extendSingleData($website)
    {/*{{{*/
        $website['key']  = $website['skey'];
        $website['desc'] = $website['sdesc'];
        $websiteObj  = DDA::ins()->get_website_by_id($website['id']);
        $websiteMark = ZMarkProxy::enable($websiteObj,new WebsiteTagDef());
        $website['tags'] = $websiteMark->getTags(WebsiteTagDef::TAGS);
        $website['tags_str'] = implode(",",$website['tags']);
        $website['searchkey'] = $websiteMark->getTags(WebsiteTagDef::SEARCHKEY);
        $website['searchkey_str'] = implode(",",$website['searchkey']);
        $website['role'] = $websiteMark->getTags(WebsiteTagDef::ROLE);
        $website['role_str'] = implode(",",$website['role']);
        return $website;
    }/*}}}*/
}

