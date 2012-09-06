<?php
/**
 * This file is part of the sfSearch package.
 * (c) Carl Vondrick <carl.vondrick@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * An empty criterion that matches nothing.
 *
 * @package sfSearch
 * @subpackage Criteria
 * @author Carl Vondrick
 */
final class xfCriterionEmpty implements xfCriterion
{
  /**
   * @see xfCriterion
   */
  public function translate(xfCriterionTranslator $translator)
  {
  }

  /**
   * @see xfCriterion
   */
  public function toString()
  {
    return 'EMPTY';
  }

  /**
   * @see xfCriterion
   */
  public function optimize()
  {
    return $this;
  }
}
