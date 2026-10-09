<?php

use AccessToMemory\test\TransactionTestCase;

/**
 * @covers \QubitRepository
 *
 * @internal
 */
class QubitRepositoryTest extends TransactionTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        QubitRepository::clearDiskUsageCache();
    }

    protected function tearDown(): void
    {
        // Don't leak cached sizes for rows that are about to be rolled back
        QubitRepository::clearDiskUsageCache();

        parent::tearDown();
    }

    public function testGetDiskUsageWithNoDigitalObjects()
    {
        $repository = $this->newRepository();

        $this->assertSame(0, $repository->getDiskUsage());
    }

    public function testGetDiskUsageSumsDigitalObjectsInRepository()
    {
        $repository = $this->newRepository();

        // A master and its derivatives are all stored in the repository dir
        $this->newDigitalObject($this->repositoryPath($repository->slug), 1000);
        $this->newDigitalObject($this->repositoryPath($repository->slug), 250);
        $this->newDigitalObject($this->repositoryPath($repository->slug), 34);

        $this->assertSame(1284, $repository->getDiskUsage());
    }

    public function testGetDiskUsageExcludesDigitalObjectsOutsideRepository()
    {
        $repository = $this->newRepository();
        $otherRepository = $this->newRepository();

        $this->newDigitalObject($this->repositoryPath($repository->slug), 500);

        // Another repository
        $this->newDigitalObject($this->repositoryPath($otherRepository->slug), 7000);

        // A directory whose name starts with this repository's slug
        $this->newDigitalObject($this->repositoryPath($repository->slug.'-other'), 8000);

        // Objects not linked to a repository
        $this->newDigitalObject($this->repositoryPath('null'), 9000);

        // Remote (external) digital object
        $this->newDigitalObject('https://example.com/'.$repository->slug.'/', 10000);

        $this->assertSame(500, $repository->getDiskUsage());
        $this->assertSame(7000, $otherRepository->getDiskUsage());
    }

    public function testGetDiskUsageEscapesLikeWildcardsInSlug()
    {
        $uniqueId = uniqid();
        $repository = $this->newRepository('test_repo_'.$uniqueId);
        $similarRepository = $this->newRepository('test-repo-'.$uniqueId);

        $this->newDigitalObject($this->repositoryPath($repository->slug), 40);
        $this->newDigitalObject($this->repositoryPath($similarRepository->slug), 2000);

        $this->assertSame(40, $repository->getDiskUsage());
        $this->assertSame(2000, $similarRepository->getDiskUsage());
    }

    /**
     * @dataProvider unitsProvider
     *
     * @param string $units
     * @param float  $expected
     */
    public function testGetDiskUsageInUnits($units, $expected)
    {
        $repository = $this->newRepository();
        $this->newDigitalObject($this->repositoryPath($repository->slug), 1234567890);

        // Request bytes first so the converted value is computed from the cache
        $this->assertSame(1234567890, $repository->getDiskUsage());
        $this->assertSame($expected, $repository->getDiskUsage(['units' => $units]));
    }

    public function unitsProvider(): array
    {
        return [
            ['k', 1234567.89],
            ['M', 1234.57],
            ['G', 1.23],
            ['g', 1.23],
        ];
    }

    public function testGetDiskUsageIsCachedUntilDigitalObjectSaved()
    {
        $repository = $this->newRepository();
        $digitalObject = $this->newDigitalObject($this->repositoryPath($repository->slug), 100);

        $this->assertSame(100, $repository->getDiskUsage());

        // Change the size directly in the database, bypassing cache invalidation
        QubitPdo::modify(
            'UPDATE '.QubitDigitalObject::TABLE_NAME.' SET byte_size = ? WHERE id = ?',
            [300, $digitalObject->id]
        );

        $this->assertSame(100, $repository->getDiskUsage());

        // Saving a digital object invalidates the cache
        $this->newDigitalObject($this->repositoryPath($repository->slug), 50);

        $this->assertSame(350, $repository->getDiskUsage());
    }

    protected function newRepository(?string $slug = null): QubitRepository
    {
        $repository = new QubitRepository();
        $repository->indexOnSave = false;
        $repositoryId = rand(1000000, 9999999);
        $repository->setAuthorizedFormOfName('Test repository '.$repositoryId);

        if (null !== $slug) {
            $repository->slug = $slug;
        } else {
            $repository->slug = 'test-repository-'.$repositoryId;
        }

        $repository->save();

        return $repository;
    }

    /**
     * Create a digital object row without writing a file to disk.
     *
     * @param string $path     directory the file would be stored in
     * @param int    $byteSize size of the file
     */
    protected function newDigitalObject(string $path, int $byteSize): QubitDigitalObject
    {
        $object = new QubitObject();
        $object->save();

        $digitalObject = new QubitDigitalObject();
        $digitalObject->object = $object;
        $digitalObject->usageId = QubitTerm::MASTER_ID;
        $digitalObject->indexOnSave = false;
        $digitalObject->name = 'file.jpg';
        $digitalObject->path = $path;
        $digitalObject->byteSize = $byteSize;
        $digitalObject->save();

        return $digitalObject;
    }

    protected function repositoryPath(string $repoDir): string
    {
        $uploadDir = trim(sfConfig::get('app_upload_dir', 'uploads'), '/');

        return sprintf('/%s/r/%s/%s/', $uploadDir, $repoDir, hash('sha256', uniqid()));
    }
}
