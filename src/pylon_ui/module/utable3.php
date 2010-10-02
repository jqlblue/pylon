<?php
class UColumn3 extends PropertyObj
{/*{{{*/
    public function __construct($setProp)
    {/*{{{*/
        parent::__construct();
        $this->th = array();
        $this->td = array();
        $this->merge($setProp);
        $this->analyzeColSet();
    }/*}}}*/
    public function getTdCode($row,$params)
    {/*{{{*/
        return $this->getCode('td',$row,$params);
    }/*}}}*/
    public function getThCode($params)
    {/*{{{*/
        return $this->getCode('th',null,$params);
    }/*}}}*/

/*{{{*/
    protected function getCode($tag,$row=null,$params=null)
    {/*{{{*/
        $tagAttrs = $this->$tag;
        $attrs    = $tagAttrs['attr'];
        $attrStr  = '';
        if(!empty($attrs))
            foreach($attrs as $attr=>$val)
                $attrStr .= " $attr='".$this->getAttrVal($tag,$attr,$row,$params)."' "; 
        $html = $this->getHtmlVal($tag,$row,$params);
        return "<$tag $attrStr > $html </$tag> \r\n ";
    }/*}}}*/
    protected function analyzeColSet()
    {/*{{{*/
        $attrs = $this->getPropArray();
        $th_attr = $td_attr = array();
        if(!empty($attrs))
        {
            foreach($attrs as $attr=>$val)
            {
                $args = split("_" ,$attr,2);
                if(strtolower($args[0]) == 'th')
                {
                    if(strtolower($args[1]) == 'html')
                        $th['html'] = $val;
                    elseif(!empty($args[1]))
                        $th['attr']{$args[1]} = $val;
                }
                if(strtolower($args[0]) == 'td')
                {
                    if(strtolower($args[1]) == 'html')
                        $td['html'] = $val;
                    elseif(!empty($args[1]))
                        $td['attr']{$args[1]} = $val;
                }
            }
        }
        $this->th = $th;
        $this->td = $td;
    }/*}}}*/
    public function setTdAttr($attr,$val)
    {/*{{{*/
        $td = $this->td;
        $td['attr'][$attr] = $val;
        $this->td = $td;
    }/*}}}*/
    public function setThAttr($attr,$val)
    {/*{{{*/
        $th = $this->th;
        $th['attr'][$attr] = $val;
        $this->th = $th;
    }/*}}}*/
    protected function getAttrVal($tag,$attr,$row,$params)
    {/*{{{*/
        $tagAttrs = $this->$tag;
        $attrVal = $tagAttrs['attr'][$attr];
        list($callTag,$val) = split(":",$attrVal,2);
        if(strtolower($callTag) == 'c')
            return $this->execFmtCode($val,$row,$params);        
        if(strtolower($callTag) == 'f')
            return $this->execFmtFunc($val,$row,$params);
        return $attrVal;
    }/*}}}*/
    protected function getHtmlVal($tag,$row,$params)
    {/*{{{*/
        $tagAttrs = $this->$tag;
        $attrVal = $tagAttrs['html'];
        if(!empty($attrVal))
        {
            list($callTag,$val) = split(":",$attrVal,2);
            if(strtolower($callTag) == 'c')
                return $this->execFmtCode($val,$row,$params);        
            if(strtolower($callTag) == 'f')
                return $this->execFmtFunc($val,$row,$params);
            $code = 'return "'.$attrVal.'";';
            return $this->execFmtCode($code,$row,$params);        
//            return $attrVal;
        }
        else
        {
            if($tag == 'td')
            {
                $key = $this->td_name;
                if(is_object($row))
                    return $row->$key;
                else
                    return $row[$key];
            }
        }
    }/*}}}*/
    protected function execFmtCode($fmtCode,$row,$params)
    {/*{{{*/
        if(!empty($fmtCode))
        {
            $fmtCode = stripslashes($fmtCode);
            $key = $this->td_name;
            if(is_object($row))
                $val = $row->have($key)?$row->$key:'';
            else
                $val = $row[$key];
            $fmtFunc = create_function('$row,$val,$params',$fmtCode);
            return $fmtFunc($row,$val,$params);
        }
    }/*}}}*/
    protected function execFmtFunc($func,$row,$params)
    {/*{{{*/
        if(!is_null($func))
        {
            $callparams = array($row,$params);
            list($cls,$fun) = split("::",$func,2);
            $func = array($cls,$fun);
            return call_user_func_array($func,$callparams);
        }
    }/*}}}*/
/*}}}*/

}/*}}}*/
class URow3 extends PropertyObj
{/*{{{*/
    public function __construct($setProp=array())
    {/*{{{*/
        parent::__construct();
        if(!empty($setProp))
            $this->merge($setProp);
        $this->analyzeRowSet();
    }/*}}}*/
    public function addSet($setProp)
    {/*{{{*/
        $attrs = $this->getPropArray();
        $this->merge($setProp);
        $this->analyzeRowSet();
    }/*}}}*/

