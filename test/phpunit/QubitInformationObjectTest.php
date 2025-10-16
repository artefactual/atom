<?php

use AccessToMemory\test\TransactionTestCase;

/**
 * @internal
 *
 * @covers \QubitInformationObject::getByTitleIdentifierAndRepo
 */
class QubitInformationObjectTest extends TransactionTestCase
{
    protected $io;
    protected $repo;

    /**
     * @dataProvider dataProviderForGetByTitleIdentifierAndRepo
     *
     * @param string      $identifier    the information object identifier
     * @param string      $title         the information object title
     * @param null|string $repoName      the repository authorized_form_of_name
     * @param mixed       $hasLinkedRepo boolean indicating if the information object should have a linked repository
     * @param null|string $expectedTitle expected localized title if a match is found; otherwise, null
     */
    public function testGetByTitleIdentifierAndRepo($identifier, $title, $repoName, $hasLinkedRepo, $expectedTitle)
    {
        $randomString = rand(1000000, 9999999);

        $io = new QubitInformationObject();
        $io->title = 'TestDescriptionTitle'.$randomString;
        $io->identifier = 'TestDescriptionIdentifier'.$randomString;

        // Set up a linked repository if needed for the test.
        if (true === $hasLinkedRepo) {
            $repository = new QubitRepository();
            $repository->indexOnSave = false;
            $repository->setAuthorizedFormOfName('TestRepository'.$randomString);
            $repository->save();
            $io->setRepositoryId($repository->id);
        }

        $io->indexOnSave = false;
        $io->save();

        if (null !== $repoName) {
            $repoName .= $randomString;
        }

        $result = QubitInformationObject::getByTitleIdentifierAndRepo(
            $identifier.$randomString,
            $title.$randomString,
            $repoName
        );

        if (null === $expectedTitle) {
            // No match expected.
            $this->assertNull($result, 'Expected null result when no matching record exists.');
        } else {
            // A match is expected.
            $this->assertNotNull($result, 'Expected a valid integer id when data should match.');
            $this->assertIsInt($result, 'Expected the returned id to be an integer.');

            $resultIo = QubitInformationObject::getById($result);
            $this->assertNotNull($resultIo, 'Expected a valid information object.');
            $this->assertEquals($expectedTitle.$randomString, $resultIo->title, 'The information object title does not match expected.');
        }
    }

    public function dataProviderForGetByTitleIdentifierAndRepo()
    {
        // Order of fields: $identifier, $title, $repoName, $hasLinkedRepo, $expectedTitle
        return [
            // Id, title and repository specified but repo not linked: matching fail.
            ['TestDescriptionIdentifier', 'TestDescriptionTitle', 'TestRepository', false, null],
            // Id, title specified only and repo is linked: matching success.
            ['TestDescriptionIdentifier', 'TestDescriptionTitle', null, false, 'TestDescriptionTitle'],
            // Id, title and repository specified but title not matched and repo missing: matching fail.
            ['TestDescriptionIdentifier', 'TestDescriptionTitleX', 'TestRepository', false, null],
            // Id, title and repository specified but id not matched and repo missing: matching fail.
            ['TestDescriptionIdentifierX', 'TestDescriptionTitle', 'TestRepository', false, null],
            // Id, title and repository specified in lookup & all exist: matched.
            ['TestDescriptionIdentifier', 'TestDescriptionTitle', 'TestRepository', true, 'TestDescriptionTitle'],
            // Id, title specified only & repo not linked: matching success.
            ['TestDescriptionIdentifier', 'TestDescriptionTitle', null, true, 'TestDescriptionTitle'],
            // Id, title and repository specified but repo not matched: matching fail.
            ['TestDescriptionIdentifier', 'TestDescriptionTitle', 'TestRepositoryX', true, null],
            // Id, title and repository specified but title not matched: matching fail.
            ['TestDescriptionIdentifier', 'TestDescriptionTitleX', 'TestRepository', true, null],
            // Id, title and repository specified but id not matched: matching fail.
            ['TestDescriptionIdentifierX', 'TestDescriptionTitle', 'TestRepository', true, null],
        ];
    }

