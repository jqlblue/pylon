<?php

/**\addtogroup datastruct
 * @{
 */
class XCmd 
{/*{{{*/
    protected $_code;
    public function __construct($code)
    {
        $this->_code = $code;
    }
    public function call($node)
    {
        call_user_func($this->_code,$node);
    }
}/*}}}*/
class CodeCmd
{/*{{{*/
    protected $_code;
    public function __construct($code)
    {
        $this->_code = $code;
    }
    public function call($node)
    {
        eval($this->_code);
    }
}/*}}}*/
class ToArrayCmd
{/*{{{*/
    public $_nodes = array();
    public function call($node)
    {
        $id = $node->id();
        $pid = TreeSNode::NULL_NODE_TAG;

        if($node->getParent() != null)
            $pid = $node->getParent()->id();
        $rid = TreeSNode::NULL_NODE_TAG;
        if($node->getRoot() !=null)
        {
            $rid = $node->getRoot()->id();
        }

        $data = $node->getData();
        $node = new TreeSNode( $id,$pid,$data,$rid);
        array_push($this->_nodes,$node);
    }
}/*}}}*/
class InitRootCmd
{/*{{{*/
    private $_root;
    public function __construct($root)
    {
        $this->_root = $root;
    }
    public function call($node)
    {
        $node->setRoot($this->_root);
    }
}/*}}}*/
class ClearCmd
{/*{{{*/
    public function call(&$node)
    {
        $node->clear();
        $node = null;
    }
}/*}}}*/

/** 
 * @brief  Tree 内部节点。
 */
class TreeNode
{/*{{{*/
    private $_id;
    private $_parent;
    private $_root;
    private $_sonList=array();
    private $_data;
    private $_level;

    public function __construct($id,$data)
    {/*{{{*/
        $this->_id = $id;
        $this->_data = $data;
    }/*}}}*/
    public function clear()
    {/*{{{*/
        $this->_data = null;
        $this->_level= null;
        $this->_sonList =null;
        $this->_parent =null;
        $this->_root =null;
        $this->_id =null;
    }/*}}}*/
    public function id()
    {/*{{{*/
        return $this->_id;
    }/*}}}*/
    public function addSon($son)
    {/*{{{*/
        DBC::requireIsa($son,'TreeNode');
        $son->setParent($this);
        array_push($this->_sonList,$son);
    }/*}}}*/
    public function getData()
    {/*{{{*/
        return $this->_data;
    }/*}}}*/
    public function setData($data)
    {/*{{{*/
        return $this->_data = $data;
    }/*}}}*/
    public function setRoot($node)
    {
        DBC::requireIsa($node,'TreeNode');
        $this->_root = $node;
    }
    public function getRoot()
    {
        return $this->_root;
    }
    public function haveSon()
    {
        return count($this->_sonList)>0;
    }
    public function getSons()
    {
        return $this->_sonList;
    }
    public function getParent()
    {
        return $this->_parent;
    }
    public function setParent($node)
    {
        DBC::requireIsa($node,'TreeNode');
        $this->_parent = $node;
    }
    public function getLevel()
    {
        return $this->_level;
    }

    public function setLevel($level)
    {
        $this->_level=$level;
    }
    function cacluLevel($node)
    {
        if($node->getParent == null)
            $node->setLevel(1);
        else
            $node->setLevel($node->getLevel()+1);
    }
}/*}}}*/


function treeCacluLevel($node)
{/*{{{*/
    if($node->getParent() == null)
        $node->setLevel(1);
    else
        $node->setLevel($node->getParent()->getLevel()+1);
}/*}}}*/
/** 
 * @brief  Tree的标准节点，对于与外部数据转换
 */
class TreeSNode 
{/*{{{*/
    const NULL_NODE_TAG="-1";
    private $_data;
    static private $_idName = "id";
    static private $_pidName = "pid";
    static private $_dataName = "data";
    static private $_rootName = "rid";
    /** 
     * @brief  设置映谢
     * 
     * @param $idName  
     * @param $pidName 父ID的名称,pid是default值 
     * @param $dataName  数据在输入数组中的key, data 是 default值
     * 
     * @return 
     */
    static public function setMapName($idName = "id",$pidName="pid",$dataName="data")
    {/*{{{*/
        self::$_idName = $idName;
        self::$_pidName = $pidName;
        self::$_dataName = $data;
    }/*}}}*/
    public function toArray()
    {/*{{{*/
        return  $this->_data;
    }/*}}}*/
    static public  function instanceByArray($data)
    {/*{{{*/
        return new TreeSNode((int)$data[self::$_idName],(int)$data[self::$_pidName], 
            $data[self::$_dataName],$data[self::$_rootName]);
    }/*}}}*/
    public function  __construct($id,$pid,$data,$rid=null)
    {/*{{{*/
        DBC::requireNotNull($id ,"id is not int");
        DBC::requireNotNull($pid ,"id is not int");
        $this->id = $id;
        $this->pid= $pid;
        $this->data = $data;
        $this->rid = $rid;
    }/*}}}*/
    public function __set($name,$val)
    {/*{{{*/
        $this->_data[$name] = $val;
    }/*}}}*/
    public function __get($name)
    {/*{{{*/
        return $this->_data[$name];
    }/*}}}*/
    public function id()
    {/*{{{*/
        return $this->id;
    }/*}}}*/
}/*}}}*/

