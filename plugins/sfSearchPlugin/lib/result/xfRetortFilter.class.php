<?php
/**
 * This file is part of the sfSearch package.
 * (c) Carl Vondrick <carl.vondrick@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * A retort that wraps another retort to filter its responses.
 *
 * This allows for other components to add features to any retort.
 *
 * @package sfSearch
 * @subpackage Result
 * @author Carl Vondrick
 */
final class xfRetortFilter implements xfRetort
{
  /**
   * The wrapped retort.
   *
   * @var xfRetort
   */
  private $retort;

  /**
   * The filters.
   *
   * @var array
   */
  private $filters = array();

  /**
   * Constructor to set initial values.
   *
   * @param xfRetort $retort
   * @param array $filters
   */
  public function __construct(xfRetort $retort, array $filters = array())
  {
    $this->retort = $retort;
    $this->filters = $filters;
  }

  /**
   * Registers a filter.
   *
   * @param xfRetortFilterCallable|callable $call
   */
  public function registerFilter($filter)
  {
    if ($filter instanceof xfRetortFilterCallback || is_callable($filter))
    {
      $this->filters[] = $filter;
    }
    else
    {
      throw new xfResultException('Retort filter must be a valid PHP callback or instance or xfRetortFilterCallable');
    }
  }

  /**
   * @see xfRetort
   */
  public function can(xfDocumentHit $hit, $method, array $args = array())
  {
    return $this->retort->can($hit, $method, $args);
  }

  /**
   * @see xfRetort
   */
  public function respond(xfDocumentHit $hit, $method, array $args = array())
  {
    $response = $this->retort->respond($hit, $method, $args);

    foreach ($this->filters as $filter)
    {
      if ($filter instanceof xfRetortFilterCallback)
      {
        $response = $filter->filter($response, $hit, $method, $args);
      }
      elseif (is_callable($filter))
      {
        $response = call_user_func($filter, $response);
      }
    }

    return $response;
  }
}
