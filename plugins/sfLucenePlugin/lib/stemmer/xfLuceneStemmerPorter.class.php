<?php
/**
 * This file is part of the sfLucene package.
 * (c) Carl Vondrick <carl.vondrick@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * A stemmer wrapper for the Martin Porter stemming algorithm.  Algorithm works
 * for standard English only.
 *
 * PHP implementation by Richard Heyes.
 *
 * More information: http://tartarus.org/martin/PorterStemmer/
 *
 * @package sfLucene
 * @subpackage Stemmer
 * @author Martin Porter
 * @author Richard Heyes
 * @author Carl Vondrick
 */
final class xfLuceneStemmerPorter implements xfLuceneStemmer
{
  /**
   * @see xfLuceneStemmer
   */
  public function doStem($word)
  {
    return PorterStemmer::stem($word);
  }
}
