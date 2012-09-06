<?php
/**
 * This file is part of the sfSearch package.
 * (c) Carl Vondrick <carl.vondrick@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * A retort to generate a route.
 *
 * @package sfSearch
 * @subpackage Result
 * @author Carl Vondrick
 */
final class xfRetortRoute implements xfRetort
{
  /**
   * The route template.
   *
   * @var string
   */
  private $template;

  /**
   * The template matches.
   *
   * @var array
   */
  private $matches = array();

  /**
   * The method bound to
   *
   * @var string
   */
  private $method = 'getRoute';

  /**
   * Constructor
   *
   * @param string $template The route template
   */
  public function __construct($template)
  {
    $this->template = $template;

    preg_match_all('/\$(\w+?)\$/', $template, $matches);
    $this->matches = $matches[1];
  }

  /**
   * The method to accept
   *
   * @param string $method
   */
  public function setMethod($method)
  {
    $this->method = $method;
  }
  
  /**
   * @see xfRetort
   */
  public function can(xfDocumentHit $hit, $method, array $args = array())
  {
    return $this->method == $method;
  }

  /**
   * @see xfRetort
   */
  public function respond(xfDocumentHit $hit, $method, array $args = array())
  {
    $route = $this->template;

    foreach ($this->matches as $match)
    {
      $route = str_replace('$' . $match . '$', $hit->getDocument()->getField($match)->getValue(), $route);
    }

    return $route;
  }
}
