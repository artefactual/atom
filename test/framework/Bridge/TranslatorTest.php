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

namespace Atom\Tests\Framework\Bridge;

use Atom\Framework\Bridge\TranslatorFactory;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class TranslatorTest extends TestCase
{
    public function testLoadsLegacyAtoMXliffCatalogue(): void
    {
        $projectDirectory = dirname(__DIR__, 3);
        $translator = (new TranslatorFactory(
            $projectDirectory,
            'qubit',
            ['qbAclPlugin'],
        ))->create('fr');

        self::assertSame('Accueil', $translator->__('Home'));
        self::assertSame('Fermer', $translator->__('Close'));
        self::assertSame(
            'Imprimé : 2026-07-24',
            $translator->__('Printed: %d%', [
                '%d%' => '2026-07-24',
            ]),
        );
        self::assertSame(
            'Skip to main content',
            $translator->__('Skip to main content'),
        );
    }

    public function testFallsBackToSourceInDefaultCulture(): void
    {
        $projectDirectory = dirname(__DIR__, 3);
        $translator = (new TranslatorFactory(
            $projectDirectory,
            'qubit',
            [],
        ))->create('en');

        self::assertSame('Home', $translator->__('Home'));
    }
}
