<?php
class EmailIsp
{
    static $_isp = null;
    static public function register($isp,$domain)
    {/*{{{*/
        self::$_isp[$isp] = $domain;
    }/*}}}*/
    static public function regIsp()
    {/*{{{*/
        self::register("163","@163.com");
        self::register("126","@126.com");
        self::register("yahoocn","@yahoo.cn");
        self::register("sina","@sina.com");
        self::register("sohu","@sohu.com");
    }/*}}}*/
    static public function getAll()
    {/*{{{*/
        self::regIsp();
        return self::$_isp;
    }/*}}}*/
    static public function getIsp($isp)
    {/*{{{*/
        self::regIsp();
        return self::$_isp[$isp];
    }/*}}}*/
}
