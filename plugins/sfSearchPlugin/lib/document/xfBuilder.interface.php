<?php
/**
 * This file is part of the sfSearch package.
 * (c) Carl Vondrick <carl.vondrick@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Interface for a document builder.
 *
 * @package sfSearch
 * @subpackage Document
 * @author Carl Vondrick
 */
interface xfBuilder
{
  /**
   * Builds onto a document.
   *
   * @param mixed $input The input
   * @param xfDocument $doc The document
   * @returns xfDocument
   */
  public function build($input, xfDocument $doc);
}
