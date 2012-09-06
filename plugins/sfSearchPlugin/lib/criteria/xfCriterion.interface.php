<?php
/**
 * This file is part of the sfSearch package.
 * (c) Carl Vondrick <carl.vondrick@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * An interface for criterions.
 *
 * @package sfSearch
 * @subpackage Criteria
 * @author Carl Vondrick
 */
interface xfCriterion
{
  /**
   * Gets the criterion as a string.  This should not considered parseable, but 
   * just a way to debug the criterion.
   *
   * @returns string
   */
  public function toString();

  /**
   * Translates the criterion into a concrete for the engine.
   *
   * @param xfCriterionTranslator 
   */
  public function translate(xfCriterionTranslator $translator);

  /**
   * Attempts to optimize the query by reducing empty or impossible steps.
   *
   * This method should return an optimized version of itself, or itself
   * if no optimized version exists.
   *
   * @returns xfCriterion
   */
  public function optimize();
}
