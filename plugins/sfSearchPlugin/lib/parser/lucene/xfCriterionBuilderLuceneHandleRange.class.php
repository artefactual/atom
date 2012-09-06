<?php
/**
 * This file is part of the sfSearch package.
 * (c) Carl Vondrick <carl.vondrick@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * A builder to handle range queries.
 *
 * @package sfSearch
 * @subpackage Parser
 * @author Carl Vondrick
 */
final class xfCriterionBuilderLuceneHandleRange extends xfCriterionBuilderActionCommon
{
  /**
   * @see xfFiniteStateMachineAction
   */
  public function execute()
  {
    $endMarker = $this->builder->getLexeme(0)->getLexeme();
    $endValue = $this->builder->getLexeme(-1)->getLexeme();
    $startValue = $this->builder->getLexeme(-3)->getLexeme();  // skip -2 because it's the separator
    $startMarker = $this->builder->getLexeme(-4)->getLexeme();

    $startInclusive = $startMarker == '[';
    $endInclusive = $endMarker == ']';

    $this->builder->add(new xfCriterionRange($startValue, $endValue, $startInclusive, $endInclusive));
  }
}
