<?php
/**
 * Net_Gearman_TaskTest.
 */
class Net_Gearman_TaskTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Unknown job type.
     *
     * @return void
     */
    public function testExceptionFromConstruct()
    {
        $this->expectException(\Net_Gearman_Exception::class);
        new Net_Gearman_Task('foo', [], null, 8);
    }

    /**
     * Test parameters.
     *
     * @return void
     */
    public function testParameters()
    {
        $uniq = uniqid();
        $task = new Net_Gearman_Task('foo', ['bar'], $uniq, 1);

        $this->assertEquals('foo', $task->func);
        $this->assertEquals(['bar'], $task->arg);
        $this->assertEquals($uniq, $task->uniq);
    }

    public function testAttachInvalidCallback()
    {
        $this->expectException(\Net_Gearman_Exception::class);
        $task = new Net_Gearman_Task('foo', []);
        $task->attachCallback('func_bar');
    }

    public function testAttachInvalidCallbackType()
    {
        $this->expectException(\Net_Gearman_Exception::class);
        $task = new Net_Gearman_Task('foo', []);
        $this->assertInstanceOf('Net_Gearman_Task', $task->attachCallback('strlen', 666));
    }

    public static function callbackProvider()
    {
        return [
            ['strlen',  Net_Gearman_Task::TASK_FAIL],
            ['intval',  Net_Gearman_Task::TASK_COMPLETE],
            ['explode', Net_Gearman_Task::TASK_STATUS],
        ];
    }

    /**
     * @dataProvider callbackProvider
     */
    public function testAttachCallback($func, $type)
    {
        $task = new Net_Gearman_Task('foo', []);
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
        $task = new Net_Gearman_Task('foo', ['foo' => 'bar']);

        $this->assertEquals(null, $task->complete('foo'));

        // Attach a callback for real
        $task->attachCallback('Net_Gearman_TaskTest_testCallBack');

        // build result and call complete again
        $json = json_decode('{"foo":"bar"}');
        $task->complete($json);

        $this->assertEquals($json, $task->result);

        $this->assertEquals(
            ['func' => 'foo', 'handle' => '', 'result' => $json],
            $GLOBALS['Net_Gearman_TaskTest']
        );

        unset($GLOBALS['Net_Gearman_TaskTest']);
    }

    /**
     * See that task has handle and server assigned.
     *
     * @group functional
     *
     * @return void
     */
    public function testTaskStatus()
    {
        $client = new Net_Gearman_Client([NET_GEARMAN_TEST_SERVER]);

        $task = new Net_Gearman_Task('Reverse', range(1, 5));
        $task->type = Net_Gearman_Task::JOB_BACKGROUND;

        $set = new Net_Gearman_Set();
        $set->addTask($task);

        $client->runSet($set);

        $this->assertNotEquals('', $task->handle);
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
    $GLOBALS['Net_Gearman_TaskTest'] = [
        'func' => $func,
        'handle' => $handle,
        'result' => $result,
    ];
}
