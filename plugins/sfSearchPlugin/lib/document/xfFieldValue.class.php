<?php
/**
 * This file is part of the sfSearch package.
 * (c) Carl Vondrick <carl.vondrick@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * A field with a value anchored to it.
 *
 * @package sfSearch
 * @subpackage Document
 * @author Carl Vondrick
 */
final class xfFieldValue
{
  /**
   * The field the value is bound to.
   *
   * @var xfField
   */
  private $field;

  /**
   * The value
   *
   * @var string
   */
  private $value;

  /**
   * The encoding of the value
   *
   * @var string
   */
  private $encoding = 'utf8';

  /**
   * Initializes default values.
   *
   * @param xfField $field The field
   * @param string $value The value
   * @param string $encoding The encoding (optional)
   */
  public function __construct(xfField $field, $value, $encoding = 'utf8')
  {
    $this->field = $field;
    $this->value = $field->transformValue($value);
    $this->encoding = $encoding;
  }

  /**
   * Gets the field.
   *
   * @returns xfField
   */
  public function getField()
  {
    return $this->field;
  }

  /**
   * Gets the field value.
   *
   * @returns string
   */
  public function getValue()
  {
    return $this->value;
  }

  /**
   * Gets the field encoding
   *
   * @returns string
   */
  public function getEncoding()
  {
    return $this->encoding;
  }
}
