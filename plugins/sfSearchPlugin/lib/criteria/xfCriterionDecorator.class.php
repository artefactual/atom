<?php
/**
 * This file is part of the sfSearch package.
 * (c) Carl Vondrick <carl.vondrick@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * A criterion that wraps another criterion to signify how to handle it, eg it
 * should not match or give it a boost.
 *
 * @package sfSearch
 * @subpackage Criteria
 * @author Carl Vondrick
 */
abstract class xfCriterionDecorator implements xfCriterion
{
  /**
   * The criterion that MUST match
   *
   * @var xfCriterion
   */
  private $criterion;

  /**
   * Constructor to set criterion
   *
   * @param xfCriterion $criterion
   */
  public function __construct(xfCriterion $criterion)
  {
    $this->criterion = $criterion;
  }

  /**
   * Gets the criterion
   *
   * @returns xfCriterion
   */
  public function getCriterion()
  {
    return $this->criterion;
  }

  /**
   * @see xfCriterion
   */
  public function translate(xfCriterionTranslator $translator)
  {
    $this->criterion->translate($translator);
  }

  /**
   * @see xfCriterion
   */
  public function toString()
  {
    return $this->criterion->toString();
  }

  /**
   * @see xfCriterion
   */
  public function optimize()
  {
    $this->criterion->optimize();

    return $this;
  }
}
