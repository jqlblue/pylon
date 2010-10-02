<?php
require_once('simpletest/unit_tester.php');
require_once('simpletest/reporter.php');
     
class EchoObj
{/*{{{*/
    private $_data;
    public function __construct($data)
    {
        $this->_data = $data;
    }
    public function echoData()
    {
        
        if(is_array($this->_data))
        {
            foreach($this->_data as $item)
            {
                echo $item."|";
            }
        }
        else
            echo $this->_data."|";
    }
    static public function create($data)
    {
        return new EchoObj($data);
    }
}/*}}}*/
class TestFacory
{/*{{{*/
    function create($data)
    {
        return new EchoObj($data);
    }
}/*}}}*/
function createID()
{/*{{{*/
    static $id =0;
    ++ $id;
    return $id;
}/*}}}*/
class  PrintObj
{/*{{{*/
    public function call($node)
    {

        $obj= $node->getData();
        echo $obj;
        echo $node->id()."-";
        if($node->getParent() !=null)
        echo $node->getParent()->id()."\n";
    }
}/*}}}*/
class BigObj
{/*{{{*/
    private $data=null;
    public function __construct()
    {
        $data= array();
        for($i=0; $i< 1000; $i++)
        {
            $this->data[$i]= "$i,i love  you! ggggggggggggggggggggggggggggggggggxxxx";
        }
    }
}/*}}}*/
class TreeTest extends UnitTestCase
{/*{{{*/
    public function testA()
    {/*{{{*/
        $data[]= new TreeSNode(1,-1,1.2);
        $data[]= new TreeSNode(2, 1,1.2);
        $data[]= new TreeSNode(3, 1,1.2);
        $data[]= new TreeSNode(4, 1,1.2);
        $data[]= new TreeSNode(5, 2,1.2);
        $data[]= new TreeSNode(6, 2,1.2);
        $data[]= new TreeSNode(7, 4,1.2);
        $data[]= new TreeSNode(8, 4,1.2);

        $tree = Tree::instanceByList($data);
        $tree->traversal(new PrintObj,Tree::FRONT_ORDER);
        $nodes = $tree->toTreeSNodeList();
        $tree2 = Tree::instanceByList($nodes);
        $this->assertEqual($tree->getRoot()->id(),$tree2->getRoot()->id());
        echo "\n-------------\n";
        $tree->traversal(new PrintObj());
        echo "\n-------------\n";
        $tree2->traversal(new PrintObj());
        echo "\n-------------\n";
    }/*}}}*/
    private function createBigTree()
    {/*{{{*/
        $data[]= new TreeSNode(1,-1,new BigObj());
        $data[]= new TreeSNode(2, 1,new BigObj());
        $data[]= new TreeSNode(3, 1,new BigObj());
        $data[]= new TreeSNode(4, 1,new BigObj());
        $data[]= new TreeSNode(21, 2,new BigObj());
        $data[]= new TreeSNode(22, 2,new BigObj());
        $data[]= new TreeSNode(23, 2,new BigObj());
        $data[]= new TreeSNode(24, 2,new BigObj());
        $data[]= new TreeSNode(25, 2,new BigObj());
        $data[]= new TreeSNode(26, 2,new BigObj());
        $data[]= new TreeSNode(41, 4,new BigObj());
        $data[]= new TreeSNode(42, 4,new BigObj());
        $data[]= new TreeSNode(43, 4,new BigObj());
        $data[]= new TreeSNode(44, 4,new BigObj());
        $data[]= new TreeSNode(45, 4,new BigObj());
        $data[]= new TreeSNode(46, 4,new BigObj());
        $data[]= new TreeSNode(47, 4,new BigObj());

        $tree = Tree::instanceByList($data);
    }/*}}}*/
    public function testBigTree()
    {/*{{{*/
        $this->createBigTree();
        $this->createBigTree();
        $this->createBigTree();
        $this->createBigTree();
    }/*}}}*/
}/*}}}*/
?>
