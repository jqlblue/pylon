<?php
abstract class SysConfSvc
{/*{{{*/
    protected $s        = null;
    protected $storekey = null;
    public function __construct()
    {/*{{{*/
        $this->s = new SysConfStore();
        $this->s->open();
        $this->setKey();
    }/*}}}*/

    protected function setKey()
    {/*{{{*/
    }/*}}}*/

    public function set($data)
    {/*{{{*/
        return $this->s->set($this->storekey,$data);
    }/*}}}*/
    public function get()
    {/*{{{*/
        return $this->s->get($this->storekey);
    }/*}}}*/
    public function close()
    {/*{{{*/
        return $this->s->close();
    }/*}}}*/
}/*}}}*/
class SysConfStore
{/*{{{*/
    public $dbPath;
    public $fileHand;
    public function __construct()
    {/*{{{*/
        $dbPath = Conf::SYS_CONF_DATA_PATH;
        $this->dbPath = $dbPath;
        $this->open();
        if($_REQUEST['debug'])
            var_dump($this->dump());
    }/*}}}*/
    public function __destruct()
    {/*{{{*/
        $this->close();
    }/*}}}*/
    public function open()
    {/*{{{*/
        $this->fileHand = dba_open($this->dbPath, "c", "db4");
    }/*}}}*/
    public function close()
    {/*{{{*/
        dba_close($this->fileHand);
    }/*}}}*/
    public function get($key)
    {/*{{{*/
        $this->dataDebug($key);
        $r =  unserialize(dba_fetch($key,$this->fileHand));
        return $r;
    }/*}}}*/
    public function set($key,$data)
    {/*{{{*/
        $r = dba_replace($key, serialize($data), $this->fileHand);
        return $r;
    }/*}}}*/
    public function dump()
    {/*{{{*/
        $result = array('dbname'=>basename($this->dbPath));
        $key = dba_firstkey($this->fileHand);
        while ($key != false) {
            $data = dba_fetch($key,$this->fileHand);
            if(unserialize($data))$data = unserialize($data);
            $result[$key] = $data;
            $key = dba_nextkey($this->fileHand);
        }
        return $result;
    }/*}}}*/
    public function dataDebug($key)
    {/*{{{*/
        if($_REQUEST['data_debug'])
        {
            $data =  unserialize(dba_fetch($key,$this->fileHand));
            echo "====== $key DATA IN $this->dbPath BDB BEGIN =======<br>\n";
            var_dump($data);
            echo "<br>====== $key DATA IN $this->dbPath BDB END =======<br>\n";
        }
    }/*}}}*/
}/*}}}*/

//广告属性系统配置
class AdvAttrConfSvc extends SysConfSvc
{/*{{{*/
    const STORE_KEY= 'ADV_ATTR_DIM';
    protected function setKey()
    {/*{{{*/
        $this->storekey = self::STORE_KEY;
    }/*}}}*/
}/*}}}*/
//内容广告系统配置
class ContentAdvConfSvc extends SysConfSvc
{/*{{{*/
    const STORE_KEY= 'CONTENT_ADV_DEFAULT';
    protected function setKey()
    {/*{{{*/
        $this->storekey = self::STORE_KEY;
    }/*}}}*/
}/*}}}*/
class ThemeAdvConfSvc extends SysConfSvc
{/*{{{*/
    const STORE_KEY= 'THEME_ADV_DEFAULT';
    protected function setKey()
    {/*{{{*/
        $this->storekey = self::STORE_KEY;
    }/*}}}*/
}/*}}}*/

/********** How To Use **************
 * $s = new AdvAttrConfSvc();
 * $s->get();
 * $s->set($data);
 ***********************************/
