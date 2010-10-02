<?php
header("Content-Type: text/html;charset=utf-8");
require_once("init.php");
$cacheSvc=null;
AdminSysAssembly::setup($cacheSvc);

SimpleService::bindSvc(new UserImpl());
SimpleService::appServing("macubegic");
