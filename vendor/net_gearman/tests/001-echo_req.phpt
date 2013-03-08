--TEST--
echo_req
--SKIPIF--
<?php
if (!file_exists(dirname(__FILE__) . '/tests-config.php')) {
    die('skip This test requires a test-config.php file.');
}
--FILE--
<?php
require_once dirname(__FILE__) . '/tests-config.php';
require_once 'Net/Gearman/Connection.php';

$s = Net_Gearman_Connection::connect(array_pop($servers));
Net_Gearman_Connection::send($s, 'echo_req', array('text' => 'helloworld'));

do {
    $ret = Net_Gearman_Connection::read($s);
} while (is_array($ret) && !count($ret));

print_r($ret);

Net_Gearman_Connection::close($s);

?>
--EXPECT--
Array
(
    [function] => echo_res
    [type] => 17
    [data] => Array
        (
            [text] => helloworld
        )

)
