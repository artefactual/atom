<?php
/**
 * This file is part of the sfSearch package.
 * (c) Carl Vondrick <carl.vondrick@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Interface for a parser to parse queries.
 *
 * Use the parser with criterions:
 * <code>
 * $parser = new xfParserFoo;
 * $crit = $parser->parse($query);
 *
 * $results = $index->find($crit);
 * </code>
 *
 * @package sfSearch
 * @subpackage Parser
 * @author Carl Vondrick
 */
interface xfParser
{
  /**
   * Parses the query.
   *
   * @param string $query The query to parse
   * @param string $encoding The query encoding
   * @returns xfCriterion
   * @throw xfParserException if parsing fails
   */
  public function parse($query, $encoding = 'utf8');
}
