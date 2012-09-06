<?php
/**
 * This file is part of the sfSearch package.
 * (c) Carl Vondrick <carl.vondrick@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * A criterion translator is passed to a criterion object which is then
 * used to create a concrete criterion that the engine can understand.
 *
 * It is important for the translator to have no dependency on the xfCriterion
 * interface.
 *
 * Implementation suggestion: a stack should be created.
 *
 * @package sfSearch
 * @subpackage Criteria
 * @author Carl Vondrick
 */
interface xfCriterionTranslator
{
  /**
   * Opens a new boolean query.
   */
  public function openBoolean();

  /**
   * Closes the current boolean query.
   */
  public function closeBoolean();

  /**
   * Sets these queries to have this boost.
   */
  public function setNextBoost($boost);

  /**
   * Opens a new requirement query.
   */
  public function setNextRequired();

  /**
   * Opens a new negative query.
   */
  public function setNextProhibited();

  /**
   * Opens a new fields query.
   */
  public function setNextField($field);

  /**
   * Creates a phrase query.
   */
  public function createPhrase($phrase, $slop);

  /**
   * Creates a range query.
   */
  public function createRange($start, $end, $startInclude, $endInclude);

  /**
   * Creates a term query.
   */
  public function createTerm($term);

  /**
   * Creates a wildcard query.
   */
  public function createWildcard($pattern);
}