    public function getCode($cols,$row,$params)
    {/*{{{*/
        $attrs   = $this->tr['attr'];
        $attrStr = '';
        if(!empty($attrs))
            foreach($attrs as $attr=>$val)
                $attrStr .= "$attr='".$this->getAttrVal($attr,$row,$params)."' "; 
        $tds = '';
        if(!empty($cols))
            foreach($cols as $col)
                $tds .= $col->getTdCode($row,$params);
        return "<TR $attrStr> \r\n $tds </TR> \r\n ";
    }/*}}}*/
    public function addDiyCode($trCode)
    {/*{{{*/
        return $trCode."\r\n ";
    }/*}}}*/
    public function getTheadCode($cols,$params)
    {/*{{{*/
        $tds = '';
        if(!empty($cols))
            foreach($cols as $col)
                $tds .= $col->getThCode($params);
        return "<TR>$tds</TR> \r\n ";
    }/*}}}*/

    protected function analyzeRowSet()
    {/*{{{*/
        $attrs = $this->getPropArray();
        unset($attrs['tr']);
        $tr_attr = array();
        if(!empty($attrs))
        {/*{{{*/
            foreach($attrs as $attr=>$val)
            {
                $args = split("_" ,$attr,2);
                if(strtolower($args[0]) == 'tr')
                    $tr_attr{$args[1]} = $val;
            }
        }/*}}}*/
        $tr['attr'] = $tr_attr;
        $this->tr   = $tr;
    }/*}}}*/
    protected function getAttrVal($attr,$row,$params=null)
    {/*{{{*/
        $attrVal = $this->tr['attr'][$attr];
        $spAttr  = split(":",$attrVal,2);
        if(strtolower($spAttr[0]) == 'c')
            return $this->execFmtCode($spAttr[1],$row,$params);        
        if(strtolower($spAttr[0]) == 'f')
            return $this->execFmtFunc($spAttr[1],$row,$params);
        return $attrVal;
    }/*}}}*/
    protected function execFmtCode($fmtCode,$row,$params=null)
    {/*{{{*/
        if(!empty($fmtCode))
        {
            $fmtFunc = create_function('$row,$params',$fmtCode);
            return $fmtFunc($row,$params);
        }
    }/*}}}*/
    protected function execFmtFunc($func,$row=array(),$params=null)
    {/*{{{*/
        if(!is_null($func))
        {
            $callparams = array($row,$params);
            list($cls,$fun) = split("::",$func,2);
            $func = array($cls,$fun);
            var_dump($func);
            return call_user_func_array($func,$callparams);
        }
    }/*}}}*/
}/*}}}*/
class UTable3  extends PropertyObj
{/*{{{*/
    public function __construct($params=null)
    {/*{{{*/
        $this->columns = array();
        $this->params  = null;
        $this->datas   = array();
        $this->footRows = array();
        $this->headRows = array();
        $this->eachRowCallback = null;
        $this->tdAttrs = array();
        $this->row = new URow3(array()); 
    }/*}}}*/
    public function show()
    {/*{{{*/
        echo $this->getTableHtml();      
    }/*}}}*/
    public function addColumnByProp($colSetProp)
    {/*{{{*/
        $key           = DBC::requireNotNull($colSetProp->td_name);
        $columns       = $this->columns;
        $columns[$key] = new UColumn3($colSetProp); 
        $this->columns = $columns;
    }/*}}}*/
    public function setTableParams($params)
    {/*{{{*/
        $this->params = $params;
    }/*}}}*/

    public function setRowAttr($rowSetProp)
    {/*{{{*/
        $this->row->addSet($rowSetProp);
    }/*}}}*/
    public function setTableAttr($attrs)
    {/*{{{*/
        $tableAttrs = array();
        if(!empty($attrs))
        {
            foreach($attrs as $attr=>$val)
            {
                list($tag,$attrName) = split('_',$attr,2);
                if($tag = 'table')
                    $tableAttrs{$attrName} = $val;
            }
        }
        $this->tableAttrs = $tableAttrs;
    }/*}}}*/
    public function setDataFunc($callback,$params)
    {/*{{{*/
        if(!is_null($callback))
            $this->datas = call_user_func_array($callback,$params); 
    }/*}}}*/
    public function setCurData($datas)
    {/*{{{*/
        $this->datas = $datas;
    }/*}}}*/
    public function setTdAttr($col,$attr,$val)
    {/*{{{*/
        $tdAttrs       = $this->tdAttrs;
        $tdAttrs[$col][$attr] = $val;
        $this->tdAttrs = $tdAttrs;
//        $columns = $this->columns;
//        $column = DBC::requireNotNull($columns[$col]);
//        $columns[$col]->setTdAttr($attr,$val);
//        $this->columns = $columns;
    }/*}}}*/
    protected function setDefTdAttr()
    {/*{{{*/
        $tdAttrs       = $this->tdAttrs;
        if(!empty($tdAttrs))
        {
            $columns = $this->columns;
            foreach($tdAttrs as $col=>$attrs)
            {
                DBC::requireNotNull($columns[$col]);
                foreach($attrs as $attr=>$val)
                    $columns[$col]->setTdAttr($attr,$val);
            }
            $this->columns = $columns;
        }
        unset($tdAttrs);
    }/*}}}*/

