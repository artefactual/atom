<?php
/**
 * This file is part of the sfSearch package.
 * (c) Carl Vondrick <carl.vondrick@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * A criterion that matches a phrase.
 *
 * @package sfSearch
 * @subpackage Criteria
 * @author Carl Vondrick
 */
final class xfCriterionPhrase implements xfCriterion
{
  /**
   * The phrase
   *
   * @var string
   */
  private $phrase;

  /**
   * The slop
   *
   * @var int
   */
  private $slop = 0;

  /**
   * Constructor to set phrase.
   *
   * @param string $phrase
   * @param int $slop
   */
  public function __construct($phrase, $slop = 0)
  {
    $this->phrase = $phrase;
    $this->slop = $slop;
  }

  /**
   * Gets the phrase
   *
   * @returns string
   */
  public function getPhrase()
  {
    return $this->phrase;
  }

  /**
   * Sets the slop
   *
   * @var int $slop
   */
  public function setSlop($slop)
  {
    $this->slop = (int) $slop;
  }

  /**
   * Gets the slop
   *
   * @returns int
   */
  public function getSlop()
  {
    return $this->slop;
  }

  /**
   * @see xfCriterion
   */
  public function toString()
  {
    $string = 'PHRASE {"' . $this->phrase . '"';

    if ($this->slop > 0)
    {
      $string .= ' SLOP ' . $this->slop;
    }

    return $string . '}';
  }

  /**
   * @see xfCriterion
   */
  public function translate(xfCriterionTranslator $translator)
  {
    $translator->createPhrase($this->phrase, $this->slop);
  }

  /**
   * @see xfCriterion
   */
  public function optimize()
  {
    return $this;
  }
}
