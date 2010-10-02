<?php
class UPanelBox
{/*{{{*/
    public $value=null;
    public $cleantag=null;
    public $prop=array();
    public function __construct($items,$value,$cleantag,$propArr)
    {/*{{{*/
        $this->items=$items;
        $this->value=$value;
        $this->cleantag= $cleantag;
        $this->prop = $propArr;
    }/*}}}*/
    public function show()
    {/*{{{*/
        if(is_array($this->items))
        {
        foreach($this->items as $i)
        {
            $i->show();
        }
        }
        else
        {
            $this->items->show();
        }
    }/*}}}*/
}/*}}}*/

class FRadioRenderer
{/*{{{*/
    public function show($uiobj)
    {/*{{{*/
        $attr = $uiobj->getPropArray();
        $options = $attr['options'];
        unset($attr['options']);
        $value = isset($attr['value']) ?  $attr['value'] : null;
        foreach($options as $key=>$val)
        {
            $attr['id']=$key;
            $attr['value']=$key;
            $attr['type']="radio";
            $attrstr= Uelement::attr2str($attr);
            echo "<li>";
            if( strval($key) == strval($value)) 
                echo " <input $attrstr  checked >";
            else
                echo " <input $attrstr  >";
            echo " <label for=\"$key\">$val </label> ";

            echo "</li>";
        }
    }/*}}}*/
}/*}}}*/

class TextTpl
{/*{{{*/
    public function __construct($id,$vars,$rule)
    {
        $this->id = $id;
        $this->vars =$vars;
        $this->rule = $rule;
    }
    public function filterProp($id,$value)
    {/*{{{*/
        $prop[$this->id]=$value;
        return $prop;
    }/*}}}*/
    public function build()
    {/*{{{*/
        $u = new UModuleDom();
        $value=Ufilter::valueof($this->vars,$this->id);
        $boxs=$u->ul($u->li($this->desc),
                     $u->li($u->text_id_value_orule_class($this->id,$value,$this->rule,"input_text")),
                     $u->li($u->ext_std_submit("filter","查询" )));

        $prop= $this->filterProp($this->id,$value);
        return new UPanelBox($boxs,$value,"{$this->id}=",$prop);
    }/*}}}*/
}/*}}}*/
class StringTpl  extends TextTpl
{/*{{{*/
    public function __construct($id,$vars,$rule,$needFuzzy = true)
    {/*{{{*/
        parent::__construct($id,$vars,$rule);
        $this->needFuzzy=$needFuzzy;
    }/*}}}*/
    public function filterProp($id,$value)
    {/*{{{*/
        if($this->needFuzzy && !empty($value))
        {
            $prop[$this->id]="? like '%$value%'";
        }
        else
            $prop[$this->id]=$value;
        return $prop;
    }/*}}}*/
}/*}}}*/
class RadioTpl
{/*{{{*/
    public function __construct($id,$vars,$options)
    {
        $this->id = $id;
        $this->vars =$vars;
        $this->options= $options;
    }
    public function build()
    {
        $u = new UModuleDom();
        $value=Ufilter::valueof($this->vars,$this->id);
        $boxs=$u->ul(
            $u->radio_id_options_value($this->id,$this->options,$value)->regRenderer(new FRadioRenderer()),
            $u->li($u->ext_std_submit("filter","查询" )));
        $prop[$this->id]=$value;
        return new UPanelBox($boxs,$this->options[$value],"{$this->id}=",$prop);
    }
}/*}}}*/

class NumScopeTpl
{/*{{{*/
    public function __construct($vars,$prefix,$rule=null)
    {/*{{{*/
        $this->vars = $vars;
        $this->prefix = $prefix;
        $this->rule = is_null($rule)?"#digit":$rule;
    }/*}}}*/
    public function build()
    {/*{{{*/
        $u= new UModuleDom();
        $min = Ufilter::valueof($this->vars,$this->prefix."min",null);
        $max = Ufilter::valueof($this->vars,$this->prefix."max",null);
        $mergeValue = self::valueof($min,$max);

        $ul= $u->ul(
            $u->li("[值]>=",$u->text_id_value_orule_class($this->prefix."min",$min,$this->rule,"input_digit")),
            $u->li("[值]<=",$u->text_id_value_orule_class($this->prefix."max",$max,$this->rule,"input_digit")),
            $u->li($u->ext_std_submit("filter","查询" ))
        );
        $prop[$this->prefix."min"]=$min;
        $prop[$this->prefix."max"]=$max;
        return new UPanelBox($ul,$mergeValue,"{$this->prefix}min=&{$this->prefix}max=",$prop);
    }/*}}}*/
    static public function valueof($min,$max)
    {/*{{{*/
        if($min!=""&&$max!="") {$desc="[值]>={$min} and [值]<={$max}";}
        elseif($min!="")   {$desc="[值]>={$min}";}
        elseif($max!="")   {$desc="[值]<={$max}";}
        else {$desc="";}
        return $desc;
    }/*}}}*/

}/*}}}*/
class TimeScopeTpl
{/*{{{*/
    public function __construct($vars)
    {
        $this->vars= $vars;
    }
    public function build()
    {
        $u= new UModuleDom();
        $beg = Ufilter::valueof($this->vars,"begintime");
        $end = Ufilter::valueof($this->vars,"endtime"   );
        $sumValue = Ufilter::sumvalueof($this->vars,"begintime","endtime");
        $ul= $u->ul(
            $u->li("开始:",$u->text_id_value_rule_class("begintime",$beg,"#date","datePick")),
            $u->li("结束:",$u->text_id_value_rule_class("endtime",$end,"#date","datePick")),
            $u->li($u->ext_std_submit("filter","查询" ))
        );
        $prop["begintime"]=($beg)?$beg." 00:00:00":$beg;
        $prop["endtime"]=($end)?$end. "23:59:59":$end;
        return new UPanelBox($ul,$sumValue,"begintime=&endtime=",$prop);
    }
}/*}}}*/
class PanelBoxTpl
{/*{{{*/
    static public function numscope($vars,$prefix=null,$rule=null)
    {/*{{{*/
        return new NumScopeTpl($vars,$prefix,$rule);
    }/*}}}*/
    static public function timescope($vars)
    {/*{{{*/
        return new TimeScopeTpl($vars);
    }/*}}}*/
    static public function datetime($id,$vars)
    {/*{{{*/
        return new DateTpl($id,$vars,null);
    }/*}}}*/
    static public function radio($id,$vars,$options)
    {/*{{{*/
        return new RadioTpl($id,$vars,$options);
    }/*}}}*/
    static public function text($id,$vars,$rule=null)
    {/*{{{*/
        return new TextTpl($id,$vars,$rule);
    }/*}}}*/
    static public function string($id,$vars,$rule=null,$needFuzzy=true)
    {/*{{{*/
        return new StringTpl($id,$vars,$rule,$needFuzzy);
    }/*}}}*/
}/*}}}*/