    public function appendEachRow($callback)
    {/*{{{*/
        $this->eachRowCallback = $callback;
    }/*}}}*/
    public function appendFootRow($code='<tr><td>eg</td></tr>')
    {/*{{{*/
        $footRows = $this->footRows;
        $footRows[] = $code;
        $this->footRows = $footRows;
    }/*}}}*/
    public function appendHeadRow($code='<tr><td>eg</td></tr>')
    {/*{{{*/
        $headRows = $this->headRows;
        $headRows[] = $code;
        $this->headRows = $headRows;
    }/*}}}*/

    public function getTableHtml()
    {/*{{{*/
        $this->setDefTdAttr();
        $datas      = $this->datas;
        $params     = $this->params;
        $tableAttrs = $this->getTableAttr();

        $html  = "<TABLE $tableAttrs > \r\n";
        $html .= "<THEAD>  \r\n";
        $html .= $this->row->getTheadCode($this->columns,$params);
        $html .= "</THEAD> \r\n ";
        $html .= "<TBODY>  \r\n";
        $html .= $this->addHeadRows();
        if(!empty($datas))
            foreach($datas as $data)
            {
                $html .= $this->row->getCode($this->columns,$data,$params);
                if($this->have('eachRowCallback') && $this->eachRowCallback)
                {
                    $callparams = array($data);
                    $trCode = call_user_func_array($this->eachRowCallback,$callparams);
                    $html .= $this->row->addDiyCode($trCode);
                }
            }
        $html .= $this->addFootRows();
        $html .= "</TBODY> \r\n";
        $html .= "</TABLE> \r\n";
        return $html;
    }/*}}}*/


    protected function getTableAttr()
    {/*{{{*/
        $tableAttrs = $this->tableAttrs;
        $str = '';
        if(!empty($tableAttrs))
            foreach($tableAttrs as $attr=>$val)
                $str .= " ".$attr."='".$val."' ";
        return $str;
    }/*}}}*/
    protected function addFootRows()
    {/*{{{*/
        $footRows = $this->footRows;
        $code = '';
        if(!empty($footRows))
            foreach($footRows as $row)
               $code .= $row." \r\n ";
        return $code;
    }/*}}}*/
    protected function addHeadRows()
    {/*{{{*/
        $headRows = $this->headRows;
        $code = '';
        if(!empty($headRows))
            foreach($headRows as $row)
               $code .= $row." \r\n ";
        return $code;
    }/*}}}*/
}/*}}}*/
/************************** Demo ******************************
=========== Action Code =============
$table = new UTable3();
$table->setDataFunc(array('UIDemoActions','table3Data'),array($vars->pageObj));
$table->appendEachRow(array('UIDemoActions','previewSpace'));
$xcontext->table = $table;
=========== Smarty Tpl Code ============
{utable obj=$table table_id='utable_test' table_class='datalist' params=$xxx }
{row tr_id='c:return $row->id();' tr_relid='c:return $row->updatetime();' }
{col th_html='ID'       td_name='id'         td_html='c:return $row->id();'}
{col th_html='最后更新' td_name='update'     td_html='c:return $row->updatetime();' }
{col th_html='备注'     td_name='note'       td_html='c:return MiniOPs::upAttribute(htmlspecialchars($row->note),"Posisetting",$row->id(),"note","备注");'}
{col th_html='角色'     td_name='role'       td_html='c:return $row->audiencerole->getInfo();'}
{col th_html='媒体'     td_name='sowner'     td_html='c:return $row->siteowner->username." (".$row->siteowner->id().")";'}
{col th_html='广告领域' td_name='advdomain'  td_html='c:return $row->advDomain->name;'}
{col th_html='TAG'      td_name='tag'        td_html='f:UIDemoActions::getPosTags'}
{col th_html='播放器'   td_name='player'     td_html='c:return $row->adplayer->id()." (".$row->showspec->width."X".$row->showspec->height.")";'}
{col th_html='绑定任务' td_name='taskobj'    td_html='f:UIDemoActions::getBindCtrl'}
{col th_html='匹配策略' td_name='matchsgt'   td_html='c:return $row->getBindCtrl()->getMatcherDesc();'}
{col th_html='有效期'   td_name='lifestatus' td_html='c:return MiniOPs::lifestatus($row->lifestatus,"PosiSetting",$row->id());'}
{col th_html='操作'     td_name='op'         td_html='f:UIDemoActions::getOp'}
{/utable}
****************************************************************/

class UTable3Excel
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
        $table->rowCallback = null;

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
                $row .= $tr;
            }
        }
        return $row;
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
?>
