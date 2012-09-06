<?php
/**
 * This file is part of the sfSearch package.
 * (c) Carl Vondrick <carl.vondrick@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require dirname(__FILE__) . '/../../bootstrap/unit.php';
require 'result/xfPager.class.php';

$t = new lime_test(37, new lime_output_color);

$pager = new xfPager(new ArrayIterator(range(1, 1000)));

$t->diag('->getPage(), ->setPage()');
$t->is($pager->getPage(), 1, '->getPage() returns the current page');
$pager->setPage(3);
$t->is($pager->getPage(), 3, '->setPage() changes the page');
$pager->setPage(31415926);
$t->is($pager->getPage(), 1000 / 10, '->setPage() does not go higher than the last page');
$pager->setPage(0);
$t->is($pager->getPage(), 1,' ->setPage() does not go lower than the first page');

$t->diag('->getPerPage(), ->setPerPage()');
$t->is($pager->getPerPage(), 10, '->getPerPage() returns the per page count');
$pager->setPerPage(11);
$t->is($pager->getPerPage(), 11, '->setPerPage() changes the per page count');
$pager->setPerPage(-1);
$t->is($pager->getPerpage(), 0, '->setPerPage() does not allow the per page count to go below 0');
$pager->setPerPage(10);
$pager->setPage(100);
$pager->setPerPage(100);
$t->is($pager->getPage(), 10, '->setPerPage() keeps the page count in bounds');

$t->diag('->getResults(), ->getNbResults()');
$pager->setPage(2);
$t->isa_ok($pager->getResults(), 'LimitIterator', '->getResults() returns a LimitIterator');
$t->is($pager->getResults()->getPosition(), 0, '->getResults() returns a LimitIterator with correct position');
$t->isa_ok($pager->getResults()->getInnerIterator(), 'ArrayIterator', '->getResults() returns LimitIterator with the correct iterator');
$t->is($pager->getNbResults(), 1000, '->getNbResults() returns the number of results');

$t->diag('->getFirstPage(), ->getLastPage()');
$pager->setPerPage(11);
$t->is($pager->getFirstPage(), 1, '->getFirstPage() returns the first page');
$t->is($pager->getLastPage(), 91, '->getLastPage() returns the last page');
$pager->setPerPage(0);
$t->is($pager->getLastPage(), 1, '->getLastPage() returns the first page if per page count is 0');

$t->diag('->atFirstPage(), ->atLastPage()');
$pager->setPage(1);
$pager->setPerPage(10);
$t->ok($pager->atFirstPage(), '->atFirstPage() returns true if at first page');
$t->ok(!$pager->atLastPage(), '->atLastPage() returns false if not at last page');
$pager->setPage(100);
$t->ok(!$pager->atFirstPage(), '->atFirstPage() returns false if not at first page');
$t->ok($pager->atLastPage(), '->atLastPage() returns true if at last page');

$t->diag('->haveToPaginate()');
$pager->setPage(5);
$t->ok($pager->haveToPaginate(), '->haveToPaginate() returns true if in middle');
$pager->setPage(1);
$t->oK($pager->haveToPaginate(), '->haveToPaginate() returns true if at first but not last');
$pager->setPage(100);
$t->ok($pager->haveToPaginate(), '->haveToPaginate() returns true if at last but not first');
$pager->setPerPage(10000000);
$t->ok(!$pager->haveToPaginate(), '->haveToPaginate() returns false if at first and at at last');

$t->diag('->getNextPage(), ->getPreviousPage()');
$pager->setPerPage(10);
$pager->setPage(1);
$t->is($pager->getNextPage(), 2, '->getNextPage() is the next page');
$t->is($pager->getPreviousPage(), 1, '->getPreviousPage() returns the first page if at first page already');
$pager->setPage(100);
$t->is($pager->getNextPage(), 100, '->getNextPage() returns the last page if at last page already');
$t->is($pager->getPreviousPage(), 99, '->getPreviusPage() is the previous page');

$t->diag('->getStartPosition(), ->getEndPosition()');
$pager->setPage(1);
$t->is($pager->getStartPosition(), 1, '->getStartPosition() returns the start position when at start');
$t->is($pager->getEndPosition(), 10, '->getEndPosition() returns the end position when at astart');
$pager->setPage(2);
$t->is($pager->getStartPosition(), 11, '->getStartPosition() returns the start position when in middle');
$t->is($pager->getEndPosition(), 20, '->getEndPosition() returns the end position when in middle');
$pager->setPerPage(99);
$pager->setPage(11);
$t->is($pager->getStartPosition(), 991, '->getStartPosition() returns the start position when at end');
$t->is($pager->getEndPosition(), 1000, '->getEndPosition() returns the end position when at end');

$t->diag('->getLinks()');
$pager->setPerPage(10);
$pager->setPage(50);
$t->is($pager->getLinks(10), range(45, 55), '->getLinks() returns array of numbers with current page at center');
$pager->setPage(1);
$t->is($pager->getLinks(10), range(1, 6), '->getLinks() returns array of numbers with current page first if at first page');
$pager->setPage(100);
$t->is($pager->getLinks(10), range(95, 100), '->getLinks() returns array of numbers with current page last if at last page');

$t->diag('->setUrlFormat(), ->getPageUrl()');
$pager->setUrlFormat('page=%page%');
$t->is($pager->getPageUrl(5), 'page=5', '->getPageUrl() generates a page URL');
