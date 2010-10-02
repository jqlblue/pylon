<?php

class Uhtml
{/*{{{*/
    public function __construct($html)
    {
        $this->html =$html;
    }
    public function show()
    {
        echo $this->html;
    }
}/*}}}*/

class Uelement extends PropertyObj 
{/*{{{*/
    public $renderer=null;
    public $subitems=array();
    public $type=null;
    public function __construct($type,$args,$renderer)
    {/*{{{*/
        DBC::requireNotNull($renderer);
        $this->type=$type;
        $this->renderer=$renderer;
        if(!isset($args['name']) && isset($args['id']))
        {
            $args['name'] = $args['id'];
        }
        parent::__construct($args);
    }/*}}}*/
    static public function attr2str($attr)
    {/*{{{*/
        return  JoinUtls::jassoArrayEx(" " ,$attr, create_function('$k,$v','return "$k=\"$v\"";'));
    }/*}}}*/
    public function regRenderer($renderer)
    {/*{{{*/
        DBC::requireNotNull($renderer,"regRenderer renderer is null");
        $this->renderer= $renderer;
        return $this;
    }/*}}}*/
    public function addSubs($items)
    {/*{{{*/
        $this->subitems=$items;
        return $this; 
    }/*}}}*/
    public function show()
    {/*{{{*/
        $this->renderer->show($this);
    }/*}}}*/
}/*}}}*/
/*********** base Renderer****/
class TagRenderer 
{/*{{{*/
    public function show($obj)
    {/*{{{*/
        $args = $obj->getPropArray();
        $attrstr= Uelement::attr2str($args);
        echo "<{$obj->type} $attrstr >\n";
        if(is_array($obj->subitems))
        {
            foreach($obj->subitems as $i)
            {
                $i->show();
            }
        }
        else if ($obj->subitems != null)
        {
            $obj->subitems->show();
        }
        echo "</{$obj->type}>\n";
    }/*}}}*/

}/*}}}*/
/***********/

class InputRenderer
{/*{{{*/
    public function show($obj)
    {/*{{{*/
        $attr = $obj->getPropArray();
        $attr['type']=$obj->type;
        if(!isset($attr['name']))
        {
            $attr['name'] = $attr['id'];
        }
        $attrstr= Uelement::attr2str($attr);
        echo "<input $attrstr >\n";
    }/*}}}*/
    public function validate($vars)
    {/*{{{*/
        if($this->have("rule"))
        {
            $name=$this->name;
            $validator = new IValidator($name,"",true,null);
            $validator->useRule($this->rule);
            $validator->validate($vars->$name);
        }
    }/*}}}*/
}/*}}}*/

class SelectRenderer  
{/*{{{*/
    
    public function show($obj)
    {
        $attr = $obj->getPropArray();
        $options = $attr['options'];
        $value = isset($attr['value']) ?  $attr['value'] : null;
        unset($attr['options']);
        unset($attr['value']);
        $attrstr= Uelement::attr2str($attr);
        echo "<select $attrstr> \n";
        if(!empty($options))
            foreach($options as $key=>$val)
            {
                if( $key == $value) 
                    echo "<option value=\"$key\" selected=\"true\">$val</option>\n";
                else
                    echo "<option value=\"$key\">$val</option>\n";
            }
        echo "</select>\n";
    }
}/*}}}*/

class RadioRenderer 
{/*{{{*/
    public function show($obj)
    {/*{{{*/
        $attr = $obj->getPropArray();
        $options = $attr['options'];
        unset($attr['options']);
        $value = isset($attr['value']) ?  $attr['value'] : null;
        if(!empty($options))
            foreach($options as $key=>$val)
            {
                $attr['id']=$attr['name'].$key;
                $attr['value']=$key;
                $attr['type']="radio";
                $attrstr= Uelement::attr2str($attr);
                if( $key == $value) 
                    echo " <input $attrstr  checked >";
                else
                    echo " <input $attrstr  >";
                echo " <label for=\"".$attr['name'].$key."\">$val </label> ";
            }
    }/*}}}*/
}/*}}}*/

class CheckboxRenderer 
{/*{{{*/
    public function show($obj)
    {/*{{{*/
        $attr = $obj->getPropArray();
        $prefix = $attr['id'];
        $options = $attr['options'];
        unset($attr['options']);
        $values = isset($attr['value'])&&$attr['value'] ?  $attr['value'] : array();
        unset($attr['value']);
        $index=0;
        if(!empty($options))
            foreach($options as $key=>$val)
            {
                $id = "{$prefix}_$index";
                $attr['id']= $id;
                $attr['name']="{$prefix}[]";
                $attr['value']=$key;
                $attr['type']="checkbox";
                $attrstr= Uelement::attr2str($attr);
                if(in_array($key,$values)) 
                    echo " <input $attrstr  checked >";
                else
                    echo " <input $attrstr  >";
                echo " <label for=\"$id\">$val </label> ";
                $index ++;
            }
    }/*}}}*/
}/*}}}*/

class TextareaRenderer
{/*{{{*/
    public function show($obj)
    {/*{{{*/
        $attr = $obj->getPropArray();
        if(!isset($attr['name']))
            $attr['name'] = $attr['id'];
        $value = '';
        if(isset($attr['value']))
        {
            $value = $attr['value'];
            unset($attr['value']);
        }
        $attrstr= Uelement::attr2str($attr);
        echo "<textarea $attrstr >$value</textarea>\n";
    }/*}}}*/
    public function validate($vars)
    {/*{{{*/
        if($this->have("rule"))
        {
            $name=$this->name;
            $validator = new IValidator($name,"",true,null);
            $validator->useRule($this->rule);
            $validator->validate($vars->$name);
        }
    }/*}}}*/
}/*}}}*/

class Udom
{/*{{{*/
    static public $renderers=array();
    public function __construct()
    {
        $this->renderers['select']   = new SelectRenderer();
        $this->renderers['radio']    = new RadioRenderer();
        $this->renderers['checkbox'] = new CheckboxRenderer();
        $this->renderers['textarea'] = new TextareaRenderer();
        $this->renderers['text']     = new InputRenderer();
        $this->renderers['submit']   = new InputRenderer();
        $this->renderers['button']   = new InputRenderer();
        $this->renderers['hidden']   = new InputRenderer();
        $this->renderers['password'] = new InputRenderer();
    }
    public function __call($name, $params)
    {/*{{{*/
        $funargs = split("_" ,$name);
        
        $first = array_shift($funargs);
        if(count($funargs)  >  count($params))
        {
            $value = JoinUtls::jarray(",",$params);
            DBC::unExpect("Uelement"," Uelement [$name], [$value] not match");
        }
        $attrVaules = array_slice($params,0,count($funargs));
        $dataArgs   = array_slice($params,count($funargs));
        if(!in_array('Prop',$funargs))
            if(count($funargs) >=1 )
                $attr  = array_combine($funargs,$attrVaules);
            else 
                $attr=array();
        else
            $attr = $attrVaules;
        $obj = new Uelement($first,$attr,new TagRenderer());
        $items=array();
        foreach($dataArgs as $i)
        {/*{{{*/
            if(is_array($i))
                $items = array_merge($items,$i);
            else if(is_string($i)||is_numeric($i))
            {
                $items[] = new Uhtml($i);
            }
            else if( $i instanceof Uelement)
            {
                $items[]=$i;
            }
        }/*}}}*/
        $obj->addSubs($items);
        if ( isset($this->renderers[$first]))
            $obj->regRenderer($this->renderers[$first]);
        return $obj;
        
    }/*}}}*/
}/*}}}*/

?>
