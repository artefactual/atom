<?php

use AccessToMemory\test\TransactionTestCase;

/**
 * @internal
 *
 * @covers \QubitContactInformationAcl
 */
class QubitContactInformationAclTest extends TransactionTestCase
{
    public function testUnauthenticatedUserIsDenied()
    {
        $user = $this->createMockUser(false);
        $resource = new QubitContactInformation();

        $this->assertFalse(
            QubitContactInformationAcl::isAllowed($user, $resource, 'read')
        );
    }

    public function testAdministratorIsAllowed()
    {
        $user = $this->createMockUser(true, [QubitAclGroup::ADMINISTRATOR_ID]);
        $resource = new QubitContactInformation();

        $this->assertTrue(
            QubitContactInformationAcl::isAllowed($user, $resource, 'update')
        );
    }

    public function testEditorIsAllowed()
    {
        $user = $this->createMockUser(true, [QubitAclGroup::EDITOR_ID]);
        $resource = new QubitContactInformation();

        $this->assertTrue(
            QubitContactInformationAcl::isAllowed($user, $resource, 'update')
        );
    }

    public function testAuthenticatedNonPrivilegedUserWithNoParentIsDenied()
    {
        $user = $this->createMockUser(true);
        $resource = new QubitContactInformation();
        $resource->actorId = 0;

        $this->assertFalse(
            QubitContactInformationAcl::isAllowed($user, $resource, 'read')
        );
    }

    public function testDelegatesToRepositoryWhenParentRepositoryExists()
    {
        $qubitUser = new QubitUser();
        $qubitUser->username = 'testuser_'.rand(1000000, 9999999);
        $qubitUser->email = 'test'.rand(1000000, 9999999).'@example.com';
        $qubitUser->setPassword('password');
        $qubitUser->active = true;
        $qubitUser->save();

        $contextUser = sfContext::getInstance()->getUser();
        $contextUser->signIn($qubitUser);

        $repository = new QubitRepository();
        $repository->indexOnSave = false;
        $repository->setAuthorizedFormOfName('TestRepo'.rand(1000000, 9999999));
        $repository->save();

        $resource = new QubitContactInformation();
        $resource->actorId = $repository->id;

        // With default ACL settings, the authenticated group has read
        // permission. Delegating to the repository should return true.
        $this->assertTrue(
            QubitContactInformationAcl::isAllowed($contextUser, $resource, 'read')
        );

        QubitAcl::destruct();
        $contextUser->signOut();
    }

    public function testDelegatesToActorWhenParentActorExists()
    {
        $qubitUser = new QubitUser();
        $qubitUser->username = 'testuser_'.rand(1000000, 9999999);
        $qubitUser->email = 'test'.rand(1000000, 9999999).'@example.com';
        $qubitUser->setPassword('password');
        $qubitUser->active = true;
        $qubitUser->save();

        $contextUser = sfContext::getInstance()->getUser();
        $contextUser->signIn($qubitUser);

        $actor = new QubitActor();
        $actor->indexOnSave = false;
        $actor->setAuthorizedFormOfName('TestDonor'.rand(1000000, 9999999));
        $actor->save();

        $resource = new QubitContactInformation();
        $resource->actorId = $actor->id;

        // With default ACL settings, the authenticated group has read
        // permission. Delegating to the actor should return true.
        $this->assertTrue(
            QubitContactInformationAcl::isAllowed($contextUser, $resource, 'read')
        );

        QubitAcl::destruct();
        $contextUser->signOut();
    }

    private function createMockUser(bool $authenticated, array $groups = [])
    {
        $user = $this->getMockBuilder(stdClass::class)
            ->addMethods(['isAuthenticated', 'hasGroup'])
            ->getMock();

        $user->method('isAuthenticated')
            ->willReturn($authenticated);

        $user->method('hasGroup')
            ->willReturnCallback(function ($groupId) use ($groups) {
                return in_array($groupId, $groups);
            });

        return $user;
    }
}
