<?php
/**
 * This file is part of the sfSearch package.
 * (c) Carl Vondrick <carl.vondrick@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * A common criteria to combine criterions.
 *
 * @package sfSearch
 * @subpackage Criteria
 * @author Carl Vondrick
 */
final class xfCriteria implements xfCriterion
{
  /**
   * The registered criterions.
   *
   * @var array
   */
  private $criterions = array();

  /**
   * Adds a criterion.
   *
   * @param xfCriterion $crit The criterion
   */
  public function add(xfCriterion $crit)
  {
    $this->criterions[] = $crit;
  }

  /**
   * Returns all the criterions.
   *
   * @returns array
   */
  public function getCriterions()
  {
    return $this->criterions;
  }

  /**
   * Gets the last criterion
   *
   * @returns xfCriterion 
   */
  public function getLast()
  {
    $c = count($this->criterions);

    if ($c == 0)
    {
      throw new xfException('No criterions are added.');
    }
    
    return $this->criterions[$c - 1];
  }

  /**
   * Replaces the last criterion with another.
   *
   * @param xfCriterion $new The new criterion
   */
  public function replaceLast(xfCriterion $new)
  {
    $c = count($this->criterions);

    if ($c == 0)
    {
      throw new xfException('No criterions are added.');
    }

    $this->criterions[$c - 1] = $new;
  }

  /**
   * @see xfCriterion
   */
  public function translate(xfCriterionTranslator $translator)
  {
    if (count($this->criterions) > 0)
    {
      $translator->openBoolean();

      foreach ($this->criterions as $crit)
      {
        $crit->translate($translator);
      }

      $translator->closeBoolean();
    }
  }

  /**
   * @see xfCriterion
   */
  public function toString()
  {
    $string = 'BOOLEAN {';

    if (count($this->criterions) > 0)
    {
      foreach ($this->criterions as $crit)
      {
        $string .= '[' . $crit->toString() . '] AND ';
      }

      $string = substr($string, 0, -5);
    }

    return $string . '}';
  }

  /**
   * @see xfCriterion
   */
  public function optimize()
  {
    if (count($this->criterions) == 0)
    {
      return new xfCriterionEmpty;
    }

    if (count($this->criterions) == 1)
    {
      return $this->criterions[0]->optimize();
    }

    $crits = $this->criterions;
    $this->criterions = array();

    foreach ($crits as $crit)
    {
      $new = $crit->optimize();

      if (!$new instanceof xfCriterionEmpty)
      {
        $this->criterions[] = $new;
      }
    }

    return $this;
  }
}
