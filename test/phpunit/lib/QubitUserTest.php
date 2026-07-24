<?php

use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @covers \QubitUser
 */
class QubitUserTest extends TestCase
{
    public function testSetPasswordGeneratesHexSalt(): void
    {
        $user = new QubitUser();
        $user->email = 'user@example.com';

        $user->setPassword('password');

        $this->assertMatchesRegularExpression('/^[0-9a-f]{32}$/', $user->getSalt());
    }

    public function testSetPasswordGeneratesUniqueSalts(): void
    {
        $first = new QubitUser();
        $first->email = 'user@example.com';
        $first->setPassword('password');

        $second = new QubitUser();
        $second->email = 'user@example.com';
        $second->setPassword('password');

        $this->assertNotSame($first->getSalt(), $second->getSalt());
    }

    public function testSetPasswordMaintainsPasswordVerificationContract(): void
    {
        $password = 'password';
        $user = new QubitUser();
        $user->email = 'user@example.com';

        $user->setPassword($password);

        $this->assertTrue(password_verify(
            sha1($user->getSalt().$password),
            $user->getPasswordHash()
        ));
    }
}
