<?php
    require_once("simpletest/unit_tester.php");
    require_once("simpletest/reporter.php");

    class DBCTestCls
    {/*{{{*/
        private $a;
        private $b;
        private $c;
        public function __construct($a,$b,$c)
        {
            $this->a= DBC::requireNotNull($a);
            $this->b= DBC::requireNotNull($b);
            $this->c= DBC::requireNotNull($c);
        }
        public function fun1($a,$b,$c)
        {
            DBC::requireNotNull($a);
            DBC::requireNotNull($b);
            DBC::requireNotNull($c);
        }
    }/*}}}*/
    class DBCTestCase extends UnitTestCase
    {/*{{{*/
        public function testHowUse()
        {/*{{{*/
            DBC::$failAction = DBC::DO_WARN;
            DBC::requireTrue(false);
            $b= "mytest";
            DBC::requireEquals($b,"text",'$b');
            $b= "text";
            DBC::requireNotEquals($b,"text");
            DBC::unExpect($b);
            DBC::requireNotNull(null);
            DBC::requireNull($b);
        }/*}}}*/
        public function testUsecase1()
        {
            $obj = new DBCTestCls("a","b","c");
            $obj->fun1("a","b","c");
        }
    }/*}}}*/
?>
