<?php

use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @covers \QubitInformationObject::getByTitleIdentifierAndRepo
 */
class QubitInformationObjectTest extends TestCase
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
}
