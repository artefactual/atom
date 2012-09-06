<?php
/**
 * This file is part of the sfSearch package.
 * (c) Carl Vondrick <carl.vondrick@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * A field without a value to be added to a document.
 *
 * @package sfSearch
 * @subpackage Document
 * @author Carl Vondrick
 */
final class xfField
{
  /**
   * Type flag for stored.
   */
  const STORED = 1;

  /**
   * Type flag for indexed.
   */
  const INDEXED = 2;

  /**
   * Type flag for tokenized.
   */
  const TOKENIZED = 4;

  /**
   * Type flag for binary data.
   */
  const BINARY = 8;
  
  /**
   * Type flag for indexed, stored, not-tokenized
   */
  const KEYWORD = 3;

  /**
   * Type flag for indexed, stored, tokenized.
   */
  const TEXT = 7;

  /**
   * Type flag for indexed, tokenized.
   */
  const UNSTORED = 6;

  /**
   * Type flag for stored (alias).
   */
  const UNINDEXED = 1;

  /**
   * The field name.
   *
   * @var string
   */
  private $name;

  /**
   * The field type.
   *
   * @var int
   */
  private $type;

  /**
   * The callbacks.
   *
   * @var array
   */
  private $callbacks = array();

  /**
   * The field boost
   *
   * @var float
   */
  private $boost = 1.0;

  /**
   * Initializes the field.
   *
   * @param string $name The field name
   * @param int $type The field type
   * @throws xfDocumentException if type is invalid
   */
  public function __construct($name, $type = 3)
  {
    if(!is_int($type) || $type <= 0)
    {
      throw new xfDocumentException('Field type must be an integer');
    }

    $this->name = $name;
    $this->type = $type;
  }

  /**
   * Sets the field boost.
   *
   * @param float $boost
   */
  public function setBoost($boost)
  {
    $this->boost = (float) $boost;
  }

  /**
   * Gets the field boost.
   *
   * @returns float
   */
  public function getBoost()
  {
    return $this->boost;
  }

  /**
   * Registers a callback.
   *
   * @param callable $callback The callback to register.
   */
  public function registerCallback($callback)
  {
    $this->callbacks[] = $callback;
  }

  /**
   * Transforms a value from the callbacks.
   *
   * @param string $value The initial value
   * @returns string The transformed value
   */
  public function transformValue($value)
  {
    foreach ($this->callbacks as $callback)
    {
      $value = call_user_func($callback, $value);
    }

    return $value;
  }

  /**
   * Gets the field name.
   *
   * @returns string The field name
   */
  public function getName()
  {
    return $this->name;
  }

  /**
   * Gets the field type as an integer.
   *
   * @returns int
   */
  public function getType()
  {
    return $this->type;
  }
}
