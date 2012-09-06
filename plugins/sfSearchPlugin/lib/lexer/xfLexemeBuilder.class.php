<?php
/**
 * This file is part of the sfSearch package.
 * (c) Carl Vondrick <carl.vondrick@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Holds a lexemes of lexemes and aids in generating new ones.
 *
 * The builder also acts a lightweight dependency injector for xfLexeme.
 *
 * @package sfSearch
 * @subpackage Lexer
 * @author Carl Vondrick
 */
final class xfLexemeBuilder
{
  /**
   * The lexemes of lexemes.
   *
   * @var array
   */
  private $lexemes = array();

  /**
   * The current lexeme being built.
   *
   * @var string
   */
  private $currentLexeme;

  /**
   * The current lexeme type.
   *
   * @var int
   */
  private $type;

  /**
   * The position in the string.
   *
   * @var int
   */
  private $position = -1;

  /**
   * The characters to analyze
   *
   * @var array
   */
  private $characters = array();

  /**
   * The encoding of the string
   *
   * @var string
   */
  private $encoding = array();

  /**
   * The lexeme class.
   *
   * @var string
   */
  private $class;

  /**
   * Constructor to set the class.
   *
   * @param string $class
   */
  public function __construct($string, $encoding = 'utf-8', $class = 'xfLexeme')
  {
    $this->class = $class;
    $this->encoding = $encoding;

    $length = mb_strlen($string, $encoding);

    for ($x = 0; $x < $length; $x++)
    {
      $this->characters[] = mb_substr($string, $x, 1, $encoding);
    }

    $this->newLexeme();
  }

  /**
   * Advances the pointer and gets the next character.
   *
   * @returns null|string The next character, or null if done.
   */
  public function next()
  {
    $this->position++;

    if (array_key_exists($this->position, $this->characters))
    {
      return $this->characters[$this->position];
    }

    return false;
  }

  /**
   * Gets the character from our current position.
   *
   * @returns string
   */
  public function getCharacter()
  {
    if ($this->position < 0 || $this->position >= count($this->characters))
    {
      return null;
    }

    return $this->characters[$this->position];
  }

  /**
   * Adds a character to the lexeme being built.
   *
   * @param string $char
   */
  public function addToLexeme($char)
  {
    $this->currentLexeme .= $char;
  }

  /**
   * Commits the current lexeme.
   */
  public function commit()
  {
    if ($this->currentLexeme !== '')
    {
      $class = $this->class;

      $this->lexemes[] = new $class($this->currentLexeme, $this->type, $this->position);
    }
    // else throw new Exception('Warning: Empty commit!'); // for testing purposes

    $this->newLexeme();
  }

  /**
   * Commits the current lexeme and creates a new one.
   */
  public function newLexeme()
  {
    $this->currentLexeme = '';
    $this->type = null;
  }

  /**
   * Gets a lexeme by index.
   *
   * If index is positive, it finds it going forwards.
   * If index is negative, it finds it going backwards.
   *
   * @param int $index
   * @returns xfLexeme
   */
  public function getLexeme($index)
  {
    if ($index < 0)
    {
      $index = count($this->lexemes) + $index; // $index is negative...
    }

    if ($index >= count($this->lexemes) || $index < 0)
    {
      throw new xfException('Trying to get a lexeme that is out of range.');
    }

    return $this->lexemes[$index];
  }

  /**
   * Gets the current lexeme type.
   *
   * @returns string
   */
  public function getType()
  {
    return $this->type;
  }

  /**
   * Sets the lexeme type
   *
   * @param int $type
   */
  public function setType($type)
  {
    $this->type = $type;
  }

  /**
   * Gets the current position
   *
   * @returns int
   */
  public function getPosition()
  {
    return $this->position;
  }

  /**
   * Advances the position by $count (default 1)
   *
   * @param int $count The position to increase by (default 1)
   */
  public function advancePosition($count = 1)
  {
    $this->position += $count;
  }

  /**
   * Gets the lexemes.
   *
   * @returns array
   */
  public function getLexemes()
  {
    return $this->lexemes;
  }

  /**
   * Gets the lexemes size.
   *
   * @returns int
   */
  public function count()
  {
    return count($this->lexemes);
  }
}
