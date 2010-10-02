<?php
require_once("simpletest/unit_tester.php");
require_once("simpletest/reporter.php");

class TestService
{
    public function hello($name)
    {
        return "hello my friends $name";
    }
}

class SimpleWebSvcTC extends UnitTestCase
{
    public function testHowToUse()
    {
        $websvc = new SimpleWebSvc();
        $websvc->regSvc(new TestService());
        //?do=hello&name=qihoo 
        // as  obj->hello('qihoo');
        $GET['do']='hello';
        $GET['name']='qihoo';
        $res=$websvc->invokeSvc($GET);
        $this->assertEqual($res,TestService::hello('qihoo'));
    }
}
?>
