<?php

use Atom\Framework\Bridge\Context;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class SymfonyRuntimeTest extends TestCase
{
    public function testApplicationSuiteUsesOnlyBridgeClasses(): void
    {
        $projectDirectory = dirname(__DIR__, 2);

        $this->assertFalse(class_exists('sfCoreAutoload', false));
        $this->assertInstanceOf(Context::class, sfContext::getInstance());

        foreach ([
            'sfAction',
            'sfContext',
            'sfFilter',
            'sfForm',
            'sfStorage',
        ] as $class) {
            $path = (new ReflectionClass($class))->getFileName();

            $this->assertIsString($path);
            $this->assertStringStartsWith(
                $projectDirectory.'/src/',
                $path
            );
        }
    }
}
