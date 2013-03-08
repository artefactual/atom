<?php
/**
 * Net_Gearman_ConnectionTest
 *
 * PHP version 5
 *
 * @category   Testing
 * @package    Net_Gearman
 * @subpackage Net_Gearman_Task
 * @author     Till Klampaeckel <till@php.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version    CVS: $Id$
 * @link       http://pear.php.net/package/Net_Gearman
 * @since      0.2.4
 */
class Net_Gearman_TaskTest extends PHPUnit_Framework_TestCase
{
    /**
     * Unknown job type.
     *
     * @return void
     * @expectedException Net_Gearman_Exception
     */
    public function testExceptionFromConstruct()
    {
        new Net_Gearman_Task('foo', array(), null, 8);
    }

    /**
     * Test parameters.
     *
     * @return void
     */
    public function testParameters()
    {
        $uniq = uniqid();
        $task = new Net_Gearman_Task('foo', array('bar'), $uniq, 1);

        $this->assertEquals('foo', $task->func);
        $this->assertEquals(array('bar'), $task->arg);
        $this->assertEquals($uniq, $task->uniq);        
    }

    /**
     * @expectedException Net_Gearman_Exception
     */
    public function testAttachInvalidCallback()
    {
        $task = new Net_Gearman_Task('foo', array());
        $task->attachCallback('func_bar');
    }

    /**
     * @expectedException Net_Gearman_Exception
     */
    public function testAttachInvalidCallbackType()
    {
        $task = new Net_Gearman_Task('foo', array());
        $this->assertType('Net_Gearman_Task', $task->attachCallback('strlen', 666));
    }

    public static function callbackProvider()
    {
        return array(
            array('strlen',  Net_Gearman_Task::TASK_FAIL),
            array('intval',  Net_Gearman_Task::TASK_COMPLETE),
            array('explode', Net_Gearman_Task::TASK_STATUS),
        );
    }

    /**
     * @dataProvider callbackProvider
     */
    public function testAttachCallback($func, $type)
    {
        $task = new Net_Gearman_Task('foo', array());
        $task->attachCallback($func, $type);

        $callbacks = $task->getCallbacks();

        $this->assertEquals($func, $callbacks[$type][0]);
    }

    /**
     * Run the complete callback.
     *
     * @return void
     */
    public function testCompleteCallback()
    {
        $task = new Net_Gearman_Task('foo', array('foo' => 'bar'));

        $this->assertEquals(null, $task->complete('foo'));

        // Attach a callback for real
        $task->attachCallback('Net_Gearman_TaskTest_testCallBack');

        // build result and call complete again
        $json = json_decode('{"foo":"bar"}');
        $task->complete($json);

        $this->assertEquals($json, $task->result);

        $this->assertEquals(
            array('func' => 'foo', 'handle' => '', 'result' => $json),
            $GLOBALS['Net_Gearman_TaskTest']
        );

        unset($GLOBALS['Net_Gearman_TaskTest']);
    }

    /**
     * See that task has handle and server assigned.
     *
     * @return void
     */
    public function testTaskStatus()
    {
        $client = new Net_Gearman_Client();
 
        $task       = new Net_Gearman_Task('Reverse', range(1,5));
        $task->type = Net_Gearman_Task::JOB_BACKGROUND;
 
        $set = new Net_Gearman_Set();
        $set->addTask($task);
 
        $client->runSet($set);
 
        $this->assertNotEquals('', $task->handle);
        $this->assertNotEquals('', $task->server);
    }
}

/**
 * A test callback.
 *
 * @param string $func
 * @param string $handle
 * @param mixed  $result
 *
 * @return void
 */
function Net_Gearman_TaskTest_testCallBack($func, $handle, $result)
{
    $GLOBALS['Net_Gearman_TaskTest'] = array(
        'func'   => $func,
        'handle' => $handle,
        'result' => $result
    );
}