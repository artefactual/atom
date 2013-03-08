--TEST--
Net_Gearman_Set, Net_Gearman_Client::runSet()
--SKIPIF--
<?php
die('skip THIS TEST IS BROKEN.');
if (!file_exists(dirname(__FILE__) . '/tests-config.php')) {
    die('skip This test requires a test-config.php file.');
}
--FILE--
<?php
require_once dirname(__FILE__) . '/tests-config.php';
require_once 'Net/Gearman/Client.php';

$sums = array(
    array(1, 2, 5),
    array(12, 34, 100),
    array(120, 1000)
);

$set = new Net_Gearman_Set();
foreach ($sums as $s) {
    $task = new Net_Gearman_Task('Sum', $s);
    $set->addTask($task);
}

$client = new Net_Gearman_Client($servers);
$client->runSet($set);

$superSum = 0;
foreach ($set as $task) {
    $superSum += $task->result["result"];
}

var_dump($superSum);

?>
--EXPECT--
int(1274)
