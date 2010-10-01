<?php
class UColumn extends PropertyObj
{/*{{{*/
    public function __construct($key,$name,$fun=null,$class=null)
    {/*{{{*/
        $this->key   = $key;
        $this->name  = $name;
        $this->fun   = $fun;
        $this->class = $class;
    }/*}}}*/
}/*}}}*/
class UTable  extends PropertyObj
{/*{{{*/

    public function __construct($name,$desc=" Table no Desc")
    {/*{{{*/
        $this->name = $name;
        $this->desc = $desc;
        $this->columns = array();
        $this->datas = array();
        $this->fmtRowFun=null;
    }/*}}}*/

    function addColumn($key,$name,$fun=null,$class=null)
    {/*{{{*/
        $columns = $this->columns;
        $columns[$key] = new UColumn($key,$name,$fun,$class); 
        $this->columns = $columns;
    }/*}}}*/

    function appendDataRow($row)
    {/*{{{*/
        $datas = $this->datas;
        $datas[] = $row;
        $this->datas  = $datas;
    }/*}}}*/

    function bindDatas($dataArray)
    {/*{{{*/
        $this->datas = $dataArray;
    }/*}}}*/

    function show($convertFun=null,$css="tablesorter")
    {/*{{{*/

        $colcnt  = count($this->columns);
        $columns = JoinUtls::jarrayEx(' ',$this->columns,
            create_function('$x','return "<th class=\"$x->class\" >".$x->name."</th>";'));
        $datas = ''; 
        if(is_array($this->datas))
            foreach ($this->datas as $row)
            {
                if($convertFun != null)
                {
                    $row = call_user_func($convertFun,$row);
                }
                else
                {
                    if($this->fmtRowFun !=null)
                    {
                        $row = call_user_func($this->fmtRowFun,$row);
                    }
                }
                $showRow = array_intersect_key($row,$this->columns);
                $i = 0;
                $tr = "\n <tr>";
                foreach ($this->columns as $field)
                {
                    $tr .= "\n <td class=\"{$field->class}\">".$showRow[$field->key]."</td>";
                    $i++;
                }
                $tr .= "\n </tr>";
                $datas .= $tr;
            }
        $desc = $this->desc;
        $name = $this->name;
        $html = " <table  id=\"$name\" name=\"$name\" border=\"0\" cellpadding=\"0\"  cellspacing=\"0\" class=\"$css\"> 
            <thead> <tr > $columns </tr> </thead> 
            <tbody> $datas </tbody> </table> ";
        echo $html;
    }/*}}}*/
}/*}}}*/
?>
