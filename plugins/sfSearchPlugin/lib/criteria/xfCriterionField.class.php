<?php
/**
 * This file is part of the sfSearch package.
 * (c) Carl Vondrick <carl.vondrick@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * A criterion to require a field to match.
 *
 * @package sfSearch
 * @subpackage Criteria
 * @author Carl Vondrick
 */
final class xfCriterionField extends xfCriterionDecorator
{
  /**
   * The field it must match on.
   *
   * @var array
   */
  private $field = array();

  /**
   * Constructor to set initial values.
   *
   * @param xfCriterion $criterion The criterion that must match this field.
   * @param string $field The field that the criterion must match
   */
  public function __construct(xfCriterion $criterion, $field)
  {
    parent::__construct($criterion);
    
    $this->field = $field;
  }

  /**
   * Gets the field
   *
   * @returns array
   */
  public function getField()
  {
    return $this->field;
  }

  /**
   * @see xfCriterion
   */
  public function toString()
  {
    return 'FIELD {' . $this->field . ' IS ' . $this->getCriterion()->toString() . '}';
  }

  /**
   * @see xfCriterion
   */
  public function translate(xfCriterionTranslator $translator)
  {
    $translator->setNextField($this->field);

    parent::translate($translator);
  }
}
