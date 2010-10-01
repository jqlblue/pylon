<?php
class HtmlConvert
{/*{{{*/
    static public function conv2infmt($content,$storeimg=false,$rdnote="",$rdtags="")
    {/*{{{*/
        /*
         * <a href='link1'>common link</a>
         * <a href="link2?fefwef&fewf=fewfewfw'cscascas"mkmkmk'>complex link</a> //not support
         * <a href='asdsdsadadas' title="fewfew" href=fewfewfwfew>duplicated href link</a> 
         * <a href=asdsdsadadas >untidy href with no quotes</a>  //not support
         * <a href='javascript:alert(1)' >link With Js</a>
         *
         * process content href, replaces a href with rded href
         */
        BR::isTrue(strlen(trim($content))<10000, "全代码长度超过10K");
        if (get_magic_quotes_gpc())
            $content = stripslashes($content);
        $GLOBALS['hrefCnt'] = 0;
        $content = RDConvert::toInnerFmt($content,$rdnote,$rdtags);
        if($storeimg)
            $content =  preg_replace_callback('/(<img.+?src=)(.+?)([>|\s])/',array("HtmlConvert","storeImags"),$content);
        return $content;
    }/*}}}*/
    static public function conv2showfmt($htmldata,$args=null)
    {/*{{{*/
        $htmldata = RDConvert::toStdFmt($htmldata,$args);
        $htmldata = preg_replace_callback('/\<style(.*?)\>(.|\n)+?\<\/style\>/', array('HtmlConvert','processStyle'), $htmldata);
        return $htmldata;
    }/*}}}*/
    static public function conv2stdfmt($htmldata,$args=null)
    {/*{{{*/
        $htmldata = RDConvert::toStdFmt($htmldata,$args);
        return $htmldata;
    }/*}}}*/
    static public function storeImags($str)
    {/*{{{*/
        $pre = $str[1];
        $suf = $str[3];
        $imgurl = str_replace("'",'',str_replace('"', '', $str[2]));
        if(strpos($imgurl,Conf::SHOW_FILE_DOMAIN) != false )
           return  $pre.'"'.$imgurl.'"'.$suf;
        UploadSvc::init(Conf::SHOW_FILE_PATH,Conf::SHOW_FILE_DOMAIN,$maxUploadSize);
        $u = UploadSvc::getUpload("image");
        $res=$u->uploadRemoteImg($imgurl);
        BR::isTrue($res['ST'] == 'OK',"store images [$imgurl] fail!");
        return  "$pre\"{$res['URL']}\"$suf";
    }/*}}}*/
    static public function processStyle($matches)
    {/*{{{*/
        global $advertid;
        $result = preg_replace('/^(?:\s*)((?:.*?){(?:.*)})/m', "#rf_$advertid \\1",$matches[0]);
        return $result;
    }/*}}}*/

}/*}}}*/

