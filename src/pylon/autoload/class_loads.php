<?php

/**\addtogroup autoload
 * @{
 */
function __autoload($classname)
{/*{{{*/

    if(Sys::$_autoLoader != null  )
    {

        $path= Sys::$_autoLoader->getCalssPath($classname);
        if ($path != null)
        {
            include("$path");
            return ;
        }
        else
        {/*{{{*/
            $info="";
            $info .= "******* autoload error *********<br>\n";
            $info .= "not found classname : $classname <br>\n";
            $msg = Sys::$_autoLoader->getRecommendMsg($classname);
            $info .= "is maybe one of  [ $msg]";
            echo $info;
            throw new LogicException("load class $classname define faiure!!, $info");
        }/*}}}*/
    }
    else
    {
        echo "******* autoload not init *********<br>\n";
        echo "not found classname : $classname <br>\n";
        throw new LogicException("load class $classname define faiure!!");
    }
}/*}}}*/

class Sys
{/*{{{*/
    const CACHE_NO=0;
    const CACHE_AUTO=1;
    static $cacheimpl=1;
    static public $_autoLoader =null;
    static public function offLocalCache()
    {
        self::$cacheimpl=Sys::CACHE_NO;
    }
    static public function localCache()
    {/*{{{*/
        static $_cache =null;
        if($_cache) return $_cache;
        require("cache_svc.php");
        $stg = new CacheStg(100,null);
        $_cacheSvc = new NullDriver(); 
        if(self::$cacheimpl == Sys::CACHE_AUTO)
        {
            if(EADriver::isEnable() )
            {
                $_cacheSvc= new EADriver();
            }
            elseif(APCDriver::isEnable() )
            {
                $_cacheSvc= new APCDriver();
            }
            elseif(MemCacheSvc::isEnable())
            {
                $_cacheSvc= new MemCacheSvc(MemCacheSvc::localhostConf());
            }
        }
        $_cache=new CacheSvcWarpper("sys",$_cacheSvc,$stg);
        return $_cache;
    }/*}}}*/

    static public function classExists($clsName)
    {
        return Sys::$_autoLoader->getCalssPath($clsName) != null;
    }
    static public function className($iclsname)
    {
        $name= Sys::$_autoLoader->getClassName($iclsname);
        return $name;
    }
}/*}}}*/
class ComboLoader
{/*{{{*/
    private $loaders =null;
    private $loadersCnt=null;
    public function __construct($loaders)
    {
        $this->loaders = $loaders;
        $this->loadersCnt=count($loaders);
    }

    public function pushImpl($loader)
    {
        array_push($this->loaders,$loader);
        $this->loadersCnt  +=1;
    }
    static function setup($key)
    {/*{{{*/
        $loaders = func_get_args();
        array_shift($loaders);
        $_cache = Sys::localCache();
        Sys::$_autoLoader = new CacheProxy($_cache,new ComboLoader($loaders),$key);
    }/*}}}*/
    static function push($loader)
    {
        Sys::$_autoLoader->dao->pushImpl($loader);
    }
    public function getCalssPath($clsName)
    {/*{{{*/
        for($i=0 ; $i<$this->loadersCnt ; $i++)
        {
            $loader=$this->loaders[$i];
            $path = $loader->getCalssPath($clsName);
            if($path !=null ) return $path;
        }
        return null;
    }/*}}}*/
    public function getClassName($icls)
    {/*{{{*/
        for($i=0 ; $i<$this->loadersCnt ; $i++)
        {
            $loader=$this->loaders[$i];
            $name= $loader->getClassName($icls);
            if($name!=null ) return $name;
        }
        return null;
    }/*}}}*/
    public function getRecommendMsg($clsName)
    {/*{{{*/
        $recs=array();
        foreach($this->loaders as $loader)
        {
            $recs = array_merge($recs,$loader->recommend($clsName));
        }
        $msg =  JoinUtls::jarray('<br>',$recs);
        return $msg;
    }/*}}}*/
}/*}}}*/
class ClassLoader 
{/*{{{*/
    private $root=null;
    private $data=null;
    private $classPaths=null;
    public function __construct($root,$datafile)
    {/*{{{*/
        $this->root=$root;
        $this->datafile= $datafile;
    }/*}}}*/
    public static function setup($key,$root,$datafile)
    {/*{{{*/
        $_cache = Sys::localCache();
        Sys::$_autoLoader = new CacheProxy($_cache,new ClassLoader($root,$datafile),$key);
    }/*}}}*/
    public function getCalssPath($clsName)
    {/*{{{*/
        $classpath = $this->getClassMapDef();
        return isset($classpath[$clsName])? $classpath[$clsName] : null  ;
    }/*}}}*/
    public function getClassName($icls)
    {/*{{{*/
        $classpath = $this->getClassMapDef();
        foreach($classpath  as $name =>$v)
        {
            if(strcasecmp($name,$icls) == 0 )
            {
                return $name;
            }
        }
        return null;
    }/*}}}*/
    public function recommend($clsName)
    {/*{{{*/
        $this->classPaths = $this->getClassMapDef();
        $recommend = Prompt::recommend($clsName,array_keys($this->classPaths));
        return $recommend;
    }/*}}}*/
    public function getClassMapDef()
    {/*{{{*/
        $file= $this->datafile;
        $ROOT=$this->root;
        include("$file");
        return $data;
    }/*}}}*/

    public function getRecommendMsg($clsName)
    {/*{{{*/
        require_once('pylon/utility/utility.php');
        require_once('pylon/tility/join_utls.php');

        $rs = $this->recommend($clsName);
        $msg =  JoinUtls::jarray(',',$rs);
        return $msg;
    }/*}}}*/
}/*}}}*/

/** 
 *  @}
 */
?>
