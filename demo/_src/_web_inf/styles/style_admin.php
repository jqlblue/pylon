<?php
include("../../_config.php");
include("pui/css_load.php");
$style="pui";
$cssfile= array( 
    "reset.css",
    "pstyle.css",
    "$style/plugin/facebox.css",
    "$style/plugin/cluetip.css",
    "ball_admin.css",
//    "utask.css", 
);
CssLoad::load($cssfile,__FILE__,Conf::CSS_RELEASE);
