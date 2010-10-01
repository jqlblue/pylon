<?php
class Userconf extends Entity
{/*{{{*/
    static public function createByBiz($userid,$confkey,$confval)
    {/*{{{*/
        $obj = new Userconf(EntityID::create('userconf'));
        $obj->userid  = $userid;
        $obj->confkey = $confkey;
        $obj->confval = serialize($confval);
        return Entity::createByBiz($obj);
    }/*}}}*/
    public function update($confval)
    {/*{{{*/
        $this->confval = serialize($confval);
    }/*}}}*/
    public function getval()
    {
        return unserialize($this->confval);
    }
}/*}}}*/
