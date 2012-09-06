<?php
/**
 * This file is part of the sfSearch package.
 * (c) Carl Vondrick <carl.vondrick@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * A lexer for xfParserLucene.
 *
 * Logic based on Zend_Search_Lucene_Search_QueryLexer
 *
 * @package sfSearch
 * @subpackage Lexer
 * @author Carl Vondrick
 * @see xfParserLucene
 */
final class xfLexerLucene implements xfLexer
{
  /**
   * State for white space, eg a space.
   */
  const ST_WHITE               = 0;

  /**
   * State for syntax, eg a bracket.
   */
  const ST_SYNTAX              = 1;

  /**
   * State for a common lexeme, eg a number.
   */
  const ST_LEXEME              = 2;

  /**
   * State for a quote, eg "
   */
  const ST_QUOTED              = 3;

  /**
   * State for a character that is escaped, eg \+
   */
  const ST_ESCAPED_CHAR        = 4;

  /**
   * State for a character that is escaped inside a quote, eg "\+"
   */
  const ST_ESCAPED_QCHAR       = 5;

  /**
   * State for special modifiers, such as ^ (boost) and ~ (slop)
   */
  const ST_MODIFIER            = 6;

  /**
   * State for a number, eg 3.
   */
  const ST_NUMBER              = 7;

  /**
   * State for the part after the decimal point, eg the 3 in 5.3 
   */
  const ST_MANTISSA            = 8;

  /**
   * State when an occur has occured.
   */
  const ST_ERROR               = 9;

  /**
   * Input symbol for white space.
   */
  const IN_WHITE               = 10;

  /**
   * Symbol for a syntax related character, eg a bracket.
   */
  const IN_SYNTAX              = 11;

  /**
   * Symbol for a modifier, such as ^ and ~
   */
  const IN_MODIFIER            = 12;

  /**
   * Symbol for a escape character, such as \+
   */
  const IN_ESCAPE_CHAR        = 13;

  /**
   * Symbol for a quote character
   */
  const IN_QUOTE               = 14;

  /**
   * Symbol for a decimal / period
   */
  const IN_DECIMAL             = 15;

  /**
   * Symbol for a number, eg 1 or 2.
   */
  const IN_NUMBER              = 16;

  /**
   * Symbol for a regular character, eg a.
   */
  const IN_CHAR                = 17;

  /**
   * Symbol a character that is mutable, eg a +
   */
  const IN_MUTABLE            = 18;

  /**
   * Characters that count as white space.
   *
   * @var string
   */
  private $q_whitespace       = " \n\r\t";

  /**
   * Characters that count as syntax.
   *
   * @var string
   */
  private $q_syntax           = ':[](){}!|&';

  /**
   * Characters that count as modifiers.
   *
   * @var string
   */
  private $q_modifier         = '~^';

  /**
   * Characters that count has escape characters.
   *
   * @var string
   */
  private $q_escape           = '\\';

  /**
   * Characters that count as quotes.
   *
   * @var string
   */
  private $q_quote            = '"';

  /**
   * Characters that count as decimals
   *
   * @var string
   */
  private $q_decimal          = '.';

  /**
   * Characters that count as a number.
   *
   * @var string
   */
  private $q_number           = '0123456789';

  /**
   * Characters that count as mutable.
   *
   * @var string
   */
  private $q_mutable          = '+-';

  /**
   * The lexeme holder.
   *
   * @var xfLexemeBuilder
   */
  private $builder;

  /**
   * The finite state machine
   *
   * @var xfFiniteStateMachine
   */
  private $fsm;

  /**
   * Tokenizes the input.
   *
   * @param string $string
   */
  public function tokenize($string, $encoding = 'utf-8')
  {
    $string = trim($string, "\r\n\t ");

    if ($string === '')
    {
      return array();
    }

    $this->builder = new xfLexemeBuilder($string, $encoding, 'xfLexemeLucene');

    $this->setupFiniteStateMachine();

    $this->process();

    return $this->builder->getLexemes();
  }

  /**
   * Creates the lexemes.
   */
  private function process()
  {
    while (false !== $char = $this->builder->next())
    {
      $this->fsm->process($this->translateCharacter($char));
    }

    $this->fsm->process(self::IN_WHITE);

    if ($this->fsm->getState() !== self::ST_WHITE)
    {
      throw new xfParserException('Unexpected end of query.');
    }
  }

