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

use Atom\Framework\Bridge\ViewCacheManager;
use Atom\Framework\Cache\Cache;
use Atom\Framework\Configuration\ModuleConfigurationLoader;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @internal
 */
final class ViewCacheManagerTest extends TestCase
{
    private InMemoryViewCache $cache;
    private RequestStack $requests;
    private ViewCacheManager $manager;

    protected function setUp(): void
    {
        vfsStream::setup('project', null, [
            'apps' => ['qubit' => [
                'config' => ['cache.yml' => <<<'YAML'
                    default:
                      enabled: false
                      lifetime: 86400
                    YAML],
                'modules' => ['menu' => [
                    'config' => ['cache.yml' => <<<'YAML'
                        _mainMenu:
                          enabled: true
                          contextual: false
                        YAML],
                ]],
            ]],
        ]);
        $this->cache = new InMemoryViewCache(['prefix' => 'atom']);
        $this->requests = new RequestStack();
        $this->requests->push(Request::create('http://example.test/'));
        $this->manager = new ViewCacheManager(
            $this->cache,
            new ModuleConfigurationLoader(
                'vfs://project',
                'qubit',
                [],
            ),
            $this->requests,
        );
    }

    public function testCachesOnlyConfiguredGetComponents(): void
    {
        $variables = ['sf_cache_key' => 'theme-en-user'];

        self::assertNull($this->manager->getComponent(
            'menu',
            'mainMenu',
            $variables,
        ));
        self::assertSame(
            '<nav>Menu</nav>',
            $this->manager->setComponent(
                'menu',
                'mainMenu',
                $variables,
                '<nav>Menu</nav>',
            ),
        );
        self::assertSame(
            '<nav>Menu</nav>',
            $this->manager->getComponent(
                'menu',
                'mainMenu',
                $variables,
            ),
        );

        self::assertSame(
            '<aside>Ignored</aside>',
            $this->manager->setComponent(
                'menu',
                'userMenu',
                [],
                '<aside>Ignored</aside>',
            ),
        );
        self::assertNull($this->manager->getComponent(
            'menu',
            'userMenu',
            [],
        ));
    }

    public function testDoesNotCachePostRequests(): void
    {
        $this->requests->pop();
        $this->requests->push(Request::create(
            'http://example.test/',
            'POST',
        ));

        $this->manager->setComponent(
            'menu',
            'mainMenu',
            ['sf_cache_key' => 'post'],
            '<nav>Post</nav>',
        );

        self::assertNull($this->manager->getComponent(
            'menu',
            'mainMenu',
            ['sf_cache_key' => 'post'],
        ));
    }

    public function testRemovesComponentCachePatterns(): void
    {
        foreach (['first', 'second'] as $key) {
            $this->manager->setComponent(
                'menu',
                'mainMenu',
                ['sf_cache_key' => $key],
                '<nav>'.$key.'</nav>',
            );
        }

        self::assertTrue($this->manager->remove(
            '@sf_cache_partial?module=menu'
                .'&action=_mainMenu&sf_cache_key=*',
        ));

        foreach (['first', 'second'] as $key) {
            self::assertNull($this->manager->getComponent(
                'menu',
                'mainMenu',
                ['sf_cache_key' => $key],
            ));
        }
    }
}

final class InMemoryViewCache extends Cache
{
    private array $values = [];

    public function get($key, $default = null)
    {
        return $this->values[$this->key($key)] ?? $default;
    }

    public function has($key)
    {
        return array_key_exists($this->key($key), $this->values);
    }

    public function set($key, $data, $lifetime = null)
    {
        $this->values[$this->key($key)] = $data;

        return true;
    }

    public function remove($key)
    {
        $key = $this->key($key);
        $exists = array_key_exists($key, $this->values);
        unset($this->values[$key]);

        return $exists;
    }

    public function removePattern($pattern)
    {
        $regexp = $this->patternToRegexp($this->key($pattern));

        foreach (array_keys($this->values) as $key) {
            if (preg_match($regexp, $key)) {
                unset($this->values[$key]);
            }
        }
    }

    public function clean($mode = self::ALL)
    {
        $this->values = [];

        return true;
    }

    public function getTimeout($key)
    {
        return 0;
    }

    public function getLastModified($key)
    {
        return 0;
    }

    private function key(string $key): string
    {
        return $this->getOption('prefix').$key;
    }
}
