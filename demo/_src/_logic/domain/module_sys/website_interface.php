<?php
interface IWebsite
{
    public function getWebsiteByRole($role);
    public function getTagsByRole($role);
    public function getWebsiteByDomain($domain);
    public function searchWebsite($k);
}
class WebsiteImpl implements IWebsite
{
    public function formatWebsites($websites)
    {
        array_walk($websites,create_function('&$item,$key','
            if($item)
            {
                $item["desc"] = $item["sdesc"];
                $item["key"] = $item["skey"];
            }
        '));
        return $websites;
    }
    public function getWebsiteByRole($role)
    {/*{{{*/
        $tagFilter = array(WebsiteTagDef::ROLE=>$role);
        $markTag   = BindMark::setTag($tagFilter);
        $q = ZMarkQuery::ins(new WebsiteTagDef());
        $websites  = $q->listEnityByBinds('Website',null,null,null,$markTag,'rank','asc');
        $websites  = self::formatWebsites($websites);
        $websitesRes = array();
        $rolePre = $role."|";
        $tags = WebsiteTagDef::listTagsByRole($role);
        
        foreach($websites as $website)
        {
            $sitetags = explode(" ",$website["tag_".WebsiteTagDef::TAGS]);
            foreach($sitetags as $sitetag)
            {
                $sitetag = str_replace($rolePre,"",$sitetag);
                if(in_array($sitetag,$tags))
                    $websitesRes[$sitetag][] = $website;
            }
        }
        return $websitesRes;
    }/*}}}*/
    public function getTagsByRole($role)
    {/*{{{*/
        return WebsiteTagDef::listTagsByRole($role);
    }/*}}}*/
    public function getWebsiteByDomain($domain)
    {/*{{{*/
        /*toDo: 用户所关心得网址站集合 创造实体*/
        $domain = str_replace("http://","",$domain);
        $domain = str_replace("/","",$domain);
        if(!$domain)
            return array(
                   "key"=>"NULL" ,"rank"=>0,"domain"=>"NULL","name"=>"NULL","desc"=>""
                );
        $data = Dquery::ins()->get_website_by_domain($domain);
        if($data)
            $datas =  self::formatWebsites(array($data));
        else
        {
            /*$html = file_get_contents("http://".$domain);
            eregi("<title>(.*)</title>",$html,$title);
            $code = mb_detect_encoding($title[1],"ISO-2022-JP,GB2312,BIG5,CP936,UTF-8");
            $ti = mb_convert_encoding($title[1],"UTF-8",$code);*/
            /****/
            return array(
                   "key"=>str_replace(".","_",$domain) ,"rank"=>0,"domain"=>$domain,"name"=>$domain,"desc"=>""
               );
        }
        return $datas[0];
    }/*}}}*/
    public function searchWebsite($k)
    {/*{{{*/
        $searchKey = array(WebsiteTagDef::SEARCHKEY=>$k);
        $markTag   = BindMark::setTag($searchKey);
        $q = ZMarkQuery::ins(new WebsiteTagDef());
        $pageObj = new DataPage(12);
        $websites  = $q->listEnityByBinds('Website',null,$pageObj,null,$markTag,'rank','asc');
        $searchRes = array();
        foreach($websites as $website)
        {
            $searchRes[] = array($website['name'],$website['domain']);
        }
        return array($k,$searchRes);
    }/*}}}*/
}
