<?php
/**
 * This file is part of the sfSearch package.
 * (c) Carl Vondrick <carl.vondrick@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * A lexeme is something of significance found by the lexer.
 *
 * @package sfSearch
 * @subpackage Lexer
 * @author Carl Vondrick
 */
class xfLexeme
{
  /**
   * The type
   *
   * @var scalar
   */
  private $type;

  /**
   * The lexeme lexeme
   *
   * @var scalar
   */
  private $lexeme;

  /**
   * The lexeme position 
   *
   * @var int
   */
  private $position;
  
  /**
   * Constructor to set type, lexeme, and position
   *
   * @param scalar $lexeme
   * @param scalar $type
   * @param int $position
   */
  public function __construct($lexeme, $type, $position)
  {
    $this->lexeme = $lexeme;
    $this->position = $position;

    $this->setType($type);
  }

  /**
   * Changes the type.
   *
   * @param scalar $type
   */
  public function setType($type)
  {
    $this->type = $type;
  }

  /**
   * Gets the type
   *
   * @returns scalar
   */
  public function getType()
  {
    return $this->type;
  }

  /**
   * Gets the lexeme
   *
   * @returns scalar
   */
  public function getLexeme()
  {
    return $this->lexeme;
  }

  /**
   * Gets the position
   *
   * @returns int
   */
  public function getPosition()
  {
    return $this->position;
  }
}
