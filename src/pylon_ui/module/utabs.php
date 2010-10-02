<?php
class SmartyLoader
{/*{{{*/
    static $tplPath;
    public function __construct($tpl)
    {
        $this->tpl=$tpl;
    }
    public function load($key)
    {
        $path= self::$tplPath;
        XView::$viewer->smarty->display("$path/{$this->tpl}");
    }
}/*}}}*/
class UcomLoader
{/*{{{*/
    public function __construct($obj)
    {
        $this->obj=$obj;
    }
    public function load($key)
    {
        echo "<div id=\"$key\"  class=\"undis\">";
        $this->obj->show();
        echo "</div>";
    }
}/*}}}*/

class UrlLoader
{/*{{{*/
    public function __construct($url,$arsyntag)
    {
        $this->url = $url;
        $this->arsyntag= $arsyntag;
    }
    public function load($key)
    {

        $u= new Udom();
        $url = $this->url;
        $arsyntag = $this->arsyntag;
        $content = "{$key}_panel_content";
        $code = '
        function load_'.$key.'_panel()
        {
            data = {};
            data.url = "'.$url.'";
            data.out= "#'.$content.'";
            data.prefix = "'.$arsyntag.'";
            PUI.arsynLoad(data);
        }
        ' ;
        $dom = $u->div_id_class($key,"undis",
            $u->div_id($content),
            $u->script_language("javascript",$code));
        $dom->show();
    }
}/*}}}*/
class Upanel
{/*{{{*/
    static public function tpl($tpl)
    {
        return new SmartyLoader($tpl);
    }
    static public function uiobj($obj)
    {
        return new UcomLoader($obj);
    }
    static public function url($url,$arsyntag="")
    {
        return new UrlLoader($url,$arsyntag);
    }
    static public function action($action,$arsyntag="")
    {
        $url = "index.html?do=$action";
        return new UrlLoader($url,$arsyntag);
    }
}/*}}}*/
class Utabs
{/*{{{*/
    public function __construct($mark="",$disindex=0)
    {
        $this->mark     = $mark;
        $this->disindex = $disindex;
    }
    public function add_panel($panelkey,$dispname,$loader=null)
    {/*{{{*/
        $this->tabs[$panelkey]=$dispname;
        if($loader != null)
            $this->loaders[$panelkey]=$loader;
    }/*}}}*/
    public function show($divclass="sgt-utabs",$ulclass="tabs")
    {/*{{{*/
        $u=ApolloUI::udom();
        $panels=array();
        if(!empty($this->tabs))
        {
            foreach($this->tabs as $key=>$val)
            {
                $val = "<a><span>{$val}</span></a>";
                $newpanel=$u->li_rel($key,$val);
                $panels[]=$newpanel; 
            }
        }
        echo "<div class=\"utabs\" mark=\"{$this->mark}\">";
        $dom =  $u->div_class($divclass, $u->ul_class($ulclass,$panels));

        $dom->show();
        if(!empty($this->loaders))
        {
            foreach($this->loaders as  $key=>$loader)
            {
                $loader->load($key);

            }
        }
        $this->show_js($divclass,$ulclass);
        echo "</div>";
    }/*}}}*/
    public function show_js($divclass,$ulclass)
    {/*{{{*/

        echo '
<script type="text/javascript">
    function tabme()
    {
        $(this).siblings().removeClass("active");
        divid=$(this).attr("rel");
        $(this).addClass("active");
        $(this).parents(".utabs").children(".dis").addClass("undis").removeClass("dis");
        $("#"+divid).removeClass("undis").addClass("dis");
        var fun_name = "load_" + divid  + "_panel" ;
        try{
            eval( fun_name + "()" );
        }
        catch(exception)
        {
        }
    }

    $(".utabs .'.$divclass.' .'.$ulclass.' > li").click(tabme);
    $(".utabs .'.$divclass.' .'.$ulclass.'").each(
        function(){
            $(this).children("li:eq('.$this->disindex.')").trigger("click");
        }    
        );
    </script>
        ';
    }/*}}}*/
}/*}}}*/
?>