/// @brief 
/// 
class TreeSNodeConvt
{/*{{{*/
    /** 
     * @brief  
     * 
     * @param $nestArr 
     * 
     * @return 
     */
    static public function convtNestArray($nestArr)
    {/*{{{*/
        $nodes = array(); 
        $key = key($nestArr);
        self::convtNestArrayImp($key,$nestArr[$key],TreeSNode::NULL_NODE_TAG,'generatorID',$nodes);
        return $nodes;
    }/*}}}*/
    static protected function convtNestArrayImp($key,$val,$parentID,$fun,&$nodeList)
    {/*{{{*/

        if(is_array($val))
        {
            $curID = call_user_func($fun);
            $data = array("name"=>$key,"path"=>null,"pageID"=>$val);
            $node = new TreeSNode($curID,$parentID,$data);
            $nodeList[]=$node;
            foreach($val as $key1=>$val1)
            {
                self::convtNestArrayImp($key1,$val1,$curID,$fun,$nodeList);
            }
        }
        else
        {

            $data = array("name"=>$key,"path"=>$val,"pageID"=>$val);
            $node = new TreeSNode(call_user_func($fun),$parentID,$data);
            $nodeList[]=$node;
        }
    }/*}}}*/
}/*}}}*/

/** 
 * @brief 
 * @example  tree_test.php
 */
class Tree 
{/*{{{*/
    const  FRONT_ORDER = 1;
    const  BACK_ORDER = 2;
    private $_rootNode;
    public function __construct($rootNode)
    {/*{{{*/
        $this->_rootNode = $rootNode;
        $this->initRoot();
    }/*}}}*/
    public function __destruct()
    {
        $this->clear();
    }

    public function getRoot()
    {/*{{{*/
        return $this->_rootNode;
    }/*}}}*/

    /** 
     * @brief 遍历Tree
     * 
     * @param $fun  遍历到的节点调用的函数
     * @param $orderType 遍历的顺序： 前序，中序，后序
     * 
     * @return void 
     */
    public function traversal($fun,$orderType = Tree::FRONT_ORDER)
    {/*{{{*/
        $this->traversalImp($fun,$this->_rootNode,$orderType);
    }/*}}}*/

    private function initRoot()
    {
        $this->traversal(new InitRootCmd($this->_rootNode),Tree::FRONT_ORDER);            
        $this->calcuLeve();
    }
    /** 
     * @brief 计算Tree的层次
     * 
     * @return void 
     */
    public function calcuLeve()
    {/*{{{*/
        $this->traversal(new XCmd("treeCacluLevel"),Tree::BACK_ORDER);
    }/*}}}*/

    public function clear()
    {/*{{{*/
        $cmd = new ClearCmd();
        $this->traversal($cmd,Tree::BACK_ORDER);
    }/*}}}*/

    /** 
     * @brief 
     * 
     * @return 
     */
    public function toTreeSNodeList()
    {/*{{{*/
        $fun = new ToArrayCmd();
        $this->traversal($fun,Tree::FRONT_ORDER);
        return $fun->_nodes;
    }/*}}}*/

    protected function traversalImp($fun,$curNode,$orderType)
    {/*{{{*/
        DBC::requireNotNull($orderType);
        if($curNode == null) return ;
        if($orderType == Tree::FRONT_ORDER) 
            $fun->call($curNode); 
        if($curNode->haveSon())
        {
            $sonNodes = $curNode->getSons();
            foreach($sonNodes as $son)
            {
                $this->traversalImp($fun,$son,$orderType); 
            }
        }
        if($orderType == Tree::BACK_ORDER)
            $fun->call($curNode); 
        return ;
    }/*}}}*/


    /** 
     * @brief  由一组TreeSNode 生成一个树
     * 
     * @param $list 
     * @param $nullNodeTag 
     * 
     * @return  Tree object
     */
    static public function instanceByList($list,$nullNodeTag=TreeSNode::NULL_NODE_TAG)
    {/*{{{*/
        $rootNode = null;
        $nodeList = array();
        foreach($list as $node)
        {
            $obj = $node->data;
            if(!isset($nodeList[$node->id]))
                $nodeList[$node->id] = new TreeNode($node->id,$obj);
            else
                $nodeList[$node->id]->setData($obj);

            if($node->pid == $nullNodeTag )
            {
                $rootNode = $nodeList[$node->id];
            }
            else
            {
                if(!isset($nodeList[$node->pid]))
                    $nodeList[$node->pid]= new TreeNode($node->pid,NULL);
                $nodeList[$node->pid]->addSon($nodeList[$node->id]);
            }
        }
        $tree =  new Tree($rootNode);
        return $tree;
    }/*}}}*/


}/*}}}*/

/** 
 *  @}
 */
?>
