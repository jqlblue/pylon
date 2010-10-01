<?php
require_once 'Net/Gearman/Worker.php';
require_once('qq.php');


try {
    $worker = new Net_Gearman_Worker(array('127.0.0.1:4730'));
    $worker->addAbility('QQ');
    $worker->beginWork();
} catch (Exception $e) {
    echo $e->getMessage() . "\n";
    exit;
}

?>
