<?php
class UColumn2 extends PropertyObj
{/*{{{*/
    public function __construct($key,$name,$orderitem=array())
    {/*{{{*/
        $this->key   = $key;
        $this->name  = $name;
        $this->orderitem = $orderitem;
        $this->fmtCode = null;
        $this->callback = null;
        $this->params = null;
    }/*}}}*/
    public function fmtCode($code=null)
    {/*{{{*/
        $this->fmtCode = $code;
    }/*}}}*/
    public function fmtVal($val,$row)
    {/*{{{*/
        $fmtCode = $this->haveSet('fmtCode')?$this->fmtCode:null;
        if(!empty($fmtCode))
        {
            $fmtFunc = create_function('$val,$row',$fmtCode);
            return $fmtFunc($val,$row);
        }
        return $val;
    }/*}}}*/
    public function setCallBack($callBack,$params=null)
    {/*{{{*/
        $this->callback = $callBack;
        $this->params = $params;
    }/*}}}*/
    public function execCallBack($rowData)
    {/*{{{*/
        if(is_null($this->callback))
            return $rowData[$this->key];
        $params = array($rowData,$this->params);
        return call_user_func_array($this->callback,$params);
    }/*}}}*/
}/*}}}*/
class UTable2  extends PropertyObj
{/*{{{*/
    public function __construct($name,$desc=" Table no Desc")
    {/*{{{*/
        $this->name      = $name;
        $this->desc      = $desc;
        $this->columns   = array();
        $this->datas     = array();
        $this->fmtRowFun = null;
        $this->callback  = null;
        $this->params    = null;
        $this->rowIDCol  = null;
        $this->printFmt  = null;
        $this->colCss    = array();
        $this->headRows   = array();           
        $this->footerRows = array();           
        $this->appendRowCallback = null;
        $this->appendHeadSameRow = array();
        $this->appendFooterSameRow = array();
        $this->cleanlink = false;
        $this->theadExt = null;
        $this->extAttr = array();
        $this->rowCssClsCode = null;
    }/*}}}*/
    public function addColumn($key,$name,$orderlink=null)
    {/*{{{*/
        $columns       = $this->columns;
        $orderItem     = $this->getOrderItem($orderlink); 
        $columns[$key] = new UColumn2($key,$name,$orderItem); 
        $this->columns = $columns;
        return $this->columns[$key];
    }/*}}}*/
    //数据回调方法
    public function setDataFunc($callback,$param_arr)
    {/*{{{*/
        $this->callback = $callback;
        $this->params   = $param_arr;
        if(!is_null($this->callback))
            $this->datas = call_user_func_array($this->callback,$this->params); 
    }/*}}}*/
    public function setCurData($datas)
    {/*{{{*/
        $this->datas = $datas;
    }/*}}}*/
    public function getCurData()
    {/*{{{*/
        return $this->datas;
    }/*}}}*/
    //设置列CSS
    public function setColHighlight($col,$cssClass)
    {/*{{{*/
        $css = $this->colCss;
        $css[$col] = $cssClass;
        $this->colCss = $css;
    }/*}}}*/
    //设置行ID的INDEX
    public function setRowIDIndex($col)
    {/*{{{*/
        $this->rowIDCol = $col;
    }/*}}}*/
    public function addRowExtAttr($attr,$valCode)
    {/*{{{*/
        $extAttr = $this->extAttr;
        $extAttr[$attr] = $valCode;
        $this->extAttr  = $extAttr;
    }/*}}}*/
    public function setRowCssCls($clsCode)
    {/*{{{*/
        $this->rowCssClsCode = $clsCode;
    }/*}}}*/
    //设置每行数据Format
    public function setFmtRowFunc($fmtFunc=null)
    {/*{{{*/
        $this->fmtRowFun = $fmtFunc;
    }/*}}}*/
    //设置 Excel 数据格式化方法
    public function printFmt($callback)
    {/*{{{*/
        $this->printFmt = $callback;
    }/*}}}*/
    public function listCol()
    {/*{{{*/
        return array_keys($this->columns);
    }/*}}}*/
    public function show($css="datatable")
    {/*{{{*/
        $html = $this->getHtml($css);
        echo $html;
    }/*}}}*/
    public function getHtml($css="datatable")
    {/*{{{*/
        $columns = $this->getColumsData($this->columns);
        $colCss = $this->colCss;
        if(is_array($this->datas))
        {/*{{{*/
            $trDatas = array();
            foreach ($this->datas as $row)
            {
                $showRow =  $this->getRowData($row);
                $colcnt  = count($this->columns);
                $id = $this->getColID($row);
                $extstr = $this->getExtAttr($row);
                $extCssCls = $this->getExtCssCls($row);
                $tr = "\n <tr id='".$id."' ".$extstr." ".$extCssCls." >";
                foreach ($this->columns as $field)
                {
                    $val = $field->execCallBack($row);
                    $val = $field->fmtVal($val,$row);
                    $cssClass = $colCss[$field->key];
                    if(!empty($cssClass))
                        $tr .= "\n <td name='".$field->key."' class = '".$cssClass."'>".$val."</td>";
                    else
                        $tr .= "\n <td name='".$field->key."'>".$val."</td>";
                }
                $tr .= "\n </tr>";

                $trDatas[] = $tr;

                if(!is_null($this->appendRowCallback))
                    $trDatas[] = call_user_func_array($this->appendRowCallback,array($row));
            }
        }/*}}}*/
        $trDatas = $this->addExtRows($trDatas);
        $desc = $this->desc;
        $name = $this->name;
        return $this->getTableHtml($name,$css,$desc,$columns,$trDatas);
    }/*}}}*/
    public function cleanlink()
    {/*{{{*/
        $this->cleanlink = true;
    }/*}}}*/
    public function appendHeadRow($trHtml = '<tr><td colspan=8>aaa</td></tr>')
    {/*{{{*/
        $headrows = $this->headRows;
        $headrows[] = $trHtml;
        $this->headRows = $headrows;
    }/*}}}*/
    public function appendFooterRow($trHtml ='<tr><td colspan=8>aaa</td></tr>')
    {/*{{{*/
        $footerRows = $this->footerRows;
        $footerRows[] = $trHtml;
        $this->footerRows = $footerRows;
    }/*}}}*/
    public function appendThead($trHtml ='<tr><td colspan=8>aaa</td></tr>')
    {/*{{{*/
        $this->theadExt = $trHtml;
    }/*}}}*/

