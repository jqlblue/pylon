<?php

class TestX
{
    private $name;
    public function __construct($name)
    {
        $this->name =$name;
    }
}
class IDGenterSvcTC extends UnitTestCase
{
    private $_executer;
    private $_tableName;

    public function __construct()
    {/*{{{*/
        $dbConf =  Conf::getDBConf();
        $this->_executer = new FastSQLExecutor( $dbConf->host,$dbConf->user,$dbConf->password,$dbConf->name);
        $this->_tableName = "id_genter";

    }/*}}}*/


    public function setUp()
    {/*{{{*/
        $cmd = "CREATE TABLE IF NOT EXISTS {$this->_tableName}
        (
                    id                             integer(11),
                    obj                            varchar(30), 
                    step  integer(11)
               )";

        $this->assertFalse($this->_executer == NULL);
        $this->_executer->exeNoQuery($cmd);

        $cmd = "SELECT * FROM {$this->_tableName}";
        $row = $this->_executer->query($cmd);
        if(empty($row))
        {
            $cmds[] = "INSERT INTO {$this->_tableName}(id, obj, step) VALUES(1, \"test\", 10)";
            $cmds[] = "INSERT INTO {$this->_tableName}(id, obj, step) VALUES(1, \"other\", 10)";
            $this->_executer->exeNoQuerys($cmds);
        }
    }/*}}}*/

    public function testCreate()
    {/*{{{*/
        $gener = new IDGenterSvcImp($this->_executer);
        $otherid=array();
        $testid=array();
        for($i = 0; $i < 20; $i++)
        {
            $otherid[] = $gener->createID('other');
            $testid[] =  $gener->createID('test');   
        }
        for($i=1 ; $i<19; $i++)
        {
            $this->assertTrue($otherid[$i-1]<$otherid[$i]);
            $this->assertTrue($testid[$i-1]<$testid[$i]);
        }
    }/*}}}*/

    public function testSameObj()
    {/*{{{*/
        $a = new TestX("a");
        $b = new TestX("a");
        $c = clone $a;
        $this->assertTrue($a == $b );
        $this->assertTrue($a !== $b );
        $this->assertTrue($a !== $c );
    }/*}}}*/
}

?>

