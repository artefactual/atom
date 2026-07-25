<?php

/*
 * This file is part of the Access to Memory (AtoM) software.
 *
 * AtoM is free software: you can redistribute it and/or modify it under the
 * terms of the GNU Affero General Public License as published by the Free
 * Software Foundation, either version 3 of the License, or (at your option)
 * any later version.
 */

declare(strict_types=1);

namespace Atom\Tests\Framework\Console;

use Atom\Framework\Bridge\EventDispatcher;
use Atom\Framework\Bridge\RuntimeConfiguration;
use Atom\Framework\Console\CacheClearTask;
use Atom\Framework\Console\Formatter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @internal
 */
final class CacheClearTaskTest extends TestCase
{
    private string $projectDirectory;

    protected function setUp(): void
    {
        $this->projectDirectory = sys_get_temp_dir()
            .'/atom-cache-clear-'.bin2hex(random_bytes(8));
        mkdir(
            $this->projectDirectory.'/cache/qubit/prod/config',
            0777,
            true,
        );
        file_put_contents(
            $this->projectDirectory.'/cache/qubit/prod/config/routes.php',
            'cached',
        );
        mkdir(
            $this->projectDirectory.'/cache/qubit/prod/template',
            0777,
            true,
        );
        file_put_contents(
            $this->projectDirectory.'/cache/qubit/prod/template/view.php',
            'cached',
        );
        mkdir(
            $this->projectDirectory.'/cache/qubit/prod/symfony',
            0777,
            true,
        );
        file_put_contents(
            $this->projectDirectory
                .'/cache/qubit/prod/symfony/container.php',
            'active',
        );
        mkdir(
            $this->projectDirectory.'/cache/sessions/prod',
            0777,
            true,
        );
        file_put_contents(
            $this->projectDirectory.'/cache/sessions/prod/session',
            'authenticated',
        );
    }

    protected function tearDown(): void
    {
        RuntimeConfiguration::setActive(null);
        (new Filesystem())->remove($this->projectDirectory);
    }

    public function testClearsOnlyTheSelectedCacheType(): void
    {
        $configuration = new RuntimeConfiguration(
            'qubit',
            'prod',
            [],
            false,
            $this->projectDirectory,
        );
        RuntimeConfiguration::setActive($configuration);
        $task = new CacheClearTask(
            new EventDispatcher(),
            new Formatter(),
        );

        self::assertSame(0, $task->run([], [
            'app' => 'qubit',
            'env' => 'prod',
            'type' => 'config',
        ]));
        self::assertFileDoesNotExist(
            $this->projectDirectory
                .'/cache/qubit/prod/config/routes.php',
        );
        self::assertFileExists(
            $this->projectDirectory
                .'/cache/qubit/prod/template/view.php',
        );
    }

    public function testPreservesSessionStorageWhenClearingEverything(): void
    {
        $configuration = new RuntimeConfiguration(
            'qubit',
            'prod',
            [],
            false,
            $this->projectDirectory,
        );
        RuntimeConfiguration::setActive($configuration);
        $task = new CacheClearTask(
            new EventDispatcher(),
            new Formatter(),
        );

        self::assertSame(0, $task->run());
        self::assertFileDoesNotExist(
            $this->projectDirectory
                .'/cache/qubit/prod/config/routes.php',
        );
        self::assertFileExists(
            $this->projectDirectory
                .'/cache/sessions/prod/session',
        );
        self::assertFileExists(
            $this->projectDirectory
                .'/cache/qubit/prod/symfony/container.php',
        );
    }
}