class DateTpl extends TextTpl
{/*{{{*/
    public function build()
    {/*{{{*/
        $u = new UModuleDom();
        $value=Ufilter::valueof($this->vars,$this->id);
        $boxs=$u->ul($u->li($this->desc),
                     $u->li($u->text_id_value_orule_class($this->id,$value,"#date","datePick")),
                     $u->li($u->ext_std_submit("filter","查询" )));

        $prop= $this->filterProp($this->id,$value);
        return new UPanelBox($boxs,$value,"{$this->id}=",$prop);
    }/*}}}*/
}/*}}}*/
class Ufilter
{/*{{{*/
    public function __construct($action)
    {/*{{{*/
        $this->group_titles= array();
        $this->group_boxs = array();
        $this->action = $action;
        $this->prototype = PropertyObj::create();
        $this->prototypeSetup();
        $this->filterArr = array();
    }/*}}}*/

    static public function valueof($vars,$name,$defaultval=null)
    {/*{{{*/
        if($vars->have($name))
            return $vars->$name;
        return $defaultval;
    }/*}}}*/
    static public function sumvalueof($vars,$first,$second)
    {/*{{{*/
        if($vars->have($first) && $vars->have($second) )
        {
            $begv = $vars->$first;
            $endv = $vars->$second;
            if(!empty($begv) && !empty($endv))
                return "$begv : $endv"; 
        }
        return null;
    }/*}}}*/

    public function prototypeSetup()
    {/*{{{*/
        $u = new UModuleDom();
        $this->prototype->form = $u->form_id_action_method("xxx",$this->action,"post",null);
        $this->prototype->form->emptydesc="";
        $this->prototype->title = null;
    }/*}}}*/
    private function title_set($desc,$value,$cleantag,$must)
    {/*{{{*/
        $u = new UModuleDom();
        $index = count($this->group_titles);
        if(empty($value))
        {
            $li=$u->li_relindex_class($index,"select filter",
                $u->em($desc),
                $this->prototype->title,
                $u->a_href_class("#","select","请选择")
            );
        }
        else
        {
            if($must)
            {
                $li=$u->li_relindex_class($index,"filter",
                    $u->em($desc . ":" ),
                    $value,
                    $u->a_href_class("#","select","另选")
                );
            }
            else
            {
                $li=$u->li_relindex_class($index,"filter",
                    $u->em($desc . ":" ),
                    $value,
                    $u->a_href_class("#","select","另选"),
                    $u->a_href($this->action ."&$cleantag","清除")
                );
            }
        }
        return $li;
    }/*}}}*/

    private function box_set($id,$desc,$boxitem)
    {/*{{{*/
        $u = new UModuleDom();
        $form = clone $this->prototype->form ;
        $form->id = "{$id}_form" ;
        $form->name= $form->id;
        $form->subitems = $boxitem;

        $div = $u->div_class("uform",
               $form,
               $u->script_language("javascript",UModuleJS::formjs($form->id)));
        $index = count($this->group_boxs);
        return $u->li_relindex_class_style($index,"filterBox" ,"display: none;", $div);
    }/*}}}*/

    public function group($id,$desc,$tpl,$must=false)
    {/*{{{*/
        $tpl->desc=$desc;
        $tpl->gid = $id;
        $box=$tpl->build();
        $this->filterArr = array_merge($this->filterArr,$box->prop);
        $this->group_titles[] = $this->title_set($desc,$box->value,$box->cleantag,$must);
        $this->group_boxs[] =  $this->box_set($id,$desc,$box);
    }/*}}}*/
    public function show($container=null)
    {/*{{{*/
        $u = new Udom();
        $items=array();
        $items = array_merge($this->group_titles,$this->group_boxs);
        $div = $u->div_id_class($container,"filter-space",
            $u->ul( $items));

        $div->show();
        $jsCont =is_null($container)? 
            "$(PUI.Ufilter.setup);":
            "$.elementReady('{$container}',PUI.Ufilter.setup)";
        echo self::wrapScript($jsCont);

    }/*}}}*/
    
    public function wrapScript($content)
    {/*{{{*/
        return "<script language=\"javascript\">{$content}</script>" ;
    }/*}}}*/
    public function getFilterProp()
    {/*{{{*/
        $prop = FilterProp::create($this->filterArr);
        $fun = create_function('$v','return  !is_null($v)&&$v!=="";');
        $prop->filter(null,$fun);
        return $prop;
    }/*}}}*/

}/*}}}*/


?>
