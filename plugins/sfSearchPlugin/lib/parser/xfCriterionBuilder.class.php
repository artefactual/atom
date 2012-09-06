<?php
/**
 * This file is part of the sfSearch package.
 * (c) Carl Vondrick <carl.vondrick@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * A criterion builder creates a xfCriterion.
 *
 * This is very similar to xfCriterionTranslator, but must not implement
 * that interface because it is not a translator.
 *
 * @package sfSearch
 * @subpackage Parser
 * @author Carl Vondrick
 */
final class xfCriterionBuilder
{
  /**
   * The master query.
   *
   * @var xfCriterion
   */
  private $master = null;

  /**
   * The boolean query stack
   *
   * @var array
   */
  private $queries = array();

  /**
   * Specifies queries to decorate with
   *
   * @var array
   */
  private $decorators = array();

  /**
   * The lexemes we are iterating over
   *
   * @var array
   */
  private $lexemes = array();

  /**
   * The lexeme position
   *
   * @var int
   */
  private $position = -1;

  /**
   * Constructor to initialize
   *
   * @param array $lexemes
   */
  public function __construct(array $lexemes)
  {
    $this->lexemes = $lexemes;

    $c = new xfCriteria;
    $this->master = $c;
    $this->queries[] = $c;
  }

  /**
   * Advances the pointer and gets the next lexeme, or null if none
   *
   * @returns xfLexeme
   */
  public function next()
  {
    $this->position++;

    if (!isset($this->lexemes[$this->position]))
    {
      return null;
    }

    return $this->lexemes[$this->position];
  }

  /**
   * Gets a lexeme by index.
   *
   * The default is to get the current lexeme (0).  If a negative index is given,
   * it looks back by the magnitude.  If a positive is given, it looks forward by
   * that magnitude.
   *
   * @param int $index (default 0)
   */
  public function getLexeme($index = 0)
  {
    $lookup = $this->position + $index;

    if ($lookup < 0 || $lookup >= count($this->lexemes))
    {
      throw new xfParserException('Lexeme index is out of bounds');
    }

    return $this->lexemes[$lookup];
  }

  /**
   * Gets the master query.
   *
   * @returns xfCriterion
   */
  public function getMaster()
  {
    if (count($this->queries) != 1)
    {
      throw new xfParserException('A boolean query is still open.');
    }

    return $this->master->optimize();
  }

  /**
   * Opens a new boolean query.
   */
  public function openBoolean()
  {
    $this->add(new xfCriteria);
  }

  /**
   * Closes the current boolean query.
   */
  public function closeBoolean()
  {
    if (count($this->queries) == 1)
    {
      throw new xfParserException('Unable to close boolean query because none are open.');
    }

    $this->decorators = array();
    array_pop($this->queries);
  }

  /**
   * Gets the last criterion
   */
  public function getLastBoolean()
  {
    return $this->queries[count($this->queries) - 1];
  }

  /**
   * Adds a decorator
   *
   * @param string $decorator The decorator class
   * @param array $args to the decorator
   */
  public function addDecorator($decorator, array $args = array())
  {
    $this->decorators[$decorator] = $args;
  }

  /**
   * Decorates the previous query.
   *
   * If the third paramater $boolean is true, then the decorator is applied
   * to the previous boolean criterion instead of the previous subquery.
   *
   * @param string $decorator The decorator class
   * @param array $args to the decorator
   * @param bool $boolean
   */
  public function addRetroDecorator($decorator, array $args = array(), $boolean = false)
  {
    $offset = $boolean ? 2 : 1; 

    $lastBool = $this->queries[count($this->queries) - $offset];
    $lastBool->replaceLast($this->decorateCriterion($lastBool->getLast(), $decorator, $args));
  }

  /**
   * Decorates a criterion
   *
   * @param xfCriterion $criterion
   * @param string $decorator
   * @param array $args
   */
  private function decorateCriterion(xfCriterion $criterion, $decorator, array $args = array())
  {
    array_unshift($args, $criterion);
    $refObj = new ReflectionClass($decorator);
    if (!$refObj->isSubclassOf(new ReflectionClass('xfCriterionDecorator')))
    {
      throw new xfParserException('Decorator must extend xfCriterionDecorator');
    }

    $query = $refObj->newInstanceArgs($args);
    
    return $query; 
  }

  /**
   * Adds a query
   *
   * @param xfCriterion $query
   */
  public function add(xfCriterion $query)
  {
    // this method gets tricky because of decorators
    //
    // if the query is an xfCriteria, we need to add it to stack before applying
    // any decorators because the stack must contain only xfCriteria.  
    //
    // when we add the decorated subquery later on, we have to be sure to add it
    // to the correct parent.  if the $query is not an xfCriteria, the parent is
    // the previous one in the stack.  but, if it is an xfCriteria, it's two
    // back in the stack

    $offset = 1;
    if ($query instanceof xfCriteria)
    {
      $offset = 2;
      $this->queries[] = $query;
    }

    foreach ($this->decorators as $decorator => $args)
    {
      $query = $this->decorateCriterion($query, $decorator, $args);
    }

    $this->decorators = array();

    $this->queries[count($this->queries) - $offset]->add($query);
  }
}
