<?php
class SimpleService
{
    static $svcobjs=null;
    
    static public function bindSvc($svc)
    {
        self::$svcobjs[]=$svc;
    }

    public function xmlresult($obj)
    {/*{{{*/
        $xml = ' <?xml version="1.0" encoding="UTF-8"?>' ."\n" ;
        $xml .= "<call>\n" . self::obj2xml($obj)  . "</call>\n" ;
        return $xml;
    }/*}}}*/
    public function obj2xml($obj)
    {/*{{{*/
        $xml="";
        if(is_array($obj))
        {/*{{{*/
            foreach($obj as $k => $v)
            {
                if(is_array($v))
                {
                    $xml .=  "<$k>\n" . self::obj2xml($v) . "</$k>\n";
                }
                else
                {
                    $xml .= "<$k>$v</$k>\n";
                }
            }
            return $xml;
        }/*}}}*/
        if(is_bool($obj))
        {
            $xml =   $obj ? "true":"false";
        }
        else
            $xml .= "$obj";
        return $xml;
    }/*}}}*/
    static public function appServing($skey=null,$addtinitfun=null)
    {/*{{{*/
        $appname =  $_GET['_app'];
        if($addtinitfun != null)
            call_user_func($addtinitfun,$appname);
        self::serving($skey);
    }/*}}}*/
    static public function serving($skey)
    {/*{{{*/
        $debug=false;
        if(isset($_GET['debug']) && $_GET['debug'] == 1)
        {
            $debug=true;
            unset($_GET['debug']);
        }
        $result['result']="false";
        try
        {/*{{{*/
            BR::notNull($skey,"没有验证信息");
            //BR::isTrue(self::validateSin($_GET,$skey),"签名验证失败");
            //$args= RemoteCall::decode_array($_GET);
            $aps = AppSession::begin();
            $websvc = new SimpleWebSvc(); 
            foreach(self::$svcobjs as  $svc)
            {
                $websvc->regSvc($svc); 
            }
            if(!empty($_GET['fun']))
                $data = $websvc->invokeSvc($_GET,"fun");
            if(!empty($_GET['func']))
                $data = $websvc->invokeSvc($_GET,"func");
            if(!empty($_GET['action']))
                $data = $websvc->invokeSvc($_GET,"action");
            if(!empty($_GET['do']))
                $data = $websvc->invokeSvc($_GET,"do");
            $result['data']=$data;
            if($debug)
                var_dump($data);

            //echo $tag.RemoteCall::encode($data);
            $result['result']="true";
            $aps->commit();
//            echo self::obj2xml($result);
 //           return ;
        }/*}}}*/
        catch( Exception $e)
        {/*{{{*/
            $loger=LogerManager::getSvcLoger();
            $errorMsg = $e->getMessage();
            $errorPos = $e->getTraceAsString();
            $loger->err($errorMsg);
            $loger->err($errorPos);
            if($debug)
                var_dump($e);
            //$ce = new SvcCallException( $e);
            $result['data']= $errorMsg;
        }/*}}}*/
        if($_GET['__echo']=="json")
        {
            $echo = json_encode($result);
        }
        else if($_GET['__echo']=="jsonp")
        {
            $callback = ($_GET['__callback'])?$_GET['__callback']:"jsonp";
            $res = json_encode($result);
            $echo = "{$callback}($res);";
        }
        else
            $echo = Spyc::YAMLDump($result);
        echo $echo;
//        $arrYaml = syck_load($yaml);
//        var_dump($arrYaml,$result);
    }/*}}}*/

    static public function validateSin(&$data,$skey)
    {/*{{{*/
        if(isset($data['_sin']))
        {
            $osin = $data['_sin'];
            unset($data['_sin']);
            $sin = md5(serialize($data) .$skey);
            if($osin==substr($sin,0,6))
                return true;
            
        }
        return false;
    }/*}}}*/
}
