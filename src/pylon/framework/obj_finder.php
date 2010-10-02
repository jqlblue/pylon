<?php

class RunSpace
{/*{{{*/
    static public $curSpace ='g';
    static public function changeSpace($newspace)
    {/*{{{*/
        $old = self::$curSpace;
        self::$curSpace=$newspace;
        return $old;
    } /*}}}*/

}/*}}}*/

class ScopeSwapSpace
{/*{{{*/
    private $keep;
    public function __construct($new)
    {
        $this->keep=RunSpace::changeSpace($new);
    }
    public function __destruct()
    {
        RunSpace::changeSpace($this->keep);
    }
    static public function change($new)
    {
        return  new ScopeSwapSpace($new);
    }
}/*}}}*/

class RunSpaceContainer
{/*{{{*/
    private $spaceableObjs= array();
    private $curObjs=null;
    protected function ensureSpace()
    {/*{{{*/
        $space= RunSpace::$curSpace;
        if(! isset($this->spaceableObjs[$space]))
            $this->spaceableObjs[$space] =array();
        $this->curObjs = & $this->spaceableObjs[$space];
    }/*}}}*/
    public function getObj($name)
    {/*{{{*/
        $this->ensureSpace();
        $obj=$this->curObjs[$name];
        DBC::requireNotNull($obj);
        return $obj;
    }/*}}}*/
    public function getObjs()
    {/*{{{*/
        $this->ensureSpace();
        return $this->curObjs;
    }/*}}}*/
    public function haveObj($name)
    {/*{{{*/
        $this->ensureSpace();
        return isset($this->curObjs[$name]);
    }/*}}}*/
    public function setObj($name,$v)
    {/*{{{*/
        $this->ensureSpace();
        $this->curObjs[$name]=$v;
    }/*}}}*/
    public function cleanObjs()
    {/*{{{*/
        $this->ensureSpace();
        return $this->curObjs=array();
    }/*}}}*/
}/*}}}*/
/**\addtogroup domin_mdl
 * @{
 */
class ObjectFinder extends RunSpaceContainer
{/*{{{*/
    static public function ins()
    {/*{{{*/
        static $finder = null;
        if(is_null($finder))
            $finder = new ObjectFinder();
        return $finder;
    }/*}}}*/

    private function registerImpl($name,$obj)
    {/*{{{*/
        if( $this->haveObj($name)) 
        {
            DBC::unExpect($name, "$name have register!");
        }
        $this->setObj($name,$obj);
    }/*}}}*/

    private function replaceReg($name,$obj)
    {/*{{{*/
        $this->setObj($name,$obj);
    }/*}}}*/

    private function findImpl($name)
    {/*{{{*/
        if(! $this->haveObj($name)) 
        {
            $names = Prompt::recommend($name,array_keys($this->getObjs()));
            $str   = JoinUtls::jarray(',',$names);
            DBC::unExpect($name, "$name no register!, it maybe one of list [$str] ");
        }
        return $this->getObj($name) ;
    }/*}}}*/


    static public function register($name,$obj)
    {/*{{{*/
        $finder = self::ins();
        $finder->registerImpl($name,$obj);
    }/*}}}*/

    static public function regByClass($obj)
    {/*{{{*/
        self::register(get_class($obj),$obj);
    }/*}}}*/
    static public function find($name)
    {/*{{{*/
        $finder = self::ins();
        return  $finder->findImpl($name);
    }/*}}}*/
    static public function clean()
    {/*{{{*/
        $finder = self::ins();
        $finder->cleanObjs();
    }/*}}}*/
    static public function replaceRegByTest($name,$obj)
    {
        $finder = self::ins();
        $finder->replaceReg($name,$obj);
    }
}/*}}}*/

/** 
 *  @}
 */
?>
