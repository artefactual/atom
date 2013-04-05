<?php

require_once 'Net/Gearman/Client.php';

function complete($func, $handle, $result) {
    var_dump($func);
    var_dump($handle);
    var_dump($result);
}

function fail($task_object) {
    var_dump($task_object);
}

$client = new Net_Gearman_Client('localhost:7003');
$set = new Net_Gearman_Set();
$jobs = array(
        'AddTwoNumbers' => array('1', '2'),
        'Multiply' => array('3', '4')
    );

foreach ($jobs as $job => $args) {
    $task = new Net_Gearman_Task($job, $args);
    $task->attachCallback("complete",Net_Gearman_Task::TASK_COMPLETE);
    $task->attachCallback("fail",Net_Gearman_Task::TASK_FAIL);
    $set->addTask($task);
}

$client->runSet($set);

?>