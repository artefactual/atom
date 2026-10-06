<?php

use AccessToMemory\test\TransactionTestCase;

/**
 * @internal
 *
 * @covers \BaseObject::__get
 * @covers \QubitNote::save
 */
class QubitModelSafeguardsTest extends TransactionTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        QubitSearch::disable();
    }

    protected function tearDown(): void
    {
        QubitSearch::enable();
        QubitObject::clearCache();

        parent::tearDown();
    }

    public function testDeletedPhysicalStorageRelationToleratesStaleLazyRow()
    {
        $resource = $this->createInformationObject();

        $storage = new QubitPhysicalObject();
        $storage->indexOnSave = false;
        $storage->sourceCulture = 'en';
        $storage->typeId = QubitTerm::CONTAINER_ID;
        $storage->name = 'Box 1';
        $storage->save($this->connection);

        $resource->addPhysicalObject($storage);
        $relations = $resource->relationsRelatedByobjectId->transient;
        $this->assertCount(1, $relations);
        $relations[0]->slug = 'physical-storage-link-'.uniqid();

        $resource->save($this->connection);

        $relationId = $relations[0]->id;

        // Reload through the object table so relation columns remain lazy.
        QubitObject::clearCache();
        $relation = QubitObject::getById($relationId);
        $this->assertInstanceOf(QubitRelation::class, $relation);
        // Production does not promote the first missing-row warning to an
        // exception. The second lazy lookup is the PHP 8 TypeError regression.
        @$relation->delete($this->connection);

        // The first lookup records a stale row as false. Further lookups must
        // not pass that value to array_key_exists(), which throws on PHP 8.
        $this->assertNull(@$relation->objectId);
        $this->assertNull(@$relation->objectId);
    }

    public function testSavingResourceIgnoresDeletedLanguageNote()
    {
        $resource = $this->createInformationObject();

        $note = new QubitNote();
        $note->indexOnSave = false;
        $note->sourceCulture = 'en';
        $note->typeId = QubitTerm::LANGUAGE_NOTE_ID;
        $note->content = 'English';
        $resource->notes[] = $note;
        $resource->save($this->connection);

        $note->delete($this->connection);

        $this->assertSame($resource, $resource->save($this->connection));

        $statement = $this->connection->prepare(
            'SELECT COUNT(*) FROM note WHERE id = ?'
        );
        $statement->execute([$note->id]);
        $this->assertSame(0, (int) $statement->fetchColumn());
    }

    private function createInformationObject()
    {
        $resource = new QubitInformationObject();
        $resource->indexOnSave = false;
        $resource->parentId = QubitInformationObject::ROOT_ID;
        $resource->sourceCulture = 'en';
        $resource->title = 'Safeguard test';
        $resource->save($this->connection);

        return $resource;
    }
}