    public function appendEachRow($callback)
    {/*{{{*/
        $this->appendRowCallback = $callback;
    }/*}}}*/
    public function appendHeadSameRow($rowData)
    {/*{{{*/
        $rows = $this->appendHeadSameRow;
        $rows[] = $rowData;
        $this->appendHeadSameRow = $rows;
    }/*}}}*/
    public function appendFooterSameRow($rowData)
    {/*{{{*/
        $rows = $this->appendFooterSameRow;
        $rows[] = $rowData;
        $this->appendFooterSameRow = $rows;
    }/*}}}*/

    protected function getTableHtml($name,$css,$desc,$columns,$datas)
    {/*{{{*/
        $html = " 
            <table id=\"$name\" name=\"$name\" class=\"$css\" summary=\"$desc\"> \r\n
            <caption></caption>
            <thead> ";
        $html .= (is_null($this->theadExt))?'':$this->theadExt;
        $html .= "
            <tr> \r\n $columns </tr> 
            </thead> \r\n
            <!--<tfoot> <tr> $columns </tr> </tfoot> -->\r\n
            <tbody> ";
        if(!empty($datas))
            foreach($datas as $data)
                $html .= $data."\r\n";
        $html .= "</tbody> \r\n
            </table> \r\n";
        return $html;
    }/*}}}*/
    protected function getColumsData($columns)
    {/*{{{*/
        $uri = $_SERVER['SCRIPT_NAME'];
        $args = $_SERVER['QUERY_STRING'];
        $orderitem = array();
        parse_str($args,$olditem); 
        $columsData = '';
        if(empty($columns))
            return $columsData;
        $colCss = $this->colCss;
        foreach($columns as $key=>$column)
        {
            $orderitem = $column->orderitem;
            $cssClass = $colCss[$key];
            if(!empty($cssClass))
                $columsData .= "<th name='".$key."' class='".$cssClass."'>";
            else
                $columsData .= "<th name='".$key."' >";

            if(!empty($orderitem) && !$this->cleanlink)
            {
                $newItem = array_merge($olditem,$orderitem);
                $link = $uri.'?';
                foreach($newItem as $item=>$val)
                    $link .= $item."=".$val."&";
                $link = substr($link,0,-1);
                $columsData .= "<a href='".$link."'>".$column->name."</a>";
            }
            else 
            {
                $columsData .= $column->name;
            }
            $columsData .= "</th> \r\n";
        }
        return $columsData;
    }/*}}}*/
    protected function getRowData($row)
    {/*{{{*/
        if($this->fmtRowFun !=null)
            $row = call_user_func($this->fmtRowFun,$row);
        return  array_intersect_key($row,$this->columns);
    }/*}}}*/
    protected function getOrderItem($orderlink)
    {/*{{{*/
        $orderItem = array();
        if(!empty($orderlink))
            parse_str($orderlink,$orderItem); 
        return $orderItem;
    }/*}}}*/
    protected function getColID($row)
    {/*{{{*/
        $col      = $this->columns[$this->rowIDCol];
        if(empty($col))
            return '';
        $indexVal = $col->execCallBack($row);
        $id       = $col->fmtVal($indexVal,$row);
        return $id;
    }/*}}}*/
    protected function getExtAttr($row)
    {/*{{{*/
        $extAttrs = $this->extAttr;
        $extStr = '';
        if(!empty($extAttrs))
            foreach($extAttrs as $key=>$extAttr)
            {
                $fmtFunc = create_function('$row',$extAttr);
                $val = $fmtFunc($row);
                $extStr .= $key."='".$val."' ";
            }
        return $extStr;
    }/*}}}*/
    protected function getExtCssCls($row)
    {/*{{{*/
        $rowCssClsCode = $this->rowCssClsCode ;
        $extStr = '';
        if(!empty($rowCssClsCode))
        {
            $fmtFunc = create_function('$row',$rowCssClsCode);
            $val = $fmtFunc($row);
            $extStr = " class='".$val."' ";
        }
        return $extStr;
    }/*}}}*/
    protected function addExtRows($datas)
    {/*{{{*/
        $this->initExtRow();
        $headRows   = $this->headRows;
        $footerRows = $this->footerRows;
        if(!empty($headRows))
            foreach($headRows as $row)
                array_unshift($datas,$row);
        if(!empty($footerRows))
            foreach($footerRows as $row)
                array_push($datas,$row);
        return $datas;
    }/*}}}*/
    protected function initExtRow()
    {/*{{{*/
        $headRow = $this->appendHeadSameRow;
        $footerRow = $this->appendFooterSameRow;
        if(!empty($headRow))
        {
            $headRows = $this->headRows;
            foreach($headRow as $row)
            {
                $tr = $this->getRowTr($row); 
                array_unshift($headRows,$tr);
            }
            $this->headRows = $headRows;
        }
        if(!empty($footerRow))
        {
            $footerRows = $this->footerRows;
            foreach($footerRow as $row)
            {
                $tr = $this->getRowTr($row); 
                array_push($footerRows,$tr);
            }
            $this->footerRows = $footerRows;
        }
    }/*}}}*/
    protected function getRowTr($row)
    {/*{{{*/
        $tr = "\n <tr id='".$id."'>";
        $colCss = $this->colCss;
        foreach ($this->columns as $field)
        {
            $val = $row[$field->key];
            $cssClass = $colCss[$field->key];
            if(!empty($cssClass))
                $tr .= "\n <td name='".$field->key."' class = '".$cssClass."'>".$val."</td>";
            else
                $tr .= "\n <td name='".$field->key."'>".$val."</td>";
        }
        $tr .= "\n </tr>";
        return $tr;
    }/*}}}*/
}/*}}}*/
class UTable2Excel
{/*{{{*/
    public function __construct($key,$except=null)
    {/*{{{*/
        DBC::requireNotNull($key,'key is null!'); 
        $sessSvc = ObjectFinder::find('SessionSvc');
        $this->table = $this->decodeTable($sessSvc->get($key));
        $this->except = empty($except)?null:unserialize(base64_decode($except));
    }/*}}}*/

