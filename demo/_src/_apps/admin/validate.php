<?php
require_once("init.php");

$loader = new MyFinder() ;
AdminSysAssemply::setup(null);
AppInputRuleLib::setup();
$svc= new InputCheckSvc();      //建立输入检查服务
$rev = $svc->validate($loader);
echo json_encode($rev);          //通过 JSON 的方式输出

?>
