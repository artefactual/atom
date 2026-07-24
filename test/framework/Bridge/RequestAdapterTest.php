<?php

/*
 * This file is part of the Access to Memory (AtoM) software.
 *
 * Access to Memory (AtoM) is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Access to Memory (AtoM) is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Access to Memory (AtoM). If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

namespace Atom\Tests\Framework\Bridge;

use Atom\Framework\Bridge\RequestAdapter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

/**
 * @internal
 *
 * @coversNothing
 */
final class RequestAdapterTest extends TestCase
{
    public function testProvidesLegacyArrayAccessToParameters(): void
    {
        $request = new Request([], ['email' => 'test@example.com']);
        $adapter = new RequestAdapter($request);

        self::assertSame('test@example.com', $adapter['email']);

        $adapter['next'] = '/';
        unset($adapter['email']);

        self::assertSame('/', $adapter->next);
        self::assertFalse(isset($adapter['email']));
        self::assertSame(['next' => '/'], $adapter->getParameters());
    }

    public function testProvidesLegacyRequestLocationMethods(): void
    {
        $request = Request::create(
            'https://archives.test/atom/record',
            'GET',
            [],
            [],
            [],
            [
                'SCRIPT_NAME' => '/atom/index.php',
                'SCRIPT_FILENAME' => '/srv/atom/index.php',
            ],
        );
        $adapter = new RequestAdapter($request);

        self::assertSame('/atom', $adapter->getPathInfoPrefix());
        self::assertSame('archives.test', $adapter->getHost());
        self::assertSame(
            'https://archives.test',
            $adapter->getUriPrefix(),
        );
        self::assertTrue($adapter->isSecure());
        self::assertSame(
            'archives.test',
            $adapter->getPathInfoArray()['HTTP_HOST'],
        );
    }

    public function testProvidesLegacyRequestContentMethods(): void
    {
        $request = Request::create(
            '/api',
            'POST',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json; charset=UTF-8'],
            '{"title":"Example"}',
        );
        $adapter = new RequestAdapter($request);

        self::assertSame('POST', $adapter->getMethod());
        self::assertSame('application/json', $adapter->getContentType());
        self::assertSame(
            'application/json; charset=UTF-8',
            $adapter->getContentType(false),
        );
        self::assertSame(
            '{"title":"Example"}',
            $adapter->getContent(),
        );
    }

    public function testDerivesRequestFormatFromLegacyParameter(): void
    {
        $adapter = new RequestAdapter(Request::create(
            '/record?sf_format=xml',
        ));

        self::assertSame('xml', $adapter->getRequestFormat());
        self::assertSame('text/xml', $adapter->getMimeType('xml'));
    }

    public function testPrefersAnExplicitlySetRequestFormat(): void
    {
        $adapter = new RequestAdapter(Request::create(
            '/record?sf_format=xml',
        ));
        $adapter->setRequestFormat('json');

        self::assertSame('json', $adapter->getRequestFormat());
    }

    public function testReturnsArrayInputAndArrayDefaults(): void
    {
        $request = new Request(
            ['filters' => ['published' => true]],
            ['slugs' => ['first', 'second']],
        );
        $request->cookies->set('preferences', ['culture' => 'fr']);
        $adapter = new RequestAdapter($request);

        self::assertSame(
            ['published' => true],
            $adapter->getGetParameter('filters', []),
        );
        self::assertSame(
            ['first', 'second'],
            $adapter->getPostParameter('slugs', []),
        );
        self::assertSame(
            ['culture' => 'fr'],
            $adapter->getCookie('preferences', []),
        );
        self::assertSame(
            [],
            $adapter->getPostParameter('missing', []),
        );
    }

    public function testNormalizesUploadedFilesForLegacyActions(): void
    {
        $upload = new UploadedFile(
            __FILE__,
            'records.csv',
            'text/csv',
            \UPLOAD_ERR_OK,
            true,
        );
        $request = Request::create(
            '/import',
            'POST',
            [],
            [],
            ['file' => $upload],
        );
        $adapter = new RequestAdapter($request);

        self::assertSame([
            'name' => 'records.csv',
            'type' => 'text/csv',
            'tmp_name' => __FILE__,
            'error' => \UPLOAD_ERR_OK,
            'size' => filesize(__FILE__),
        ], $adapter->getFiles('file'));
        self::assertSame([], $adapter->getFiles('missing'));
    }
}