    /**
     * @dataProvider dataProviderForGetByTitleIdentifierAndRepoInherit
     *
     * Tests that getByTitleIdentifierAndRepo works when the repository is inherited (i.e., when
     * the information object with the matching title + identifier has NULL for the repository ID).
     *
     * @param string $identifier    the child information object identifier
     * @param string $title         the child information object title
     * @param string $repoName      the repository authorized_form_of_name
     * @param bool   $parentHasRepo whether the parent should have a linked repository
     * @param string $expectedTitle expected localized title if a match is found; otherwise, null
     */
    public function testGetByTitleIdentifierAndRepoInherit($identifier, $title, $repoName, $parentHasRepo, $expectedTitle)
    {
        $randomString = rand(1000000, 9999999);

        $parent = new QubitInformationObject();
        $parent->title = 'TestParentTitle'.$randomString;
        $parent->identifier = 'TestParentIdentifier'.$randomString;

        if (true === $parentHasRepo) {
            $repository = new QubitRepository();
            $repository->indexOnSave = false;
            $repository->setAuthorizedFormOfName('TestRepository'.$randomString);
            $repository->save();
            $parent->setRepositoryId($repository->id);
        }

        $parent->indexOnSave = false;
        $parent->save();

        $child = new QubitInformationObject();
        $child->title = 'TestChildTitle'.$randomString;
        $child->identifier = 'TestChildIdentifier'.$randomString;
        $child->parentId = $parent->id;
        $child->indexOnSave = false;
        $child->save();

        if (null !== $repoName) {
            $repoName .= $randomString;
        }

        $result = QubitInformationObject::getByTitleIdentifierAndRepo(
            $identifier.$randomString,
            $title.$randomString,
            $repoName
        );

        if (null === $expectedTitle) {
            $this->assertNull($result, 'Expected null result when no matching record exists.');
        } else {
            $this->assertNotNull($result, 'Expected a valid integer id when data should match.');
            $this->assertIsInt($result, 'Expected the returned id to be an integer.');

            $resultIo = QubitInformationObject::getById($result);
            $this->assertNotNull($resultIo, 'Expected a valid information object.');
            $this->assertEquals($expectedTitle.$randomString, $resultIo->title, 'The information object title does not match expected.');
        }
    }

    public function dataProviderForGetByTitleIdentifierAndRepoInherit()
    {
        // Order of fields: $identifier, $title, $repoName, $parentHasRepo, $expectedTitle
        return [
            // Child matches id and title, parent has matching repo: matching success.
            ['TestChildIdentifier', 'TestChildTitle', 'TestRepository', true, 'TestChildTitle'],
            // Child matches id and title, parent has repo but repo name doesn't match: matching fail.
            ['TestChildIdentifier', 'TestChildTitle', 'TestRepositoryX', true, null],
            // Child matches id and title, parent has no repo but repo name specified: matching fail.
            ['TestChildIdentifier', 'TestChildTitle', 'TestRepository', false, null],
            // Child matches id and title, parent has repo but no repo name specified: matching success.
            ['TestChildIdentifier', 'TestChildTitle', null, true, 'TestChildTitle'],
            // Child matches id and title, parent has no repo and no repo name specified: matching success.
            ['TestChildIdentifier', 'TestChildTitle', null, false, 'TestChildTitle'],
            // Child id doesn't match, parent has matching repo: matching fail.
            ['TestChildIdentifierX', 'TestChildTitle', 'TestRepository', true, null],
            // Child title doesn't match, parent has matching repo: matching fail.
            ['TestChildIdentifier', 'TestChildTitleX', 'TestRepository', true, null],
        ];
    }

