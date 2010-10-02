<?php
$home=getenv('HOME');
require_once('simpletest/unit_tester.php');
require_once('simpletest/reporter.php');
require_once('pylon/src/autoload/class_loads.php');
require_once("pylon/src/autoload/tc_support.php");
require_once('config.php');
$root=getenv('PSIONIC_HOME');
ComboLoader::setup(__FILE__,
    new ClassLoader("$root/pylon/src", "$root/pylon/src/autoload_data.php"),
    new ClassLoader("$root/pylon/test","$root/pylon/test/autoload_data.php"));

DBC::$failAction = DBC::DO_EXCEPTION;

//TestAssemply::setup();
TestCaseExec::execTC($argv,"$root/pylon/test/tc_list.txt");
?>
