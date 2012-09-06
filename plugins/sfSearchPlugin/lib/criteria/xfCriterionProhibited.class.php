<?php
/**
 * This file is part of the sfSearch package.
 * (c) Carl Vondrick <carl.vondrick@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * A criterion that wraps another to signify that it CANNOT match.
 *
 * @package sfSearch
 * @subpackage Criteria
 * @author Carl Vondrick
 */
final class xfCriterionProhibited extends xfCriterionDecorator
{
  /**
   * @see xfCriterion
   */
  public function translate(xfCriterionTranslator $translator)
  {
    $translator->setNextProhibited();

    parent::translate($translator);
  }

  /**
   * @see xfCriterion
   */
  public function toString()
  {
    return 'PROHIBITED {' . parent::toString() . '}';
  }
}
