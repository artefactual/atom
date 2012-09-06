<?php
/**
 * This file is part of the sfSearch package.
 * (c) Carl Vondrick <carl.vondrick@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * A wildcard query.
 *
 * * = matches anything, any number of times
 * ? = matches any single character
 *
 * @package sfSearch
 * @subpackage Criteria
 * @author Carl Vondrick
 */
final class xfCriterionWildcard implements xfCriterion
{
  /**
   * The query
   *
   * @var string
   */
  private $query;

  /**
   * Creates the query
   *
   * @param string $query 
   */
  public function __construct($query)
  {
    $this->query = $query;
  }

  /**
   * @see xfCriterion
   */
  public function toString()
  {
    return 'WILDCARD {' . $this->query . '}';
  }

  /**
   * @see xfCriterion
   */
  public function translate(xfCriterionTranslator $translator)
  {
    $translator->createWildcard($this->query);
  }

  /**
   * @see xfCriterion
   */
  public function optimize()
  {
    if (false === strpos($this->query, '?') && false === strpos($this->query, '*'))
    {
      return new xfCriterionTerm($this->query);
    }
    
    return $this;
  }
}
