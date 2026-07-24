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

namespace Atom\Tests\Framework\Configuration;

use Atom\Framework\Bridge\AssetRenderer;
use Atom\Framework\Bridge\Configuration;
use Atom\Framework\Bridge\ResponseAdapter;
use Atom\Framework\Configuration\ConfigurationMerger;
use Atom\Framework\Configuration\ModuleConfigurationLoader;
use Atom\Framework\Configuration\ViewConfiguration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * @internal
 *
 * @coversNothing
 */
final class ViewConfigurationTest extends TestCase
{
    protected function tearDown(): void
    {
        Configuration::clear();
    }

    public function testMergesApplicationAndModuleViewConventions(): void
    {
        $projectDirectory = dirname(__DIR__, 3);
        $configuration = new ViewConfiguration(
            new ModuleConfigurationLoader(
                $projectDirectory,
                'qubit',
                ['sfIsadPlugin'],
            ),
            new ConfigurationMerger(),
        );

        $view = $configuration->for(
            'sfIsadPlugin',
            'index',
            'Success',
        );

        self::assertTrue($view['has_layout']);
        self::assertSame('layout', $view['layout']);
        self::assertSame(
            ['sfIsadPlugin', 'stylesheet'],
            $view['components']['css'],
        );
        self::assertArrayHasKey(
            '/vendor/imageflow/imageflow.packed.css',
            $view['stylesheets'],
        );
        self::assertArrayHasKey(
            '/vendor/imageflow/imageflow.packed.js',
            $view['javascripts'],
        );
        $this->assertBrowserReadyScripts(
            $projectDirectory,
            $view['javascripts'],
        );
    }

    public function testConfiguredApplicationScriptsAreBrowserReady(): void
    {
        $projectDirectory = dirname(__DIR__, 3);
        $configuration = new ViewConfiguration(
            new ModuleConfigurationLoader(
                $projectDirectory,
                'qubit',
                ['arDominionB5Plugin'],
            ),
            new ConfigurationMerger(),
        );
        $view = $configuration->for(
            'default',
            'index',
            'Success',
        );

        $this->assertBrowserReadyScripts(
            $projectDirectory,
            $view['javascripts'],
        );
    }

    public function testDetectsActionSpecificLayoutConfiguration(): void
    {
        $configuration = new ViewConfiguration(
            new ModuleConfigurationLoader(
                dirname(__DIR__, 3),
                'qubit',
                ['arOaiPlugin'],
            ),
            new ConfigurationMerger(),
        );

        self::assertFalse($configuration->hasLocalLayout(
            'search',
            'autocomplete',
            'Success',
        ));
        self::assertTrue($configuration->hasLocalLayout(
            'arOaiPlugin',
            'identify',
            'Success',
        ));
    }

    private function assertBrowserReadyScripts(
        string $projectDirectory,
        array $scripts,
    ): void {
        Configuration::set('app_b5_theme', true);
        $response = new ResponseAdapter(new Response());

        foreach ($scripts as $source => $options) {
            $response->addJavaScript(
                (string) $source,
                options: is_array($options) ? $options : [],
            );
        }

        preg_match_all(
            '/\bsrc="([^"]+)"/',
            (new AssetRenderer())->javaScripts($response),
            $matches,
        );

        foreach ($matches[1] as $source) {
            if (preg_match('#^(?:https?:)?//#', (string) $source)) {
                continue;
            }

            $path = str_starts_with((string) $source, '/')
                ? (string) $source
                : '/js/'.$source;

            if (!str_contains(basename($path), '.')) {
                $path .= '.js';
            }

            $file = $projectDirectory.$path;

            self::assertFileExists($file, (string) $source);
            self::assertDoesNotMatchRegularExpression(
                '/^\s*(?:import|export)\s/m',
                (string) file_get_contents($file),
                sprintf(
                    'Configured script "%s" must load without a bundler.',
                    $source,
                ),
            );
            self::assertDoesNotMatchRegularExpression(
                '/\beval\s*\(|\bset(?:Timeout|Interval)'
                    .'\s*\(\s*(?:[\'"]|this\.)/',
                (string) file_get_contents($file),
                sprintf(
                    'Configured script "%s" must comply with CSP.',
                    $source,
                ),
            );
        }
    }
}
