<?php
/**
 * This file is part of the sfSearch package.
 * (c) Carl Vondrick <carl.vondrick@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * A parser that follows the Lucene-based search engine syntax.
 *
 * See the syntax described here: 
 * http://lucene.apache.org/java/docs/queryparsersyntax.html
 *
 * Logic inspired from Zend_Search_Lucene_Search_QueryParser
 * and Apache Lucene's QueryParser.
 *
 * @package sfSearch
 * @subpackage Parser
 * @author Carl Vondrick
 */
final class xfParserLucene implements xfParser
{
  /**
   * State for a normal query item (most things)
   */
  const ST_QUERY = 1;

  /**
   * State for a inclusive range start.
   */
  const ST_RANGE_START = 2;

  /**
   * State for a inclusive range end.
   */
  const ST_RANGE_END = 3;

  /**
   * State for the end indicator.
   */
  const ST_RANGE_END_INDICATOR = 4;

  /**
   * State for the separator
   */
  const ST_RANGE_SEPARATOR = 5;

  /**
   * State for an error
   */
  const ST_ERROR = 0;

  /**
   * The finite state machine
   *
   * @var xfFiniteStateMachine
   */
  private $fsm;

  /**
   * The criterion builder
   *
   * @var xfCriterionBuilder
   */
  private $builder;

  /**
   * Default slop for a phrase.
   * 
   * @var int
   */
  private $phraseSlop;

  /**
   * @see xfParser
   */
  public function parse($query, $encoding = 'utf8')
  {
    try
    {
      $lexer = new xfLexerLucene;
      return $this->process($lexer->tokenize($query, $encoding));
    }
    catch (Exception $e)
    {
      throw new xfParserException('Error parsing query: ' . $e->getMessage());
    }
  }

  /**
   * Processes the query.
   *
   * @param array $lexemes The lexemes
   * @returns xfCriterion
   */
  private function process(array $lexemes)
  {
    if (count($lexemes) == 0)
    {
      return new xfCriterionEmpty;
    }

    $this->builder = new xfCriterionBuilder($lexemes);
    $this->setupFiniteStateMachine();

    while ($lexeme = $this->builder->next())
    {
      $this->fsm->process($lexeme->getType());
    }

    if ($this->fsm->getState() != self::ST_QUERY)
    {
      throw new xfParserException('Unexpected end of query.');
    }

    return $this->builder->getMaster();
  }

  /**
   * Sets the default phrase slop.  0 is exact match.
   *
   * @param int $slop
   */
  public function setPhraseSlop($slop)
  {
    $this->phraseSlop = $slop;
  }

  /**
   * Gets the default phrase slop.
   *
   * @returns int
   */
  public function getPhraseSlop()
  {
    return $this->phraseSlop;
  }

  /**
   * Sets up the finite state machine.
   */
  private function setupFiniteStateMachine()
  {
    $this->fsm = new xfFiniteStateMachine;

    $this->fsm->addStates(array(
      self::ST_QUERY,
      self::ST_RANGE_START,
      self::ST_RANGE_END,
      self::ST_RANGE_END_INDICATOR,
      self::ST_RANGE_SEPARATOR,
      self::ST_ERROR
    ));

    $this->fsm->setInitialState(self::ST_QUERY);

    $this->fsm->addTransitions(array(
      array(self::ST_QUERY,   xfLexemeLucene::WORD,     self::ST_QUERY),
      array(self::ST_QUERY,   xfLexemeLucene::SYNTAX,   self::ST_QUERY),
      array(self::ST_QUERY,   xfLexemeLucene::PHRASE,   self::ST_QUERY),
      array(self::ST_QUERY,   xfLexemeLucene::NUMBER,   self::ST_QUERY),
      array(self::ST_QUERY,   xfLexemeLucene::FIELD,    self::ST_QUERY),
      array(self::ST_QUERY,   xfLexemeLucene::WILDCARD, self::ST_QUERY),
    ));


    $this->fsm->addTransitions(array(
      array(self::ST_QUERY,                 xfLexemeLucene::RANGE_START_INCLUSIVE,    self::ST_RANGE_START),
      array(self::ST_QUERY,                 xfLexemeLucene::RANGE_START_EXCLUSIVE,    self::ST_RANGE_START),
      array(self::ST_RANGE_START,           xfLexemeLucene::WORD,                     self::ST_RANGE_SEPARATOR),
      array(self::ST_RANGE_SEPARATOR,       xfLexemeLucene::RANGE_SEPARATOR,          self::ST_RANGE_END),
      array(self::ST_RANGE_END,             xfLexemeLucene::WORD,                     self::ST_RANGE_END_INDICATOR),
      array(self::ST_RANGE_END_INDICATOR,   xfLexemeLucene::RANGE_END_INCLUSIVE,      self::ST_QUERY), 
      array(self::ST_RANGE_END_INDICATOR,   xfLexemeLucene::RANGE_END_EXCLUSIVE,      self::ST_QUERY)
    ));

    $addTerm = new xfCriterionBuilderAddTerm($this->builder);
    $addPhrase = new xfCriterionBuilderAddPhrase($this->builder, $this->phraseSlop);
    $addWildcard = new xfCriterionBuilderAddWildcard($this->builder);
    $addField = new xfCriterionBuilderSetField($this->builder);
    $handleSyntax = new xfCriterionBuilderLuceneHandleSyntax($this->builder);
    $handleRange = new xfCriterionBuilderLuceneHandleRange($this->builder);

    $this->fsm->addInputAction(self::ST_QUERY, xfLexemeLucene::WORD, $addTerm);
    $this->fsm->addInputAction(self::ST_QUERY, xfLexemeLucene::PHRASE, $addPhrase);
    $this->fsm->addInputAction(self::ST_QUERY, xfLexemeLucene::WILDCARD, $addWildcard);
    $this->fsm->addInputAction(self::ST_QUERY, xfLexemeLucene::FIELD, $addField);
    $this->fsm->addInputAction(self::ST_QUERY, xfLexemeLucene::SYNTAX, $handleSyntax);
    $this->fsm->addExitAction(self::ST_RANGE_END_INDICATOR, $handleRange);
  }
}
