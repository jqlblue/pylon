<?php
class Module extends Entity
{/*{{{*/
    static public function createByBiz($key,$name,$desc,$icon,$developer,$depend,$recommend,$version,$mtype)
    {/*{{{*/
        $obj = new Module(EntityID::create('module'));
        $obj->mkey       = $key;
        $obj->name       = $name;
        $obj->mdesc      = $desc;
        $obj->icon       = $icon;
        $obj->msize      = 0;
        $obj->version    = $version;
        $obj->developer  = $developer;
        $obj->depend     = $depend;
        $obj->recommend  = $recommend;
        $obj->mtype      = $mtype;
        $obj->lifestatus = LifeStatus::NORMAL;
        $module = Entity::createByBiz($obj);
        return $module;
    }/*}}}*/
    public function update($name,$desc,$icon,$developer,$depend,$recommend,$version)
    {/*{{{*/
        $this->name      = $name;
        $this->mdesc     = $desc;
        if($icon)
            $this->icon  = $icon;
        $this->developer = $developer;
        $this->depend    = $depend;
        $this->recommend = $recommend;
        $this->version   = $version;
        $moduleMore  = DDA::ins()->get_modulemore_by_mkey_version($this->mkey,$version);
        if($moduleMore)
            DaoFinder::query("ModulemoreQuery")->update($this->mkey,$version,$name,$desc,$icon,$developer,$depend,$recommend);
        else
            DaoFinder::query("ModulemoreQuery")->add($this->id(),$this->mkey,$name,$desc,$icon,$developer,$depend,$recommend,$version,$this->lifestatus);
    }/*}}}*/
    public function setlifestatus($status)
    {
        $this->lifestatus = $status;
        DaoFinder::query("ModulemoreQuery")->updatelifestatus($this->mkey,$status);
    }
    static public function listmtype()
    {
        return array(
            'cube'=>1,
            'mainexe'=>2
            );
    }
}/*}}}*/

class Modulemore extends Entity
{
    public function update($name,$desc,$icon,$developer,$depend,$recommend,$version)
    {/*{{{*/
        $this->name      = $name;
        $this->mdesc     = $desc;
        if($icon)
            $this->icon  = $icon;
        $this->developer = $developer;
        $this->depend    = $depend;
        $this->recommend = $recommend;
        $this->version   = $version;
    }/*}}}*/
    public function del()
    {/*{{{*/
        //$this->lifestatus = LifeStatus::DISUSE;
        DaoFinder::query("ModulemoreQuery")->del($this->mkey,$this->version);
    }/*}}}*/
}
?>
