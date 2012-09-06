<?php
/**
 * This file is part of the sfLucene package.
 * (c) Carl Vondrick <carl.vondrick@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * The stemmer wrapper interface.
 *
 * @package sfLucene
 * @subpackage Stemmer
 * @author Carl Vondrick
 */
interface xfLuceneStemmer
{
  /**
   * Stems a word.
   *
   * @param string $word
   * @returns the stem
   */
  public function doStem($word);
}
