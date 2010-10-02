<?php
require_once("../../_config.php");
$lib = "pui";
$jsfile[] = "jquery-1.3.2.min.js";
$jsfile[] = "$lib/jquery/jquery.apollo.js";
$jsfile[] = "$lib/jquery/jquery.cookie.js";
$jsfile[] = "$lib/jquery/jquery.cluetip.js";
$jsfile[] = "$lib/jquery/facebox.admin.js";
$jsfile[] = "$lib/jquery/jquery.form.js";
$jsfile[] = "$lib/jquery/jquery.elementReady.js";
$jsfile[] = "apollo_common.js";
$jsfile[] = "apollo_admin.js";
$jsfile[] = "apollo.adminplus.js";
$jsfile[] = "$lib/pui.js";

$fmd5 = substr(md5("js_admin_pkg"),0,7);
echo "; if(typeof __js_loaded_{$fmd5}==\"undefined\"){\n";
foreach($jsfile as $file)
{
    include($file);
    echo "\n";
}
echo "\n__js_loaded_{$fmd5} = true;\n}";
