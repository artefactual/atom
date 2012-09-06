<?php
/**
 * This file is part of the sfSearch package.
 * (c) Carl Vondrick <carl.vondrick@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * A silent parser that will default to the xfParserSimple if a wrapped parser
 * fails to parse.
 *
 * Usage:
 * <code>
 * $p = new xfParserLucene('foobar (');
 * $s = new xfParserSilent($p);
 *
 * $s->parse(); // returns a criterion without exception
 * $p->parse(); // throws a parse exception because of mismatch of (
 * </code>
 *
 * @package sfSearch
 * @subpackage Parser
 * @author Carl Vondrick
 */
final class xfParserSilent implements xfParser
{
  /**
   * The parser
   *
   * @var xfParser
   */
  private $parser;

  /**
   * Else use this parser
   *
   * @var xfParser
   */
  private $else;

  /**
   * Sets the parser
   *
   * @param xfParser $parser Try to use this parser
   * @param xfParser $else but if it fails, use this parser (default xfParserSimple)
   */
  public function __construct(xfParser $parser, xfParser $else = null)
  {
    $this->parser = $parser;

    if ($else === null)
    {
      $else = new xfParserSimple;
    }

    $this->else = $else;
  }

  /**
   * @see xfParser
   */
  public function parse($query, $encoding = 'utf8')
  {
    try
    {
      return $this->parser->parse($query, $encoding);
    }
    catch (Exception $e)
    {
      return $this->else->parse($query, $encoding);
    }
  }
}
