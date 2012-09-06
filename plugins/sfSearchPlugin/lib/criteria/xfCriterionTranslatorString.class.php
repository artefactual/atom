<?php
/**
 * This file is part of the sfSearch package.
 * (c) Carl Vondrick <carl.vondrick@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * A translator for unit testing that creates a string.
 *
 * @package sfSearch
 * @subpackage Criteria
 * @author Carl Vondrick
 */
final class xfCriterionTranslatorString implements xfCriterionTranslator
{
  /**
   * The modifiers
   *
   * @var string
   */
  private $modifiers = '';

  /**
   * The current string.
   */
  private $string = '';

  /**
   * Adds to the string.
   *
   * @param string $input
   */
  private function add($input)
  {
    $this->string .= $this->modifiers . $input . ' ';

    $this->modifiers = '';
  }

  /**
   * Gets the string
   *
   * @returns string
   */
  public function getString()
  {
    return trim($this->string);
  }

  /**
   * @see xfCriterionTranslator
   */
  public function openBoolean()
  {
    $this->add('{{');
  }

  /**
   * @see xfCriterionTranslator
   */
  public function closeBoolean()
  {
    $this->string .= '}} ';
    $this->modifiers = '';
  }

  /**
   * @see xfCriterionTranslator
   */
  public function setNextRequired()
  {
    $this->modifiers .= '+';
  }

  /**
   * @see xfCriterionTranslator
   */
  public function setNextProhibited()
  {
    $this->modifiers .= '-';
  }

  /**
   * @see xfCriterionTranslator
   */
  public function setNextField($field)
  {
    $this->modifiers = $field . ':' . $this->modifiers;
  }

  /**
   * @see xfCriterionTranslator
   */
  public function setNextBoost($boost)
  {
    $this->modifiers .= $boost . '^';
  }

  /**
   * @see xfCriterionTranslator
   */
  public function createPhrase($phrase, $slop)
  {
    $this->add('"' . $phrase . '"~' . $slop);
  }

  /**
   * @see xfCriterionTranslator
   */
  public function createRange($start, $end, $startInclude, $endInclude)
  {
    if ($startInclude)
    {
      $string = '[';
    }
    else
    {
      $string = '(';
    }

    $string .= $start . ' ... ' . $end;

    if ($endInclude)
    {
      $string .= ']';
    }
    else
    {
      $string .= ')';
    }
    
    $this->add($string);
  }

  /**
   * @see xfCriterionTranslator
   */
  public function createTerm($term)
  {
    $this->add($term);
  }

  /**
   * @see xfCriterionTranslator
   */
  public function createWildcard($pattern)
  {
    $this->add('/' . $pattern . '/');
  }
}
