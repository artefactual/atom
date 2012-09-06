<?php
/**
 * This file is part of the sfSearch package.
 * (c) Carl Vondrick <carl.vondrick@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * A range query.
 *
 * @package sfSearch
 * @subpackage Criteria
 * @author Carl Vondrick
 */
final class xfCriterionRange implements xfCriterion
{
  /**
   * The start
   *
   * @var mixed
   */
  private $start;

  /**
   * The end
   *
   * @var mixed
   */
  private $end;

  /**
   * If true, start is closed interval.
   *
   * @var bool
   */
  private $startInclude = true;

  /**
   * If true, end is closed interval.
   *
   * @var bool
   */
  private $endInclude = true;

  /**
   * Constructor to set start and end. 
   *
   * Start or end (but not both) can be null, which means go to infinity.
   *
   * @param mixed $start
   * @param mixed $end
   */
  public function __construct($start, $end, $startInclude = true, $endInclude = true)
  {
    if ($start === null && $end === null)
    {
      throw new xfException('Start and end may not be both null.');
    }

    $this->start = $start;
    $this->end = $end;

    $this->startInclude = $startInclude;
    $this->endInclude = $endInclude;
  }

  /**
   * @see xfCriterion
   */
  public function toString()
  {
    $string = 'RANGE {';

    if ($this->startInclude)
    {
      $string .= '[';
    }
    else
    {
      $string .= '(';
    }

    if ($this->start !== null)
    {
      $string .= $this->start;
    }
    else
    {
      $string .= 'inf';
    }

    $string .= ',';

    if ($this->end !== null)
    {
      $string .= $this->end;
    }
    else
    {
      $string .= 'inf';
    }

    if ($this->endInclude)
    {
      $string .= ']';
    }
    else
    {
      $string .= ')';
    }

    $string .= '}';

    return $string;
  }

  /**
   * @see xfCriterion
   */
  public function translate(xfCriterionTranslator $translator)
  {
    $translator->createRange($this->start, $this->end, $this->startInclude, $this->endInclude);
  }

  /**
   * @see xfCriterion
   */
  public function optimize()
  {
    return $this;
  }
}
