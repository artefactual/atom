<?php
/**
 * This file is part of the sfSearch package.
 * (c) Carl Vondrick <carl.vondrick@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * A criterion that matches a word.
 *
 * @package sfSearch
 * @subpackage Criteria
 * @author Carl Vondrick
 */
final class xfCriterionTerm implements xfCriterion
{
  /**
   * The term
   *
   * @var string
   */
  private $term;

  /**
   * Constructor to set term
   *
   * @param string $term
   */
  public function __construct($term)
  {
    $this->term = $term;
  }

  /**
   * Gets the term.
   *
   * @returns string
   */
  public function getTerm()
  {
    return $this->term;
  }

  /**
   * @see xfCriterion
   */
  public function translate(xfCriterionTranslator $translator)
  {
    $translator->createTerm($this->term);
  }

  /**
   * @see xfCriterion
   */
  public function toString()
  {
    return $this->term;
  }

  /**
   * @see xfCriterion
   */
  public function optimize()
  {
    return $this;
  }
}
