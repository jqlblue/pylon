<?php
require_once('pylon/autoload/class_loads.php');  //autoload class 
require_once('used/config.php');               
Sys::offLocalCache(); // for dev or debug  close the autoload cache.

$pl=Conf::PSIONIC . "/pylon/";
$pui=Conf::PSIONIC . "/pylon_ui/";
ComboLoader::setup(__FILE__ 
    ,new ClassLoader($pl,"$pl/autoload_data.php")   //sgtlib autoload data
    ,new ClassLoader($pui,"$pui/autoload_data.php")   
    ,new ClassLoader(Conf::APP_ROOT,Conf::APP_ROOT."/common_load_data.php")
    );

require_once('assemblys_console.php');
ConsoleAssemply::setup(null);
?>