    static public function getImportUrl($tableObj,$url='index.html?do=utable2excel',$except=array(),$fileName='noname')
    {/*{{{*/
        DBC::requireNotNull($tableObj); 
        DBC::requireNotNull($url); 
        $table = clone $tableObj;
        $table->datas=null;
        $table->colcss = null;
        $table->appendRowCallback = null;
        $table->appendHeadSameRow = null;
        $table->appendFooterSameRow = null;
        $table->cleanlink = null;
        $table->theadExt = null;
        $table->extAttr = null;
        $table->rowCssClsCode = null;

        $str = self::encodeTable($table); 

        $sessSvc = ObjectFinder::find('SessionSvc');
        $hadTable = $sessSvc->get('__tobj');
        $hadTable = unserialize($hadTable);
        $key = md5($str);
        if(!empty($hadTable))
        {
            if(count($hadTable)>=3)
            {
                $delTobj = array_shift($hadTable);
                $sessSvc->del($delTobj);
            }
        }
        else
        {
            $hadTable = array();
        }
        array_push($hadTable,$key);
        $sessSvc->save('__tobj',serialize($hadTable));
        $sessSvc->save($key,$str);

        $except = base64_encode(serialize($except));
        if(strpos($url,'?') === false)
            $url = $url."?t2e=".$key."&t2ename=".$fileName."&except=".$except;
        else
            $url = $url."&t2e=".$key."&t2ename=".$fileName."&except=".$except;
        return $url;
    }/*}}}*/

