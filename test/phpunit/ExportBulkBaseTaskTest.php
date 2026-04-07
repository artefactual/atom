<?php

use PHPUnit\Framework\TestCase;

// Expose the protected export progress helpers and replace the STDERR write
// with an in-memory record so tests can assert whether an update would emit.
class ExportBulkBaseTaskTestDouble extends exportBulkBaseTask
{
    public $progressUpdates = [];

    public function execute($arguments = [], $options = []) {}

    public function setItemsUntilUpdateOptionPublic(array $options): void
    {
        $this->setItemsUntilUpdateOption($options);
    }

    public function getItemsUntilUpdatePublic()
    {
        return $this->itemsUntilUpdate;
    }

    public function indicateProgressPublic(int $processedCount = 0): void
    {
        $this->indicateProgress($processedCount);
    }

    protected function configure() {}

    // Record attempted progress emissions instead of writing to STDERR.
    protected function writeProgressUpdate(int $processedCount, float $startTime): void
    {
        $this->progressUpdates[] = [
            'processedCount' => $processedCount,
            'startTime' => $startTime,
        ];
    }
}

/**
 * @internal
 *
 * @covers \exportBulkBaseTask
 */
class ExportBulkBaseTaskTest extends TestCase
{
    public function testSetItemsUntilUpdateOptionDefaultsToNull(): void
    {
        $task = $this->createTask();

        $task->setItemsUntilUpdateOptionPublic([
            'items-until-update' => null,
        ]);

        $this->assertNull($task->getItemsUntilUpdatePublic());
    }

    public function testSetItemsUntilUpdateOptionZeroSuppressesProgress(): void
    {
        $task = $this->createTask();

        $task->setItemsUntilUpdateOptionPublic([
            'items-until-update' => 0,
        ]);

        // A value of 0 disables periodic progress entirely.
        $task->indicateProgressPublic(1);

        $this->assertSame(0, count($task->progressUpdates));
    }

    public function testSetItemsUntilUpdateOptionEmitsOnlyOnMatchingIntervals(): void
    {
        $task = $this->createTask();

        $task->setItemsUntilUpdateOptionPublic([
            'items-until-update' => 3,
        ]);

        // Only the third call should reach the overridden writeProgressUpdate().
        $task->indicateProgressPublic(1);
        $task->indicateProgressPublic(2);
        $task->indicateProgressPublic(3);

        $this->assertCount(1, $task->progressUpdates);
        $this->assertSame(3, $task->progressUpdates[0]['processedCount']);
    }

    /**
     * @dataProvider invalidItemsUntilUpdateProvider
     *
     * @param mixed $value
     */
    public function testSetItemsUntilUpdateOptionRejectsInvalidValues($value): void
    {
        $task = $this->createTask();

        $this->expectException(sfException::class);

        $task->setItemsUntilUpdateOptionPublic([
            'items-until-update' => $value,
        ]);
    }

    public function invalidItemsUntilUpdateProvider(): array
    {
        return [
            ['abc'],
            [-1],
            ['1.5'],
        ];
    }

    private function createTask(): ExportBulkBaseTaskTestDouble
    {
        // The task double still needs real Symfony task dependencies to satisfy
        // the sfBaseTask constructor.
        return new ExportBulkBaseTaskTestDouble(
            new sfEventDispatcher(),
            new sfFormatter()
        );
    }
}
