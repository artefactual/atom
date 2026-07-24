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

use Atom\Framework\Bridge\AssetRenderer;
use Atom\Framework\Bridge\BridgeRegistrar;
use Atom\Framework\Bridge\Context;
use Atom\Framework\Bridge\EventDispatcher;
use Atom\Framework\Bridge\RequestAdapter;
use Atom\Framework\Bridge\ResponseAdapter;
use Atom\Framework\Bridge\RuntimeConfiguration;
use Atom\Framework\Bridge\TemplateRenderer;
use Atom\Framework\Bridge\TranslatorFactory;
use Atom\Framework\Bridge\User;
use Atom\Framework\Bridge\ViewRuntimeFactory;
use Atom\Framework\Configuration\ConfigurationMerger;
use Atom\Framework\Configuration\ModuleConfigurationLoader;
use Atom\Framework\Configuration\ViewConfiguration;
use Atom\Framework\Module\ComponentLocator;
use Atom\Framework\Module\LayoutLocator;
use Atom\Framework\Module\ModuleDirectories;
use Atom\Framework\Module\TemplateLocator;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;

/**
 * @internal
 *
 * @coversNothing
 */
final class ViewRuntimeTest extends TestCase
{
    protected function tearDown(): void
    {
        Context::setInstance(null);
    }

    public function testRendersPartialsComponentsLayoutsAndSlots(): void
    {
        vfsStream::setup('project', null, [
            'framework' => ['config' => ['view.yml' => <<<'YAML'
                default:
                  has_layout: true
                  layout: layout
                  http_metas:
                    X-UA-Compatible: IE=edge
                  metas:
                    title: Fixture
                    description: Bridge fixture
                  stylesheets:
                    theme: { media: print }
                  javascripts:
                    application:
                YAML]],
            'apps' => ['qubit' => [
                'config' => [],
                'templates' => [
                    'layout.php' => '<default><?= $sf_content ?></default>',
                    'shell.php' => <<<'PHP'
                        <html><head></head><body><main><?= $sf_content ?></main><aside><?php include_slot('aside'); ?></aside></body></html>
                        PHP,
                ],
                'modules' => ['bridgefixture' => [
                    'actions' => [
                        'greetingComponent.class.php' => <<<'PHP'
                            <?php
                            class BridgeFixtureGreetingComponent extends sfComponent
                            {
                                public function execute($request)
                                {
                                    $this->greeting = 'Hello '.$this->name;
                                }
                            }
                            PHP,
                    ],
                    'config' => ['view.yml' => <<<'YAML'
                        indexSuccess:
                          components:
                            footer: [bridgefixture, greeting, { name: Footer }]
                        YAML],
                    'templates' => [
                        'indexSuccess.php' => <<<'PHP'
                            <?php decorate_with('shell'); ?>
                            <?php slot('aside'); ?>Side<?php end_slot(); ?>
                            <?= get_partial('message', ['value' => 'Body']) ?>
                            <?= get_component('bridgefixture', 'greeting', ['name' => 'AtoM']) ?>
                            <?= $sf_data->getRaw('sf_request') === $sf_request ? '<em>Request</em>' : '' ?>
                            PHP,
                        '_message.php' => '<p><?= $value ?></p>',
                        '_greeting.php' => '<strong><?= $greeting ?></strong>',
                    ],
                ]],
            ]],
        ]);
        (new BridgeRegistrar())->register();
        $directories = new ModuleDirectories(
            'vfs://project',
            'qubit',
        );
        $templates = new TemplateLocator($directories);
        $renderer = new TemplateRenderer();
        $viewConfiguration = new ViewConfiguration(
            new ModuleConfigurationLoader(
                'vfs://project',
                'qubit',
                [],
                'vfs://project/framework',
            ),
            new ConfigurationMerger(),
        );
        $factory = new ViewRuntimeFactory(
            new ComponentLocator($directories),
            $templates,
            new LayoutLocator('vfs://project', 'qubit'),
            $renderer,
            $viewConfiguration,
            new AssetRenderer(),
        );
        $request = Request::create('/');
        $request->attributes->add([
            'module' => 'bridgefixture',
            'action' => 'index',
        ]);
        $context = new Context(
            new RequestAdapter($request),
            new ResponseAdapter(new Response()),
            new User(),
            new RuntimeConfiguration(
                'qubit',
                'test',
                [],
                true,
                'vfs://project',
            ),
            new EventDispatcher(),
            $this->createMock(RouterInterface::class),
            new TranslatorFactory('vfs://project', 'qubit', []),
            $factory,
        );
        Context::setInstance($context);
        $runtime = $context->getViewRuntime();
        $runtime->prepare('bridgefixture', 'index');
        self::assertSame('Fixture', $context->getResponse()->getTitle());
        $context->getResponse()->setTitle('Action - Fixture');
        $runtime->begin('bridgefixture', 'index', 'Success');
        $path = $templates->find('bridgefixture', 'index');
        self::assertNotNull($path);
        $content = $runtime->render($path, [], 'bridgefixture');

        $html = preg_replace(
            '/>\s+</',
            '><',
            trim($runtime->decorate($content, [])),
        );

        self::assertStringContainsString(
            '<main><p>Body</p><strong>Hello AtoM</strong>'
                .'<em>Request</em></main>'
                .'<aside>Side</aside>',
            $html,
        );
        self::assertStringContainsString('<em>Request</em>', $html);
        self::assertStringContainsString(
            '<meta http-equiv="X-UA-Compatible" content="IE=edge" />',
            $html,
        );
        self::assertStringContainsString(
            '<meta name="description" content="Bridge fixture" />',
            $html,
        );
        self::assertStringContainsString(
            '<link media="print" rel="stylesheet" href="/css/theme.css" />',
            $html,
        );
        self::assertStringContainsString(
            '<script src="/js/application.js"></script></body>',
            $html,
        );
        self::assertSame(
            '<strong>Hello Footer</strong>',
            get_component_slot('footer'),
        );
        self::assertSame(
            'Action - Fixture',
            $context->getResponse()->getTitle(),
        );
    }
}
