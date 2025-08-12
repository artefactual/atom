<?php

namespace AccessToMemory\test;

use PHPUnit\Framework\TestCase;

/**
 * Custom test case class for tests that modify the database. Runs all tests in a
 * separate transaction.
 *
 * @internal
 *
 * @coversNothing
 */
class TransactionTestCase extends TestCase
{
    protected $connection;

    /**
     * Start a transaction before the test starts.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->connection = \Propel::getConnection();
        $this->connection->beginTransaction();
    }

    /**
     * Rollback once the test completes.
     */
    protected function tearDown(): void
    {
        if ($this->connection?->inTransaction()) {
            $this->connection->rollback();
        }

        parent::tearDown();
    }
}
