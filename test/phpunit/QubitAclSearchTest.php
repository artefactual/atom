<?php

use AccessToMemory\test\TransactionTestCase;

/**
 * @internal
 *
 * @covers \QubitAclSearch
 */
class QubitAclSearchTest extends TransactionTestCase
{
    public function testFiltersDraftsWithRepositorySpecificAccess()
    {
        $user = new QubitUser();
        $user->username = 'acl-search-'.rand(1000000, 9999999);
        $user->email = $user->username.'@example.com';
        $user->setPassword('password');
        $user->active = true;
        $user->save();

        $repository = new QubitRepository();
        $repository->indexOnSave = false;
        $repository->setAuthorizedFormOfName(
            'ACL search repository '.rand(1000000, 9999999)
        );
        $repository->save();

        $globalPermission = new QubitAclPermission();
        $globalPermission->userId = $user->id;
        $globalPermission->objectId = QubitInformationObject::ROOT_ID;
        $globalPermission->action = 'viewDraft';
        $globalPermission->grantDeny = 0;
        $globalPermission->save();

        $repositoryPermission = new QubitAclPermission();
        $repositoryPermission->userId = $user->id;
        $repositoryPermission->objectId = QubitInformationObject::ROOT_ID;
        $repositoryPermission->action = 'viewDraft';
        $repositoryPermission->grantDeny = 1;
        $repositoryPermission->setRepository($repository);
        $repositoryPermission->save();

        $contextUser = sfContext::getInstance()->getUser();
        $contextUser->signIn($user);
        QubitAcl::destruct();

        try {
            $query = new Elastica\Query\BoolQuery();
            QubitAclSearch::filterDrafts($query);
            $filters = $query->toArray()['bool']['must'];

            $this->assertCount(2, $filters);
            $this->assertSame(
                (int) $repository->id,
                $filters[0]['bool']['should'][0]['term']['repository.id']
            );
            $this->assertSame(
                QubitTerm::PUBLICATION_STATUS_PUBLISHED_ID,
                $filters[0]['bool']['should'][1]['term']['publicationStatusId']
            );
        } finally {
            $contextUser->signOut();
            QubitAcl::destruct();
        }
    }
}
