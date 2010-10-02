<?php
class ModuleTagDef  extends ZMarkDef implements IMarkDef
{/*{{{*/
    const LABEL = "label";
    public function settingCnt()
    {
        return array();
    }

    public function settingTag()
    {
        return array(self::LABEL);
    }
}/*}}}*/
class WebsiteTagDef  extends ZMarkDef implements IMarkDef
{/*{{{*/
    const SEARCHKEY = "searchkey";
    const TAGS      = "tags";
    const ROLE      = "role";
    static $roletags = array(
        "大全" => array("小说","游戏","新闻","视频","社区","交友","购物","招聘","生活","音乐","军事","体育","软件","汽车","博客","酷站","手机","硬件","银行"),
        "学生" => array("游戏","学习"),
        "长辈" => array("健身","视频","游戏")
        );
    public function listRole()
    {
        return array_keys(self::$roletags);
    }
    public function listTagsByRole($role)
    {
        return self::$roletags[$role];
    }
    public function listRoleTags()
    {
        return self::$roletags;
    }
    public function settingCnt()
    {
        return array();
    }

    public function settingTag()
    {
        return array(self::SEARCHKEY,self::TAGS,self::ROLE);
    }
}/*}}}*/
?>
