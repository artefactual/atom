<?php
/**
 * This file is part of the sfSearch package.
 * (c) Carl Vondrick <carl.vondrick@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class xfParserTester
{
  private $lime, $parser, $timer = 0, $count = 0;

  public function __construct(lime_test $lime, xfParser $parser)
  {
    $this->lime = $lime;
    $this->parser = $parser;
  }

  public function pass($query, $expected, $encoding = 'utf8')
  {
    $msg = str_pad($query, 20, ' ', STR_PAD_RIGHT) . ' <--> ' . $expected;

    $startTime = microtime(true); 
    $query = $this->parser->parse($query, $encoding);
    $this->timer += microtime(true) - $startTime;
    $this->count++;

    $this->lime->is($query->toString(), $expected, $msg);
  }

  public function fail($query, $encoding = 'utf8')
  {
    $msg = 'Fails: ' . $query;

    try
    {
      $this->parser->parse($query, $encoding);

      $this->lime->fail($msg);
    }
    catch (Exception $e)
    {
      $this->lime->pass($msg);
    }
  }

  public function stats()
  {
    $this->lime->diag('Query Parser Statistics:');
    $this->lime->diag('Total Queries: ' . $this->count);
    $this->lime->diag('Total Time: ' . $this->timer . ' sec');
    $this->lime->diag('Average Query Time: ' . ($this->timer / $this->count) . ' sec/query');
  }
}
