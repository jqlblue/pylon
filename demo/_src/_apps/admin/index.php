<?php
require_once("init.php");
XTools::regActFinder( new XFinder("./_act_conf.php") );
AdminSysAssembly::setup();
XController::process("do","login");  
