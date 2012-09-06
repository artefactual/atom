<?php
/**
 * This file is part of the sfLucene package.
 * (c) Carl Vondrick <carl.vondrick@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

// load Zend_Search_Lucene
xfLuceneZendManager::load();

/**
 * The token filter to stem words.
 *
 * @package sfLucene
 * @subpackage Stemmer
 * @author Carl Vondrick
 */
final class xfLuceneStemmerTokenFilter extends Zend_Search_Lucene_Analysis_TokenFilter
{
  /**
   * The stemmer
   *
   * @var xfLuceneStemmer
   */
  private $stemmer;

  /**
   * Constructor to set stemmer.
   *
   * @param xfLuceneStemmer $stemmer
   */
  public function __construct(xfLuceneStemmer $stemmer)
  {
    $this->stemmer = $stemmer;
  }

  /**
   * @see Zend_Search_Lucene_Analysis_TokenFilter
   */
  public function normalize(Zend_Search_Lucene_Analysis_Token $srcToken)
  {
    $text = $this->stemmer->doStem($srcToken->getTermText());

    $newToken = new Zend_Search_Lucene_Analysis_Token(
                                  $text,
                                  $srcToken->getStartOffset(),
                                  //$srcToken->getStartOffset() + strlen($text));
                                  $srcToken->getEndOffset());

    $newToken->setPositionIncrement($srcToken->getPositionIncrement());

    return $newToken;
  }
}
