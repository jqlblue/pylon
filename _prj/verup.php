<?php
$home=getenv("PSIONIC_HOME");
require_once("$home/src/pylon/utls/version.php");
if($argc != 3 ) 
{
    echo "args error!!";
    exit;
}

$file=$argv[1];
SysVersion::init($file);
$ver = SysVersion::ins();
switch($argv[2])
{/*{{{*/
    case "fixbug":
        $ver->fixbug();
        break;
    case "feature":
        $ver->featureUpgrade();
        break;
    case "struct":
        $ver->structUpgrade();
        break;
    default:
        break;
}/*}}}*/
$ver->commit();
$ver->save();
echo $ver->verinfo();

?>
