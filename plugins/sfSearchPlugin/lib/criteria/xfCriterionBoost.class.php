<?php
/**
 * This file is part of the sfSearch package.
 * (c) Carl Vondrick <carl.vondrick@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Gives a wrapped criterion a boost
 *
 * @package sfSearch
 * @subpackage Criteria
 * @author Carl Vondrick
 */
final class xfCriterionBoost extends xfCriterionDecorator
{
  /**
   * The boost
   *
   * @var float
   */
  private $boost;

  /**
   * Constructor to set boost
   *
   * @param xfCriterion $criterion
   * @param float $boost
   */
  public function __construct(xfCriterion $criterion, $boost = 1)
  {
    parent::__construct($criterion);

    $this->boost = $boost;
  }

  /**
   * Sets the boost
   *
   * @param float $boost
   */
  public function setBoost($boost)
  {
    $this->boost = (int) $boost;
  }

  /**
   * Gets the boost
   *
   * @returns float
   */
  public function getBoost()
  {
    return $this->boost;
  }

  /**
   * @see xfCriterion
   */
  public function translate(xfCriterionTranslator $translator)
  {
    $translator->setNextBoost($this->boost);

    parent::translate($translator);
  }

  /**
   * @see xfCriterion
   */
  public function toString()
  {
    return 'BOOST {' . $this->boost . ' ON ' . parent::toString() . '}';
  }

  /**
   * @see xfCriterion
   */
  public function optimize()
  {
    if ($this->boost == 1)
    {
      return $this->getCriterion()->optimize();
    }

    return parent::optimize();
  }
}
