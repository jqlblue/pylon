<?php
class SmartyRenderer implements XRenderer
{/*{{{*/
    public $smarty=null;
    public function __construct($smartyRoot,$templarRoot)
    {/*{{{*/
        require('smarty/Smarty.class.php');
        $smarty = new Smarty();
        $smarty->template_dir = $templarRoot;
        $smarty->compile_dir  = "$smartyRoot/templates_c";
        $smarty->cache_dir    = "$smartyRoot/cache";
        $smarty->config_dir   = "$smartyRoot/configs";
        $smarty->config_dir   = "$smartyRoot/configs";
        $this->smarty = $smarty;

    }/*}}}*/
    public function _draw($xcontext)
    {/*{{{*/
        $_datas= $xcontext->attr;
        foreach($_datas as $key=>$value){
            $$key = $value;    
            $this->smarty->assign($key,$value);
        }
        if($xcontext->have("debug") && $xcontext->debug==2)
            echo "<br>Smarty TPL:{$xcontext->_view}<br>";
        $this->smarty->display($xcontext->_view);
        exit;
    }/*}}}*/

}/*}}}*/


?>
