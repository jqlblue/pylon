<?php
///defgroup remote remote_service 

/** 
 * @brief 
 * @ingrop remote
 */
class RemoteCallException extends RuntimeException
{
    const ILLEGAL_ACCESS = "not correct access";
    public function __construct($message, $code = 0) 
    {
        parent::__construct($message, $code);
    }
}
/** 
 * @brief  simple webservice
 *
 * $websvc = new SimpleWebSvc();
 * $websvc->regSvc(new MySvcImpl());
 * echo $websvc->invokeSvc($_GET);
 *
 * @ingrop remote
 * @example simple_websvc_tc.php
 */
class SimpleWebSvc
{/*{{{*/
    private $_svcObj     = array();
    private $_svcMethod2Obj = array(); 
    private $_svcMethods = array();
    /** 
     * @brief  register a service object
     * 
     * @param $obj 
     * 
     * @return void
     */
    public function regSvc($obj)
    {/*{{{*/
        $class = new ReflectionClass(get_class($obj));
        $reflectName =  $class->getName();
        $this->_svcObj[$reflectName] = $obj;
        $this->regMethod($class); 

    }/*}}}*/
    private function regMethod($refCls)
    {/*{{{*/
        $methods     = $refCls->getMethods();
        $reflectName = $refCls->getName();
        foreach($methods as $m)
        {
            if($m->isPublic() && !$m->isConstructor() && !$m->isDestructor() && !$m->isStatic())
            {
                $this->_svcMethod2Obj[$m->getName()] = $reflectName;
                $this->_svcMethods[$reflectName][$m->getName()] = $m;
            }
        }
    }/*}}}*/
    private function findMethod($mName)
    {/*{{{*/
        if(isset($this->_svcMethod2Obj[$mName]))
        {
            $reflectName = $this->_svcMethod2Obj[$mName];
            return isset($this->_svcMethods[$reflectName][$mName])? $this->_svcMethods[$reflectName][$mName]:null;
        }
        return null;
    }/*}}}*/
    private function findObjByMethod($mName)
    {/*{{{*/
        
        return isset($this->_svcMethod2Obj[$mName])?$this->_svcObj[$this->_svcMethod2Obj[$mName]]:null;
    }/*}}}*/
    private function getCallContract($method,$op)
    {/*{{{*/
        $contract ="$op=".$method->getName()."&";
        $params= $method->getParameters();
        return $contract . JoinUtls::jarrayEx('&',$params,create_function('$params','return $params->getName()."=xxx";' ));
    }/*}}}*/
    private function help($op)
    {/*{{{*/
        $helpmsg="";
        foreach($this->_svcMethods as $ref)
        {
            foreach($ref as $m)
            {
                $helpmsg .= $this->getCallContract($m,$op)."<br>\n"; 
            }
        }
        return $helpmsg;
    }/*}}}*/
    private function mappingParams($method,$inputs,$op)
    {/*{{{*/
        $params= $method->getParameters();
        $args = array();
        $callContract = $this->getCallContract($method,$op);
        foreach($params as $p)
        {
            $pName = $p->getName();
            if(!isset($inputs[$pName])) 
                throw new RemoteCallException("No $pName param ! call as : $callContract");
            $args[$pName] = $inputs[$pName];
        }
        return $args;
    }/*}}}*/
    /** 
     * @brief invoke the service function
     * 
     * @param $data  arags  
     * support:
     *  do=help
     *  do=xxx&debug
     * 
     * @return  service function return's value
     */
    public function invokeSvc($data,$op="do")
    {/*{{{*/
        if(!isset($data[$op]) )throw new RemoteCallException("no tag the method");
        $mName = $data[$op];
        if ($mName == 'help')
            return $this->help($op);

        $method = $this->findMethod($mName);
        if(is_null($method)) throw new RemoteCallException("no this method: $mName");
        $obj= $this->findObjByMethod($mName);
        array_shift($data);
        $args = $this->mappingParams($method,$data,$op);
        $res = $method->invokeArgs($obj,$args);
        if(isset($data['debug'])&&$data['debug']==1)
        {

            $obj= $this->findObjByMethod('debug');
            $debugMethod = $this->findMethod('debug');
            $res=$debugMethod->invokeArgs($obj,array('result'=>$res));
        }
        return $res;
    }/*}}}*/
}/*}}}*/
?>