  /**
   * Translates a character into a category.
   *
   * @param string $c
   * @returns int The constant
   */
  public function translateCharacter($c)
  {
    if        (false !== strpos($this->q_whitespace,  $c))    return  self::IN_WHITE;
    elseif    (false !== strpos($this->q_syntax,      $c))    return  self::IN_SYNTAX;
    elseif    (false !== strpos($this->q_modifier,    $c))    return  self::IN_MODIFIER;
    elseif    (false !== strpos($this->q_escape,      $c))    return  self::IN_ESCAPE_CHAR;
    elseif    (false !== strpos($this->q_quote,       $c))    return  self::IN_QUOTE;
    elseif    (false !== strpos($this->q_decimal,     $c))    return  self::IN_DECIMAL;
    elseif    (false !== strpos($this->q_number,      $c))    return  self::IN_NUMBER;
    elseif    (false !== strpos($this->q_mutable,     $c))    return  self::IN_MUTABLE;
    else                                                      return  self::IN_CHAR;
  }

  /**
   * Sets up the finite state machine according to rules for the Lucene query
   * syntax.
   *
   * This part of the algorithm is derived from the Zend Framework module, 
   * Zend_Search_Lucene.  Some modifications have been made.
   *
   * @copyright Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
   * @license http://framework.zend.com/license/new-bsd New BSD License
   */
  private function setupFiniteStateMachine()
  {
    $this->fsm = new xfFiniteStateMachine();
    
    // These are our possible states.
    $this->fsm->addStates(array(
      self::ST_WHITE,
      self::ST_SYNTAX,
      self::ST_LEXEME,
      self::ST_QUOTED,
      self::ST_ESCAPED_CHAR,
      self::ST_ESCAPED_QCHAR, 
      self::ST_MODIFIER,
      self::ST_NUMBER,
      self::ST_MANTISSA,
      self::ST_ERROR
    ));

    // Start at this state.
    $this->fsm->setInitialState(self::ST_WHITE);

    // These are the possible transitions.
    $this->fsm->addTransitions(array(

      // White Space Rules:
      // We are looking for something tangible to use.
      array(self::ST_WHITE,         self::IN_WHITE,         self::ST_WHITE),
      array(self::ST_WHITE,         self::IN_SYNTAX,        self::ST_SYNTAX),
      array(self::ST_WHITE,         self::IN_MODIFIER,      self::ST_MODIFIER),
      array(self::ST_WHITE,         self::IN_ESCAPE_CHAR,   self::ST_ESCAPED_CHAR),
      array(self::ST_WHITE,         self::IN_QUOTE,         self::ST_QUOTED),
      array(self::ST_WHITE,         self::IN_DECIMAL,       self::ST_LEXEME),
      array(self::ST_WHITE,         self::IN_NUMBER,        self::ST_LEXEME),
      array(self::ST_WHITE,         self::IN_CHAR,          self::ST_LEXEME),
      array(self::ST_WHITE,         self::IN_MUTABLE,       self::ST_SYNTAX),

      // Syntax Rules:
      // We are dealing with some special syntax, such as a paranthesis.  
      array(self::ST_SYNTAX,        self::IN_WHITE,         self::ST_WHITE),
      array(self::ST_SYNTAX,        self::IN_SYNTAX,        self::ST_SYNTAX),
      array(self::ST_SYNTAX,        self::IN_MODIFIER,      self::ST_MODIFIER),
      array(self::ST_SYNTAX,        self::IN_ESCAPE_CHAR,   self::ST_ESCAPED_CHAR),
      array(self::ST_SYNTAX,        self::IN_QUOTE,         self::ST_QUOTED),
      array(self::ST_SYNTAX,        self::IN_DECIMAL,       self::ST_LEXEME),
      array(self::ST_SYNTAX,        self::IN_NUMBER,        self::ST_LEXEME),
      array(self::ST_SYNTAX,        self::IN_CHAR,          self::ST_LEXEME),
      array(self::ST_SYNTAX,        self::IN_MUTABLE,       self::ST_SYNTAX),

      // Lexeme Rules:
      // We are matching a token, and will accept most of everything.
      array(self::ST_LEXEME,        self::IN_WHITE,         self::ST_WHITE),
      array(self::ST_LEXEME,        self::IN_SYNTAX,        self::ST_SYNTAX),
      array(self::ST_LEXEME,        self::IN_MODIFIER,      self::ST_MODIFIER),
      array(self::ST_LEXEME,        self::IN_ESCAPE_CHAR,   self::ST_ESCAPED_CHAR),
      array(self::ST_LEXEME,        self::IN_QUOTE,         self::ST_ERROR),
      array(self::ST_LEXEME,        self::IN_DECIMAL,       self::ST_LEXEME),
      array(self::ST_LEXEME,        self::IN_NUMBER,        self::ST_LEXEME),
      array(self::ST_LEXEME,        self::IN_CHAR,          self::ST_LEXEME),
      array(self::ST_LEXEME,        self::IN_MUTABLE,       self::ST_LEXEME),

      // Quoted Rules:
      // When inside a quote, everything is part of the phrase query. 
      // Only leave the quote state when:
      //    a) we get another quote, signifying the phrase is over
      //    b) get a escaped character inside a quote
      array(self::ST_QUOTED,        self::IN_WHITE,         self::ST_QUOTED),
      array(self::ST_QUOTED,        self::IN_SYNTAX,        self::ST_QUOTED),
      array(self::ST_QUOTED,        self::IN_MODIFIER,      self::ST_QUOTED),
      array(self::ST_QUOTED,        self::IN_ESCAPE_CHAR,   self::ST_ESCAPED_QCHAR),
      array(self::ST_QUOTED,        self::IN_QUOTE,         self::ST_WHITE),
      array(self::ST_QUOTED,        self::IN_DECIMAL,       self::ST_QUOTED),
      array(self::ST_QUOTED,        self::IN_NUMBER,        self::ST_QUOTED),
      array(self::ST_QUOTED,        self::IN_CHAR,          self::ST_QUOTED),
      array(self::ST_QUOTED,        self::IN_MUTABLE,       self::ST_QUOTED),

      // Escaped Characters Rules:
      // We need to skip whatever character comes next as what is truly is, so
      // we make everything a lexeme.
      array(self::ST_ESCAPED_CHAR,  self::IN_WHITE,         self::ST_LEXEME),
      array(self::ST_ESCAPED_CHAR,  self::IN_SYNTAX,        self::ST_LEXEME),
      array(self::ST_ESCAPED_CHAR,  self::IN_MODIFIER,      self::ST_LEXEME),
      array(self::ST_ESCAPED_CHAR,  self::IN_ESCAPE_CHAR,   self::ST_LEXEME),
      array(self::ST_ESCAPED_CHAR,  self::IN_QUOTE,         self::ST_LEXEME),
      array(self::ST_ESCAPED_CHAR,  self::IN_DECIMAL,       self::ST_LEXEME),
      array(self::ST_ESCAPED_CHAR,  self::IN_NUMBER,        self::ST_LEXEME),
      array(self::ST_ESCAPED_CHAR,  self::IN_CHAR,          self::ST_LEXEME),
      array(self::ST_ESCAPED_CHAR,  self::IN_MUTABLE,       self::ST_LEXEME),

      // Quoted Escaped Characters Rules:
      // Same as Escaped Characters Rules, except we go a quoted lexeme instead.
      array(self::ST_ESCAPED_QCHAR, self::IN_WHITE,         self::ST_QUOTED),
      array(self::ST_ESCAPED_QCHAR, self::IN_SYNTAX,        self::ST_QUOTED),
      array(self::ST_ESCAPED_QCHAR, self::IN_MODIFIER,      self::ST_QUOTED),
      array(self::ST_ESCAPED_QCHAR, self::IN_ESCAPE_CHAR,   self::ST_QUOTED),
      array(self::ST_ESCAPED_QCHAR, self::IN_QUOTE,         self::ST_QUOTED),
      array(self::ST_ESCAPED_QCHAR, self::IN_DECIMAL,       self::ST_QUOTED),
      array(self::ST_ESCAPED_QCHAR, self::IN_NUMBER,        self::ST_QUOTED),
      array(self::ST_ESCAPED_QCHAR, self::IN_CHAR,          self::ST_QUOTED),
      array(self::ST_ESCAPED_QCHAR, self::IN_MUTABLE,       self::ST_QUOTED),

      // Modifier Rules:
      // ^ and ~ can accept either a number or a new query.
      array(self::ST_MODIFIER,      self::IN_WHITE,         self::ST_WHITE),
      array(self::ST_MODIFIER,      self::IN_SYNTAX,        self::ST_SYNTAX),
      array(self::ST_MODIFIER,      self::IN_MODIFIER,      self::ST_MODIFIER),
      array(self::ST_MODIFIER,      self::IN_ESCAPE_CHAR,   self::ST_ERROR),
      array(self::ST_MODIFIER,      self::IN_QUOTE,         self::ST_ERROR),
      array(self::ST_MODIFIER,      self::IN_DECIMAL,       self::ST_MANTISSA),
      array(self::ST_MODIFIER,      self::IN_NUMBER,        self::ST_NUMBER),
      array(self::ST_MODIFIER,      self::IN_CHAR,          self::ST_ERROR),
      array(self::ST_MODIFIER,      self::IN_MUTABLE,       self::ST_SYNTAX),

      // Number Rules:
      // Try to build a number or break the syntax.
      array(self::ST_NUMBER,        self::IN_WHITE,         self::ST_WHITE),
      array(self::ST_NUMBER,        self::IN_SYNTAX,        self::ST_SYNTAX),
      array(self::ST_NUMBER,        self::IN_MODIFIER,      self::ST_MODIFIER),
      array(self::ST_NUMBER,        self::IN_ESCAPE_CHAR,   self::ST_ERROR),
      array(self::ST_NUMBER,        self::IN_QUOTE,         self::ST_ERROR),
      array(self::ST_NUMBER,        self::IN_DECIMAL,       self::ST_MANTISSA),
      array(self::ST_NUMBER,        self::IN_NUMBER,        self::ST_NUMBER),
      array(self::ST_NUMBER,        self::IN_CHAR,          self::ST_ERROR),
      array(self::ST_NUMBER,        self::IN_MUTABLE,       self::ST_SYNTAX),

      // Mantissa Rules:
      // Accept more numbers in the mantissa or break it.
      array(self::ST_MANTISSA,      self::IN_WHITE,         self::ST_WHITE),
      array(self::ST_MANTISSA,      self::IN_SYNTAX,        self::ST_SYNTAX),
      array(self::ST_MANTISSA,      self::IN_MODIFIER,      self::ST_MODIFIER),
      array(self::ST_MANTISSA,      self::IN_ESCAPE_CHAR,   self::ST_ERROR),
      array(self::ST_MANTISSA,      self::IN_QUOTE,         self::ST_ERROR),
      array(self::ST_MANTISSA,      self::IN_DECIMAL,       self::ST_ERROR),
      array(self::ST_MANTISSA,      self::IN_NUMBER,        self::ST_MANTISSA),
      array(self::ST_MANTISSA,      self::IN_CHAR,          self::ST_ERROR),
      array(self::ST_MANTISSA,      self::IN_MUTABLE,       self::ST_SYNTAX),
      
      // Error Rules:
      // None because this state should never be reached.
    ));

    // Actions for when an error occurs.
    $quoteWithinError                = new xfParserFSMError('A quote within a lexeme is not allowed.');
    $modifierError                   = new xfParserFSMError('Only a number, white space, or syntax can follow a modifier.');
    $wrongNumberError                = new xfParserFSMError('Invalid number syntax: make sure the number only has one decimal point and contains only digits.');
    
    // Action for adding lexemes.
    $addChar                        = new xfLexemeBuilderAddChar                      ($this->builder);
    $addLexeme                      = new xfLexemeBuilderAddLexeme                    ($this->builder, xfLexemeLucene::WORD);
    $addModifier                    = new xfLexemeBuilderAddLexeme                    ($this->builder, xfLexemeLucene::SYNTAX);
    $addQuoted                      = new xfLexemeBuilderAddLexeme                    ($this->builder, xfLexemeLucene::PHRASE);
    $addNumber                      = new xfLexemeBuilderAddLexeme                    ($this->builder, xfLexemeLucene::NUMBER);
    $addSyntax                      = new xfLexemeBuilderLuceneAddSyntax              ($this->builder);

    // Bind actions for when an error occurs.
    $this->fsm->addInputAction      (self::ST_LEXEME,         self::IN_QUOTE,         $quoteWithinError);
    $this->fsm->addInputAction      (self::ST_MODIFIER,       self::IN_ESCAPE_CHAR,   $modifierError);
    $this->fsm->addInputAction      (self::ST_MODIFIER,       self::IN_QUOTE,         $modifierError);
    $this->fsm->addInputAction      (self::ST_MODIFIER,       self::IN_CHAR,          $modifierError);
    $this->fsm->addInputAction      (self::ST_NUMBER,         self::IN_ESCAPE_CHAR,   $wrongNumberError);
    $this->fsm->addInputAction      (self::ST_NUMBER,         self::IN_QUOTE,         $wrongNumberError);
    $this->fsm->addInputAction      (self::ST_NUMBER,         self::IN_CHAR,          $wrongNumberError);
    $this->fsm->addInputAction      (self::ST_MANTISSA,       self::IN_ESCAPE_CHAR,   $wrongNumberError);
    $this->fsm->addInputAction      (self::ST_MANTISSA,       self::IN_QUOTE,         $wrongNumberError);
    $this->fsm->addInputAction      (self::ST_MANTISSA,       self::IN_DECIMAL,       $wrongNumberError);
    $this->fsm->addInputAction      (self::ST_MANTISSA,       self::IN_CHAR,          $wrongNumberError);
    
    // Bind actions for adding a character.
    $this->fsm->addEntryAction      (self::ST_LEXEME,                                 $addChar);
    $this->fsm->addTransitionAction (self::ST_LEXEME,         self::ST_LEXEME,        $addChar);
    $this->fsm->addTransitionAction (self::ST_QUOTED,         self::ST_QUOTED,        $addChar);
    $this->fsm->addTransitionAction (self::ST_ESCAPED_QCHAR,  self::ST_QUOTED,        $addChar);
    $this->fsm->addEntryAction      (self::ST_NUMBER,                                 $addChar);
    $this->fsm->addEntryAction      (self::ST_MANTISSA,                               $addChar);
    $this->fsm->addTransitionAction (self::ST_NUMBER,         self::ST_NUMBER,        $addChar);
    $this->fsm->addTransitionAction (self::ST_MANTISSA,       self::ST_MANTISSA,      $addChar);

    // Bind actions for adding a lexeme.
    $this->fsm->addTransitionAction (self::ST_LEXEME,         self::ST_WHITE,         $addLexeme);
    $this->fsm->addTransitionAction (self::ST_LEXEME,         self::ST_SYNTAX,        $addLexeme);
    $this->fsm->addTransitionAction (self::ST_LEXEME,         self::ST_QUOTED,        $addLexeme);
    $this->fsm->addTransitionAction (self::ST_LEXEME,         self::ST_MODIFIER,      $addLexeme);
    $this->fsm->addTransitionAction (self::ST_LEXEME,         self::ST_NUMBER,        $addLexeme);
    $this->fsm->addTransitionAction (self::ST_LEXEME,         self::ST_MANTISSA,      $addLexeme);

    // Bind actions for adding a quoted lexeme.
    $this->fsm->addTransitionAction (self::ST_QUOTED,         self::ST_WHITE,         $addQuoted);

    // Bind actions for adding a number.
    $this->fsm->addTransitionAction (self::ST_NUMBER,         self::ST_WHITE,         $addNumber);
    $this->fsm->addTransitionAction (self::ST_NUMBER,         self::ST_SYNTAX,        $addNumber);
    $this->fsm->addTransitionAction (self::ST_NUMBER,         self::ST_MODIFIER,      $addNumber);
    $this->fsm->addTransitionAction (self::ST_MANTISSA,       self::ST_WHITE,         $addNumber);
    $this->fsm->addTransitionAction (self::ST_MANTISSA,       self::ST_SYNTAX,        $addNumber);
    $this->fsm->addTransitionAction (self::ST_MANTISSA,       self::ST_MODIFIER,      $addNumber);

    // Bind actions for adding a modifier
    $this->fsm->addEntryAction      (self::ST_MODIFIER,                               $addChar);
    $this->fsm->addEntryAction      (self::ST_MODIFIER,                               $addModifier);

    // Bind actions for adding a syntax lexeme.
    $this->fsm->addEntryAction      (self::ST_SYNTAX,                                 $addSyntax);
    $this->fsm->addTransitionAction (self::ST_SYNTAX,         self::ST_SYNTAX,        $addSyntax);
  }
}
