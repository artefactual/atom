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
            $this->assertIsArray($result);
            $this->assertCount(0, $result, 'Expected empty result when no matching record exists.');
        } else {
            // A match is expected.
            $this->assertIsArray($result);
            $this->assertCount(1, $result, 'Expected exactly one matching id.');
            $this->assertIsInt($result[0], 'Expected the returned id to be an integer.');

            $resultIo = QubitInformationObject::getById($result[0]);
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
            $this->assertIsArray($result, 'Expected array result when no matching record exists.');
            $this->assertCount(0, $result, 'Expected empty result when no matching record exists.');
        } else {
            $this->assertIsArray($result, 'Expected array result when data should match.');
            $this->assertCount(1, $result, 'Expected exactly one matching id when data should match.');
            $this->assertIsInt($result[0], 'Expected the returned id to be an integer.');

            $resultIo = QubitInformationObject::getById($result[0]);
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
        $this->assertIsArray($result, 'Expected an array of ids when repository is inherited from grandparent.');
        $this->assertCount(1, $result, 'Expected exactly one matching id.');
        $this->assertIsInt($result[0], 'Expected the returned id to be an integer.');

        $resultIo = QubitInformationObject::getById($result[0]);
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

        $this->assertIsArray($result, 'Expected an array of ids.');
        $this->assertCount(1, $result, 'Expected exactly one matching id when matching nearest parent repository.');
        $this->assertIsInt($result[0], 'Expected the returned id to be an integer.');

        $resultIo = QubitInformationObject::getById($result[0]);
        $this->assertNotNull($resultIo, 'Expected a valid information object.');
        $this->assertEquals('TestChildTitle'.$randomString, $resultIo->title, 'The information object title does not match expected.');

        $resultWithWrongRepo = QubitInformationObject::getByTitleIdentifierAndRepo(
            'TestChildIdentifier'.$randomString,
            'TestChildTitle'.$randomString,
            'TestGrandparentRepository'.$randomString
        );

        $this->assertIsArray($resultWithWrongRepo, 'Expected an array of ids.');
        $this->assertCount(0, $resultWithWrongRepo, 'Expected empty result when searching with grandparent repository since nearest parent has different repository.');
    }

    /**
     * Ensure matching works when the title exists only in the source/default culture
     * and the current/default culture has no translation. Option A should be
     * culture-agnostic for title matching.
     */
    public function testGetByTitleIdentifierAndRepoMatchesWhenTitleOnlyInSourceCulture()
    {
        $random = rand(1000000, 9999999);

        // Remember current culture and ensure we write English-only data first.
        $prevCulture = sfPropel::getDefaultCulture();
        sfPropel::setDefaultCulture('en');
        if (sfContext::hasInstance()) {
            sfContext::getInstance()->getUser()->setCulture('en');
        }

        $io = new QubitInformationObject();
        $io->indexOnSave = false;
        $io->title = 'TitleEn'.$random; // only in English
        $io->identifier = 'Identifier'.$random;
        $io->save();

        // Switch to a different culture without adding translations
        sfPropel::setDefaultCulture('fr');
        if (sfContext::hasInstance()) {
            sfContext::getInstance()->getUser()->setCulture('fr');
        }

        $result = QubitInformationObject::getByTitleIdentifierAndRepo(
            'Identifier'.$random,
            'TitleEn'.$random,
            null
        );

        $this->assertIsArray($result, 'Expected an array of ids.');
        $this->assertCount(1, $result, 'Expected a single match even when current culture lacks a title translation.');
        $this->assertIsInt($result[0]);

        // Restore culture
        sfPropel::setDefaultCulture($prevCulture);
        if (sfContext::hasInstance()) {
            sfContext::getInstance()->getUser()->setCulture($prevCulture);
        }
    }

    /**
     * Ensure matching works when the repository name exists only in the source/default culture
     * and the current/default culture has no translation. Option A should be
     * culture-agnostic for repository name matching.
     */
    public function testGetByTitleIdentifierAndRepoMatchesWhenRepoNameOnlyInSourceCulture()
    {
        $random = rand(1000000, 9999999);

        // Remember current culture
        $prevCulture = sfPropel::getDefaultCulture();

        // Create repository with English-only name
        sfPropel::setDefaultCulture('en');
        if (sfContext::hasInstance()) {
            sfContext::getInstance()->getUser()->setCulture('en');
        }

        $repository = new QubitRepository();
        $repository->indexOnSave = false;
        $repository->setAuthorizedFormOfName('RepoEn'.$random);
        $repository->save();

        // Create IO linked to repository; title only in English
        $io = new QubitInformationObject();
        $io->indexOnSave = false;
        $io->title = 'TitleEn'.$random;
        $io->identifier = 'Identifier'.$random;
        $io->setRepositoryId($repository->id);
        $io->save();

        // Switch to a different culture without adding translations
        sfPropel::setDefaultCulture('fr');
        if (sfContext::hasInstance()) {
            sfContext::getInstance()->getUser()->setCulture('fr');
        }

        $result = QubitInformationObject::getByTitleIdentifierAndRepo(
            'Identifier'.$random,
            'TitleEn'.$random,
            'RepoEn'.$random
        );

        $this->assertIsArray($result, 'Expected an array of ids.');
        $this->assertCount(1, $result, 'Expected a single match even when current culture lacks a repository name translation.');
        $this->assertIsInt($result[0]);

        // Restore culture
        sfPropel::setDefaultCulture($prevCulture);
        if (sfContext::hasInstance()) {
            sfContext::getInstance()->getUser()->setCulture($prevCulture);
        }
    }

    /**
     * Ensure inherited repository matching works when the repo name exists only in source culture.
     */
    public function testGetByTitleIdentifierAndRepoInheritedRepoNameOnlyInSourceCulture()
    {
        $random = rand(1000000, 9999999);

        $prevCulture = sfPropel::getDefaultCulture();

        // English-only repository name on parent
        sfPropel::setDefaultCulture('en');
        if (sfContext::hasInstance()) {
            sfContext::getInstance()->getUser()->setCulture('en');
        }

        $repo = new QubitRepository();
        $repo->indexOnSave = false;
        $repo->setAuthorizedFormOfName('RepoEn'.$random);
        $repo->save();

        $parent = new QubitInformationObject();
        $parent->indexOnSave = false;
        $parent->title = 'ParentTitleEn'.$random;
        $parent->identifier = 'ParentIdentifier'.$random;
        $parent->setRepositoryId($repo->id);
        $parent->save();

        $child = new QubitInformationObject();
        $child->indexOnSave = false;
        $child->title = 'ChildTitleEn'.$random;
        $child->identifier = 'ChildIdentifier'.$random;
        $child->parentId = $parent->id; // inherits repo
        $child->save();

        // Switch to different culture without adding translations
        sfPropel::setDefaultCulture('fr');
        if (sfContext::hasInstance()) {
            sfContext::getInstance()->getUser()->setCulture('fr');
        }

        $result = QubitInformationObject::getByTitleIdentifierAndRepo(
            'ChildIdentifier'.$random,
            'ChildTitleEn'.$random,
            'RepoEn'.$random
        );

        $this->assertIsArray($result, 'Expected an array of ids.');
        $this->assertCount(1, $result, 'Expected a single match when repo is inherited and repo name only exists in source culture.');
        $this->assertIsInt($result[0]);

        // Restore culture
        sfPropel::setDefaultCulture($prevCulture);
        if (sfContext::hasInstance()) {
            sfContext::getInstance()->getUser()->setCulture($prevCulture);
        }
    }

    /**
     * When no repository is supplied and multiple information objects share
     * the same identifier and title, expect multiple results in ascending id order.
     */
    public function testGetByTitleIdentifierAndRepoReturnsMultipleWithoutRepo()
    {
        $random = rand(1000000, 9999999);

        $title = 'MultiTitle'.$random;
        $identifier = 'MultiIdentifier'.$random;

        $io1 = new QubitInformationObject();
        $io1->indexOnSave = false;
        $io1->title = $title;
        $io1->identifier = $identifier;
        $io1->save();

        $io2 = new QubitInformationObject();
        $io2->indexOnSave = false;
        $io2->title = $title;
        $io2->identifier = $identifier;
        $io2->save();

        $result = QubitInformationObject::getByTitleIdentifierAndRepo($identifier, $title, null);

        $this->assertIsArray($result);
        $this->assertCount(2, $result, 'Expected two matching ids for duplicate identifier+title.');

        $expected = [min((int) $io1->id, (int) $io2->id), max((int) $io1->id, (int) $io2->id)];
        $this->assertSame($expected, $result, 'Expected results ordered ascending by id.');
    }

    /**
     * When a repository is supplied and multiple information objects in that repository
     * share the same identifier and title, expect multiple results in ascending id order.
     */
    public function testGetByTitleIdentifierAndRepoReturnsMultipleWithRepo()
    {
        $random = rand(1000000, 9999999);

        $title = 'MultiTitle'.$random;
        $identifier = 'MultiIdentifier'.$random;
        $repoName = 'MultiRepo'.$random;

        $repo = new QubitRepository();
        $repo->indexOnSave = false;
        $repo->setAuthorizedFormOfName($repoName);
        $repo->save();

        $io1 = new QubitInformationObject();
        $io1->indexOnSave = false;
        $io1->title = $title;
        $io1->identifier = $identifier;
        $io1->setRepositoryId($repo->id);
        $io1->save();

        $io2 = new QubitInformationObject();
        $io2->indexOnSave = false;
        $io2->title = $title;
        $io2->identifier = $identifier;
        $io2->setRepositoryId($repo->id);
        $io2->save();

        // Add an IO with same identifier+title but different repo to ensure it is excluded
        $otherRepo = new QubitRepository();
        $otherRepo->indexOnSave = false;
        $otherRepo->setAuthorizedFormOfName('MultiRepoOther'.$random);
        $otherRepo->save();

        $io3 = new QubitInformationObject();
        $io3->indexOnSave = false;
        $io3->title = $title;
        $io3->identifier = $identifier;
        $io3->setRepositoryId($otherRepo->id);
        $io3->save();

        $result = QubitInformationObject::getByTitleIdentifierAndRepo($identifier, $title, $repoName);

        $this->assertIsArray($result);
        $this->assertCount(2, $result, 'Expected two matching ids for duplicate identifier+title within the same repository.');

        $expected = [min((int) $io1->id, (int) $io2->id), max((int) $io1->id, (int) $io2->id)];
        $this->assertSame($expected, $result, 'Expected results ordered ascending by id and excluding other repository.');
    }
}
