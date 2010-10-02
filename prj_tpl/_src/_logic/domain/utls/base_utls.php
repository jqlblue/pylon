<?php

class Visible
{/*{{{*/
    const  V_PUBLIC="public";
    const  V_PROTECTED="protected";
    const  V_PRIVATE="private";
    static public function options()
    {/*{{{*/
        $names[self::V_PUBLIC]  = "公开";
        $names[self::V_PRIVATE] = "内部";
        return $names;
    }/*}}}*/
    static public function nameOf($v)
    {/*{{{*/
        $names = self::options();
        return $names[$v];
    }/*}}}*/

    static public function colorOf($v)
    {/*{{{*/
        $opts[self::V_PRIVATE]='009f00';
        $opts[self::V_PROTECTED]='009ff0';
        $opts[self::V_PUBLIC]='ff0000';
        return $opts[$v];
    }/*}}}*/
    static public function otherOptions($key)
    {/*{{{*/
        $opts= self::options();
        unset($opts[$key]);
        return $opts;
    }/*}}}*/
    static public function resultVisibleDQL()
    {/*{{{*/
        $private = self::V_PRIVATE;
        return "? != '$private'";
    }/*}}}*/
}/*}}}*/

class ActiveStatus
{/*{{{*/
    const ACTIVE='active';
    const UNACTIVE='unactive';

    static public function actOfStatus($v)
    {
        $act[self::ACTIVE]     = array( "operkey"=>ActiveStatus::UNACTIVE,"status"=>"已激活","oper"=>"暂停");
        $act[self::UNACTIVE]   = array( "operkey"=>ActiveStatus::ACTIVE,"status"=>"已暂停","oper"=>"激活");
        return $act[$v];
    }
}/*}}}*/

class LifeStatus
{/*{{{*/
    const NORMAL     = 'normal';
    const OVERTIME   = 'overtime';
    const DISUSE     = 'disuse';
    static public function options()
    {/*{{{*/
        $opts[self::NORMAL]   ='恢复';
        $opts[self::OVERTIME] ='过期';
        $opts[self::DISUSE]   ='刪除';
        return $opts;
    }/*}}}*/

    static public function colorOf($v)
    {/*{{{*/
        $opts[self::NORMAL]='009f00';
        $opts[self::OVERTIME]='ff0000';
        $opts[self::DISUSE]='ff000';
        return $opts[$v];
    }/*}}}*/
    static public function statusNames()
    {/*{{{*/
        $opts[self::NORMAL]   ='有效';
        $opts[self::OVERTIME] ='已过期';
        $opts[self::DISUSE]   ='已刪除';
        return $opts;
    } /*}}}*/
    static public function nameOf($v)
    {/*{{{*/
        $opts = self::statusNames();
        return $opts[$v];
    }/*}}}*/
    static public function otherOpts($key)
    {/*{{{*/
        $opts =self::options();
        unset($opts[$key]);
        return $opts;
    }/*}}}*/
    static public function normalDQL()
    {/*{{{*/
        return  " ? = 'normal'"  ;
    }/*}}}*/
    static public function overtimeDQL()
    {/*{{{*/
        return  " ? = 'overtime'"  ;
    }/*}}}*/
    static public function compatibleDQL()
    {/*{{{*/
        $disuse = LifeStatus::DISUSE;
        return  " ? != '$disuse'"  ;
    }/*}}}*/
}/*}}}*/

class Scope
{/*{{{*/
    const  S_PUBLIC    = "public";
    const  S_PROTECTED = "protected";
    static public function options()
    {/*{{{*/
        $names[self::S_PUBLIC]  = "全局";
        $names[self::S_PROTECTED]  = "定制";
        return $names;
    }/*}}}*/
    static public function nameOf($v)
    {/*{{{*/
        $names = self::options();
        return $names[$v];
    }/*}}}*/

    static public function colorOf($v)
    {/*{{{*/
        $opts[self::S_PUBLIC]='#FF0000';
        $opts[self::S_PROTECTED]='#009ff0';
        return $opts[$v];
    }/*}}}*/
    static public function otherOptions($key)
    {/*{{{*/
        $opts= self::options();
        unset($opts[$key]);
        return $opts;
    }/*}}}*/
}/*}}}*/