    /**
     * Test that repository is inherited from a grandparent when immediate parent has no repository.
     * Creates a three-level hierarchy: grandparent (with repo) -> parent (no repo) -> child (no repo).
     * Verifies child can be found when searching with grandparent's repository.
     */
    public function testGetByTitleIdentifierAndRepoWithMultiLevelInheritance()
    {
        $randomString = rand(1000000, 9999999);

        $repository = new QubitRepository();
        $repository->indexOnSave = false;
        $repository->setAuthorizedFormOfName('TestRepository'.$randomString);
        $repository->save();

        // Has repository
        $grandparent = new QubitInformationObject();
        $grandparent->title = 'TestGrandparentTitle'.$randomString;
        $grandparent->identifier = 'TestGrandparentIdentifier'.$randomString;
        $grandparent->setRepositoryId($repository->id);
        $grandparent->indexOnSave = false;
        $grandparent->save();

        // No repository
        $parent = new QubitInformationObject();
        $parent->title = 'TestParentTitle'.$randomString;
        $parent->identifier = 'TestParentIdentifier'.$randomString;
        $parent->parentId = $grandparent->id;
        $parent->indexOnSave = false;
        $parent->save();

        // No repository
        $child = new QubitInformationObject();
        $child->title = 'TestChildTitle'.$randomString;
        $child->identifier = 'TestChildIdentifier'.$randomString;
        $child->parentId = $parent->id;
        $child->indexOnSave = false;
        $child->save();

        $result = QubitInformationObject::getByTitleIdentifierAndRepo(
            'TestChildIdentifier'.$randomString,
            'TestChildTitle'.$randomString,
            'TestRepository'.$randomString
        );

        $this->assertNotNull($result, 'Expected a valid integer id when repository is inherited from grandparent.');
        $this->assertIsInt($result, 'Expected the returned id to be an integer.');

        $resultIo = QubitInformationObject::getById($result);
        $this->assertNotNull($resultIo, 'Expected a valid information object.');
        $this->assertEquals('TestChildTitle'.$randomString, $resultIo->title, 'The information object title does not match expected.');
    }

    /**
     * Test that nearest parent repository takes precedence over more distant ancestor repositories.
     * Creates a three-level hierarchy with different repositories: grandparent (repo A) -> parent (repo B) -> child (no repo).
     * Verifies child matches with parent's repo B but not grandparent's repo A.
     */
    public function testGetByTitleIdentifierAndRepoWithNearestParentRepository()
    {
        $randomString = rand(1000000, 9999999);

        $grandparentRepo = new QubitRepository();
        $grandparentRepo->indexOnSave = false;
        $grandparentRepo->setAuthorizedFormOfName('TestGrandparentRepository'.$randomString);
        $grandparentRepo->save();

        $parentRepo = new QubitRepository();
        $parentRepo->indexOnSave = false;
        $parentRepo->setAuthorizedFormOfName('TestParentRepository'.$randomString);
        $parentRepo->save();

        // Grandparent has a repository...
        $grandparent = new QubitInformationObject();
        $grandparent->title = 'TestGrandparentTitle'.$randomString;
        $grandparent->identifier = 'TestGrandparentIdentifier'.$randomString;
        $grandparent->setRepositoryId($grandparentRepo->id);
        $grandparent->indexOnSave = false;
        $grandparent->save();

        // ...and so does the parent. We want to check we're matching on the parent's
        $parent = new QubitInformationObject();
        $parent->title = 'TestParentTitle'.$randomString;
        $parent->identifier = 'TestParentIdentifier'.$randomString;
        $parent->parentId = $grandparent->id;
        $parent->setRepositoryId($parentRepo->id);
        $parent->indexOnSave = false;
        $parent->save();

        $child = new QubitInformationObject();
        $child->title = 'TestChildTitle'.$randomString;
        $child->identifier = 'TestChildIdentifier'.$randomString;
        $child->parentId = $parent->id;
        $child->indexOnSave = false;
        $child->save();

        $result = QubitInformationObject::getByTitleIdentifierAndRepo(
            'TestChildIdentifier'.$randomString,
            'TestChildTitle'.$randomString,
            'TestParentRepository'.$randomString
        );

        $this->assertNotNull($result, 'Expected a valid integer id when matching nearest parent repository.');
        $this->assertIsInt($result, 'Expected the returned id to be an integer.');

        $resultIo = QubitInformationObject::getById($result);
        $this->assertNotNull($resultIo, 'Expected a valid information object.');
        $this->assertEquals('TestChildTitle'.$randomString, $resultIo->title, 'The information object title does not match expected.');

        $resultWithWrongRepo = QubitInformationObject::getByTitleIdentifierAndRepo(
            'TestChildIdentifier'.$randomString,
            'TestChildTitle'.$randomString,
            'TestGrandparentRepository'.$randomString
        );

        $this->assertNull($resultWithWrongRepo, 'Expected null when searching with grandparent repository since nearest parent has different repository.');
    }
}
