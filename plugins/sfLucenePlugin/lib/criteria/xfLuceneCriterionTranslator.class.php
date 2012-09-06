<?php
/**
 * This file is part of the sfLucene package.
 * (c) Carl Vondrick <carl.vondrick@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Interacts with xfCriterions to create Zend_Search_Lucene queries.
 *
 * @package sfLucene
 * @subpackage Criteria
 * @author Carl Vondrick
 */
final class xfLuceneCriterionTranslator implements xfCriterionTranslator
{
  /**
   * Internal constant for a boost modifier.
   */
  const M_BOOST = 1;

  /**
   * Internal constant for a requirement modifier (required or prohibited)
   */
  const M_REQUIREMENT = 2;

  /**
   * Internal constant for a field modifier.
   */
  const M_FIELD = 3;

  /**
   * The Zend query stack.
   * 
   * @var array of boolean queries
   */
  private $queries = array();

  /**
   * Modifiers for the next query.
   *
   * @var array
   */
  private $modifiers = array();

  /**
   * The master Zend query.
   *
   * @var Zend_Search_Lucene_Search_Query
   */
  private $master;

  /**
   * @see xfCriterionTranslator
   */
  public function openBoolean()
  {
    $this->add(new Zend_Search_Lucene_Search_Query_Boolean);
  }

  /**
   * @see xfCriterionTranslator
   */
  public function closeBoolean()
  {
    array_pop($this->queries);
  }

  /**
   * @see xfCriterionTranslator
   */
  public function createPhrase($phrase, $slop)
  {
    $phrase = preg_split('/\s+/', $phrase);

    $p = new Zend_Search_Lucene_Search_Query_Phrase($phrase, null, $this->popFieldModifier());
    $p->setSlop($slop);

    $this->add($p);
  }

  /**
   * @see xfCriterionTranslator
   */
  public function createRange($start, $end, $startInclude, $endInclude)
  {
    $field = $this->popFieldModifier();
    
    $start = new Zend_Search_Lucene_Index_Term($start, $field);
    $end = new Zend_Search_Lucene_Index_Term($end, $field);

    $this->add(new Zend_Search_Lucene_Search_Query_Range($start, $end, $startInclude || $endInclude));
  }

  /**
   * @see xfCriterionTranslator
   */
  public function createTerm($term)
  {
    $this->add(new Zend_Search_Lucene_Search_Query_Term(new Zend_Search_Lucene_Index_Term($term, $this->popFieldModifier())));
  }

  /**
   * @see xfCriterionTranslator
   */
  public function createWildcard($pattern)
  {
    $this->add(new Zend_Search_Lucene_Search_Query_Wildcard(new Zend_Search_Lucene_Index_Term($pattern, $this->popFieldModifier())));
  }

  /**
   * @see xfCriterionTranslator
   */
  public function setNextBoost($boost)
  {
    $this->modifiers[self::M_BOOST] = $boost;
  }

  /**
   * @see xfCriterionTranslator
   */
  public function setNextRequired()
  {
    $this->modifiers[self::M_REQUIREMENT] = true;
  }

  /**
   * @see xfCriterionTranslator
   */
  public function setNextProhibited()
  {
    $this->modifiers[self::M_REQUIREMENT] = false;
  }

  /**
   * @see xfCriterionTranslator
   */
  public function setNextField($field)
  {
    $this->modifiers[self::M_FIELD] = $field;
  }

  /**
   * Pops the field from the modifiers.
   *
   * If the field exists in the modifiers, return it and remove it from the modifiers.
   *
   * @returns string|null string if the field is modified, null otherwise
   */
  private function popFieldModifier()
  {
    $field = null;

    if (isset($this->modifiers[self::M_FIELD]))
    {
      $field = $this->modifiers[self::M_FIELD];
      unset($this->modifiers[self::M_FIELD]);
    }
   
   return $field;
  }

  /**
   * Gets the master query, or null if not built.
   *
   * @returns Zend_Search_Lucene_Search_Query|null
   */
  public function getZendQuery()
  {
    return $this->master;
  }

  /**
   * Adds a query.
   *
   * @param Zend_Search_Lucene_Search_Query $q
   */
  private function add(Zend_Search_Lucene_Search_Query $q)
  {
    $autoClose = false;

    // apply modifiers
    foreach ($this->modifiers as $type => $value)
    {
      switch ($type)
      {
        case self::M_BOOST:
          $q->setBoost($value);
          break;
        case self::M_REQUIREMENT:
          $q = new Zend_Search_Lucene_Search_Query_Boolean(array($q), array($value));
          $autoClose = true;
          break;
      }
    }

    $this->modifiers = array();

    // determine how to add the query

    if ($this->master === null)
    {
      $this->master = $q;
    }
    else
    {
      $c = count($this->queries);

      if ($c == 0)
      {
        throw new xfLuceneException('Cannot add a query to a ' . get_class($this->master) . ' query, likely a mismatch in creating a boolean query');
      }

      $this->queries[count($this->queries) - 1]->addSubquery($q);
    }

    if (!$autoClose && $q instanceof Zend_Search_Lucene_Search_Query_Boolean)
    {
      $this->queries[] = $q;
    }
  }

  /**
   * For unit testing: gets the string of the query.
   *
   */
  public function toString()
  {
    return $this->getZendQuery()->__toString();
  }
}
