<?php
class UModuleJS
{/*{{{*/
    static public function formjs($id)
    {/*{{{*/

      $code=  
            "$.elementReady(\"{$id}\",
                function()
                {
                    UformSel = this;
                    $(UformSel).find(\"input:text,input:password,input:hidden,textarea,select\").blur(PUI.Uform.inputChecker);
                    
                    eventData = {}; 
                    eventData.ajaxmode = ($(UformSel).attr(\"ajaxmode\")==\"false\")?false:true;
                    if(typeof(callbefore_{$id})==\"function\" && $.isFunction(callbefore_{$id}))
                        eventData.callbefore = callbefore_{$id};
                    if(typeof(callback_{$id})==\"function\" && $.isFunction(callback_{$id}))
                        eventData.callback = callback_{$id};
                        
                    $(UformSel).bind(\"submit\",eventData,PUI.Uform.onSubmit);
                }
            );
            ";
        return $code;
    }/*}}}*/
}/*}}}*/
class UModuleDom extends Udom
{/*{{{*/
    public function ext_std_submit($form,$dispname)
    {/*{{{*/
        $items[] = $this->hidden_id_value("submit_$form","true");
        $items[] = $this->submit_id_value_class("sub_but","$dispname","btn");
        return $items;
    }/*}}}*/
    public function uform($id,$action,$items,$ajaxmode="false")
    {/*{{{*/
        $ajaxmode = ($ajaxmode || $ajaxmode === "true")?'true':'false';
        return  $this->div_class("uform",
            $this->form_id_action_method_ajaxmode($id,$action,"post",$ajaxmode,$items),
            $this->script_language("javascript",UModuleJS::formjs($id))
        );
    }/*}}}*/
}/*}}}*/
class Oform  extends UModuleDom
{/*{{{*/
    public $id;
    public $action;
    public $ajaxmode;
    public $needshow=false;
    public function __construct($id,$action,$ajaxmode="false")
    {/*{{{*/
        $this->id = $id;
        $this->action = $action;
        $ajaxmode = ($ajaxmode === "true")?'true':'false';
        $this->ajaxmode = $ajaxmode;
        parent::__construct();
    }/*}}}*/
    public function begin()
    {/*{{{*/
        ob_start();
    }/*}}}*/
    public function show($udom)
    {
        if(is_array($udom))
        {
            foreach($udom as $o)
            {
                $o->show();
            }
        }
        else
            $udom->show();
    }
    public function end()
    {/*{{{*/
        $content= ob_get_contents();
        ob_end_clean();
        $this->needshow=false;
        $form = $this->div_class("uform",
            $this->form_id_action_method_ajaxmode($this->id,$this->action,"post",
                        $this->ajaxmode,$content),
            $this->script_language("javascript",UModuleJS::formjs($this->id))
        );
        $form->show();
    }/*}}}*/
}/*}}}*/
?>
