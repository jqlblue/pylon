<?php
class Theme
{/*{{{*/
    private $app_tpl_path = null;
    private $base_tpl_path = null;

    public function __construct($appTplPath,$baseTplPath)
    {/*{{{*/
        $this->app_tpl_path  = $appTplPath;
        $this->base_tpl_path = $baseTplPath;
        $smartyView = XTools::renderer();
        $smartyView->smarty->theme = $this;
    }/*}}}*/
    public function tpl($file)
    {/*{{{*/
        if(file_exists("$this->app_tpl_path/$file"))
            return "$this->app_tpl_path/$file";
        return "$this->base_tpl_path/$file";
    }/*}}}*/
    public function css($file)
    {/*{{{*/
        return "<link rel=\"stylesheet\" rev=\"stylesheet\" href=\"/styles/$file\" type=\"text/css\">";
    }/*}}}*/
    public function script($file)
    {/*{{{*/
        $fmd5 = substr(md5($file),0,7);
/*        $s = "<script>
if(typeof __loaded_{$fmd5}==\"undefined\")
{
    (function(){
        var src = \"/js/$file?v=$t\" ;
        var js = document.createElement('script');
        js.setAttribute('type','text/javascript');
        js.setAttribute('src',src);
        document.getElementsByTagName('head')[0].appendChild(js);
        __loaded_{$fmd5} = true;
    })();
}
</script>"; //此法在流程中使用JQUERY则会出错 在CHROME IE下均是
 */
        $s= "<script src=\"/scripts/$file\"></script>";
        return $s;
    }/*}}}*/
    public function images($file)
    {}
}/*}}}*/
?>
