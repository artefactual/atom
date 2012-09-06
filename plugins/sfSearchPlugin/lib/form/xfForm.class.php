<?php
/**
 * This file is part of the sfSearch package.
 * (c) Carl Vondrick <carl.vondrick@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * The base sfSearch form.
 *
 * This would be an interface, but we need to inherit from sfForm.
 *
 * @package sfSearch
 * @subpackage Form
 * @author Carl Vondrick
 */
abstract class xfForm extends sfForm
{
  /**
   * Gets the page number.
   *
   * @returns int
   */
  abstract public function getPageNumber();

  /**
   * Gets the user query.
   *
   * @returns string
   */
  abstract public function getQuery();

  /**
   * Returns the URL format with %s in place of the page.
   *
   * @returns string
   */
  abstract public function getUrlFormat();
}
