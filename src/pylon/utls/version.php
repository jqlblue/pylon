<?php
class SysVersion
{/*{{{*/
    public $structNo=0;
    public $featureNo=0;
    public $fixbugNo=0;
    public $commitNo=0;
    static public $versionFile="";
    static public $instance=null;
    static public function init($file)
    {
        self::$versionFile = $file;
    }
    static public function  ins()
    {/*{{{*/
        if(self::$instance != null)
            return self::$instance;
        $version=@file_get_contents(self::$versionFile);
        list($structNo,$featureNo,$fixbugNo,$commitNo) = explode(".",$version);
        return new SysVersion($structNo,$featureNo,$fixbugNo,$commitNo);
    }/*}}}*/
    public function __construct($struct,$feature,$fixbug,$commitNo)
    {/*{{{*/
        $struct  = empty($struct) ?  0:$struct;
        $feature = empty($feature) ? 0:$feature;
        $fixbug  = empty($fixbug) ? 0:$fixbug;
        $commitNo= empty($commitNo) ? 0:$commitNo;
        
        $this->structNo  = $struct;
        $this->featureNo = $feature;
        $this->fixbugNo  = $fixbug;
        $this->commitNo  = $commitNo;
    }/*}}}*/
    public function save()
    {/*{{{*/
        $version = "{$this->structNo}.{$this->featureNo}.{$this->fixbugNo}.{$this->commitNo}";
        file_put_contents(self::$versionFile,$version);

    }/*}}}*/
    public function commit()
    {
        $this->commitNo +=1;
    }
    public function fixbug()
    {/*{{{*/
        $this->fixbugNo  += 1;
    }/*}}}*/
    public function featureUpgrade()
    {/*{{{*/
        $this->featureNo +=1 ;
        $this->fixbugNo =0 ;
    }/*}}}*/
    public function structUpgrade()
    {/*{{{*/
        $this->featureNo = 0;
        $this->fixbugNo  = 0;
        $this->structNo  += 1;
    }/*}}}*/
    public function verinfo()
    {
        $version = "{$this->structNo}.{$this->featureNo}.{$this->fixbugNo}.{$this->commitNo}";
        return $version;
    }
}/*}}}*/
?>
