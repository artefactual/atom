<?php
/**
 * This file is part of the sfSearch package.
 * (c) Carl Vondrick <carl.vondrick@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * An *extremely* simple parser for the most basic search queries.
 *
 * This parser does not throw any exceptions.
 *
 * @package sfSearch
 * @subpackage Parser
 * @author Carl Vondrick
 */
final class xfParserSimple implements xfParser
{
  /**
   * @see xfParser
   *
   * @param string $query
   * @param string $encoding (optional)
   */
  public function parse($query, $encoding = 'utf8')
  {
    $c = new xfCriteria;
    foreach (preg_split('/\s+/', $query) as $token)
    {
      $token = trim($token);

      if (!empty($token))
      {
        $c->add(new xfCriterionTerm($token));
      }
    }

    return $c->optimize();
  }
}
