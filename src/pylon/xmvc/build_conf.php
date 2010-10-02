<?php

class ActionParser
{/*{{{*/
    static public function parse($line)
    {/*{{{*/
        $regex = "/class\s+(Action_([^\s]+))/";

        if(!preg_match($regex,$line,$regs))  return  ; 

        list($all,$cls,$name) = $regs;
        $clsobj     = new ReflectionClass($cls);
        $data = array();
        $data['name'] = $name;
        $data['cls']  = $cls;
        $data['path'] = $clsobj->getFileName();
        if(!$clsobj->isSubclassOf(XAction))  return ;
        foreach ( $clsobj->getStaticProperties()  as $k => $v )
        {
            $data['extends'][$k] = $v;
        }
        return array($name , serialize($data));
    }/*}}}*/
    static public function parseFiles($in,$out)
    {/*{{{*/
        $inHandle =  fopen($in,"r");
        $outHandle =  fopen($out,"w");
        if(!$inHandle  || !$outHandle) return ;
        fwrite($outHandle, "<?php \n");
        while(! feof($inHandle))
        {
            $line = fgets($inHandle);
            if(empty($line)) continue ;
            list($key,$data) = self::parse($line);
            if(empty($data)) continue ;
            fwrite($outHandle, "\$_data['$key'] = '$data';\n");

        }
        fclose($inHandle);
        fclose($outHandle);

    }/*}}}*/
}/*}}}*/


if($argc != 4 )
{
    echo __FILE__ . "  clsload.php  in.data  out.data" ;
    exit;
}
$clsload  = $argv[1]; 
$in       = $argv[2]; 
$out      = $argv[3]; 
require_once($clsload);
ActionParser::parseFiles($in,$out);

