<?php
include("config.php");
include("pui/css_load.php");
$style="pui";
$cssfile= array( 
    "$style/plugin/datePicker.css",
    "$style/plugin/facebox.css",
);
CssLoad::load($cssfile,__FILE__,Conf::CSS_RELEASE);
?>