    public function show($toEncoding='gbk')
    {/*{{{*/
        $callback = $this->table->callback;
        $params   = $this->table->params;
        $paramKey = $this->needPage($params);
        $columns = $this->table->columns;
        if(!is_null($this->except))
        {
            foreach($columns as $key=>$col)
                if(in_array($key,$this->except))
                    unset($columns[$key]);
            $this->table->columns = $columns;
        }

        $excel = $this->getColumsData();
        $needExec = true;
        if(!is_null($paramKey))
        {
            $pageObj = new DataPage(100);
            $pageObj->goto(1);
            do{
                $params[$paramKey] = $pageObj;
                $datas = call_user_func_array($callback,$params); 
                $excel .= $this->getRowData($datas);
                $pageObj->nextPage();
                if(count($datas)<100)
                    $needExec = false;
            }while($needExec);
        }
        else
        {
            $datas = call_user_func_array($callback,$params); 
            $excel .= $this->getRowData($datas);
        }

        $excel = $this->encodingStr($excel,$toEncoding);
        echo $excel;
    }/*}}}*/
    protected function encodingStr($str,$toEncoding)
    {/*{{{*/
        $encode = mb_detect_encoding($str,"ASCII,UTF-8,CP936,EUC-CN,BIG-5,EUC-TW,gbk");
        if(strtolower($encode) != strtolower($toEncoding))
            $str = mb_convert_encoding($str,'gbk','UTF-8');
        return $str;
    }/*}}}*/
    protected function needPage($params)
    {/*{{{*/
        $paramKey= null;
        if(!empty($params))
            foreach($params as $key=>$val)
            {
                if(is_a($val,'DataPage'))
                {
                    $paramKey = $key; 
                    break;
                }
            }
        return $paramKey;
    }/*}}}*/

    protected function getColumsData()
    {/*{{{*/
        $columns = $this->table->columns;
        $columsData = '';
        if(empty($columns))
            return $columsData;
        foreach($columns as $column)
            $columsData .= $column->name.",";
        $columsData .= " \r\n ";
        return $columsData;
    }/*}}}*/
    protected function getRowData($datas)
    {/*{{{*/
        $columns = $this->table->columns;
        if(empty($columns))
            return '';
        if(is_array($datas))
        {
            foreach ($datas as $row)
            {
                if(!is_null($this->table->fmtRowFun))
                    $row = call_user_func($this->table->fmtRowFun,$row);
                $showRow =  array_intersect_key($row,$columns);
                $tr = '';
                foreach ($columns as $field)
                {
                    $val = $field->execCallBack($row);
                    $val = $this->filterRow($field->fmtVal($val,$row));
                    if(!is_null($this->table->printFmt))
                        $val = call_user_func_array($this->table->printFmt,array($val,$field->key));
                    $tar = array("\r","\n","\r\n",",");
                    $rep = array("","","","|");
                    $val = str_replace($tar,$rep,$val);
                    $val = (is_numeric($val) && $val>10000000)?"'".$val:$val;
                    $tr .= empty($val)?"-,":$val.",";
                }
                $tr .= "\r";
                $trData .= $tr;
            }
        }
        return $trData;
    }/*}}}*/
    protected function filterRow($str)
    {/*{{{*/
        $str = trim($str);
        $search = array (
            "'([,])[\s]+'",
            "'([\r])[\s]+'",
            "'([\n])[\s]+'",
            "'([\r\n])[\s]+'"
        );            
        $replace = array ("，","","","");
        return preg_replace ($search, $replace, $str);
    } /*}}}*/
    protected static function encodeTable($tableObj)
    {/*{{{*/
        return  base64_encode(serialize($tableObj));
    }/*}}}*/
    protected function decodeTable($tableStr)
    {/*{{{*/
        return  unserialize(base64_decode($tableStr));
    }/*}}}*/

}/*}}}*/
class TableUtls
{/*{{{*/
    public static function buildUrl($url, $desc, $pop=false,$extra="")
    {/*{{{*/
        if($pop)
        {
            return "<A href=\"$url\" target=\"_blank\" \"$extra\" >$desc</A>";
        }
        else
        {
            return "<A href=\"$url\" \"$extra\" >$desc</A>";
        }
    }/*}}}*/

    public static function popLayerUrl($url,$desc)
    {/*{{{*/
        return "<A href=\"$url\" rel=\"facebox\" >$desc</A>";
    }/*}}}*/

    static function filterHtml($str,$key=null)
    {/*{{{*/
        $str = trim($str);
        $str = strip_tags($str);
        return $str;
    } /*}}}*/
    static function filterHtmlTag($str,$key=null)
    {/*{{{*/
        $str = trim($str);
        $search = array ( "'<[^<]*>.*<[^<]*>'si","'([\r])[\s]+'");            
        $replace = array ("",'');
        return preg_replace ($search, $replace, $str);
    } /*}}}*/
}/*}}}*/
?>
