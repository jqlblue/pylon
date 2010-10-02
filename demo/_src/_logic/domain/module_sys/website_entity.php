<?php
class Website extends Entity
{/*{{{*/
    static public function createByBiz($name,$desc,$domain)
    {/*{{{*/
        $obj = new Website(EntityID::create('website'));
        $obj->name       = $name;
        $obj->sdesc      = $desc;
        $domain = str_replace("http://","",$domain);
        $domain = str_replace("/","",$domain);
        $obj->domain     = $domain;
        $obj->skey       = str_replace(".","_",$domain);
        $obj->rank       = $obj->id();
        $obj->lifestatus = LifeStatus::NORMAL;
        $website = Entity::createByBiz($obj);
        return $website;
    }/*}}}*/
    public function update($name,$desc,$domain)
    {/*{{{*/
        $this->name      = $name;
        $this->sdesc     = $desc;
        if($this->domain!=$domain)
        {
            $domain = str_replace("http://","",$domain);
            $domain = str_replace("/","",$domain);
            $this->domain = $domain;
            $this->skey   = str_replace(".","_",$domain);
        }
    }/*}}}*/
}/*}}}*/
