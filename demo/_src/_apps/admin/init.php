<?php
require_once("../../_config.php");
require_once('pylon/autoload/class_loads.php');  //autoload class 
//require_once('admin_config.php');               
$pl = Conf::PYLON. "/pylon/";
$pui= Conf::PYLON. "/pylon_ui/";
ComboLoader::setup(__FILE__ 
    ,new ClassLoader($pl,"$pl/autoload_data.php")   //sgtlib autoload data
    ,new ClassLoader($pui,"$pui/autoload_data.php")   
    ,new ClassLoader(Conf::PRJ_ROOT  ,Conf::PRJ_ROOT."/_src/_autoload_data.php")
    ,new ClassLoader(Conf::ZMARK_ROOT,Conf::ZMARK_ROOT."/autoload_data.php")
    );
