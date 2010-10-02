<?php
class  CssLoad
{

    const LONG_KEEP  = 2592000;  //one month; 
    const SHORT_KEEP = 600;
    const NO_KEEP = 0;

    public function zipEnable()
    {/*{{{*/
        if ( ob_get_length() == 0 and !ini_get('zlib.output_compression')
            and ini_get('output_handler') != 'ob_gzhandler'
                and ini_get('output_handler') != 'mb_output_handler' )
        {
            ob_start('ob_gzhandler');
        }
        else
        {
            ob_start();
        }
    }/*}}}*/
    public function cacheEnable($file,$keepTime)
    {/*{{{*/
        $fileMTime = filemtime($file);
        $fileSize  = filesize($file);

        $etag="\"".dechex(fileinode($file)).":6r0x:".dechex($fileMTime).":".dechex($fileSize)."\"";
        $offset = $keepTime;
        $ExpStr = "Expires: ".gmdate("D, d M Y H:i:s",time() + $offset)." GMT";
        $EtagStr = "Etag: $etag";
        $LmStr = "Last-Modified: ".gmdate("D, d M Y H:i:s",filemtime(__FILE__))." GMT";

        header("Cache-Control: public");
        header("Pragma: cache");
        header($ExpStr);
        header($EtagStr);
        header($LmStr);
    }/*}}}*/
    public function zipEnd()
    {/*{{{*/
        ob_end_flush();
    }/*}}}*/
    public function load($files,$combinfile,$release)
    {/*{{{*/
        header('Content-Type: text/css; charset: UTF-8');
        self::zipEnable();
        if ($release)
        {
            self::cacheEnable($combinfile,self::LONG_KEEP);
        }
        else
        {
            self::cacheEnable($combinfile,self::NO_KEEP);
        }
        self::includeCss($files);
        self::zipEnd();
        
    }/*}}}*/
    public function includeCss($files)
    {/*{{{*/
        foreach($files as $file)
        {
            echo "/* ============= $file ============== */\n";
            include($file); 
            echo "\n";
        }
        echo "/* ============= End ============== */";
    }/*}}}*/
}
?>
