<?php

/*
 * This file is part of the Access to Memory (AtoM) software.
 *
 * Access to Memory (AtoM) is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 */

declare(strict_types=1);

namespace Atom\Tests\Framework\Bridge;

use Atom\Framework\Bridge\User;
use Atom\Framework\Bridge\UserFactory;
use Atom\Framework\Configuration\ConfigurationException;
use Atom\Framework\Configuration\ModuleConfigurationLoader;
use Atom\Framework\Security\SecurityConfiguration;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class UserFactoryTest extends TestCase
{
    public function testCreatesConfiguredUserSubclass(): void
    {
        $security = $this->security();
        $user = (new UserFactory())->create(
            ConfiguredUser::class,
            $security,
        );

        self::assertInstanceOf(ConfiguredUser::class, $user);
    }

    public function testMapsLegacyDefaultUserToBridge(): void
    {
        $user = (new UserFactory())->create(
            'myUser',
            $this->security(),
        );

        self::assertSame(User::class, $user::class);
    }

    public function testRejectsUnrelatedConfiguredClass(): void
    {
        $this->expectException(ConfigurationException::class);
        (new UserFactory())->create(
            \stdClass::class,
            $this->security(),
        );
    }

    private function security(): SecurityConfiguration
    {
        return new SecurityConfiguration(
            new ModuleConfigurationLoader(
                sys_get_temp_dir(),
                'qubit',
                [],
            ),
        );
    }
}

final class ConfiguredUser extends User {}
