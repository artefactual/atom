<?php

require_once 'Net/Gearman/Worker.php';

try {
    $worker = new Net_Gearman_Worker(array('dev01:7003', 'dev01:7004'));
    $worker->addAbility('Hello');
    $worker->addAbility('Fail');
    $worker->addAbility('SQL');
    $worker->beginWork();
} catch (Net_Gearman_Exception $e) {
    echo $e->getMessage() . "\n";
    exit;
}

?>
