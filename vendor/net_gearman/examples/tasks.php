<?php

require_once 'Net/Gearman/Client.php';

$set = new Net_Gearman_Set();

function result($func, $handle, $result) {
    var_dump($func);
    var_dump($handle);
    var_dump($result);
}

$sql = array(
    "SELECT * FROM users WHERE username = 'joestump'",
    "SELECT * FROM users WHERE username LIKE 'joe%' LIMIT 10",
    "SELECT * FROM items WHERE deleted = 0 LIMIT 10"
);

foreach ($sql as $s) {
    $task = new Net_Gearman_Task('SQL', array(
        'sql' => $s
    ));

    $task->attachCallback('result');
    $set->addTask($task);
}

$client = new Net_Gearman_Client(array('dev01'));
$client->runSet($set);

?>
