<?php
/**
 * This file is part of the sfLucene package.
 * (c) Carl Vondrick <carl.vondrick@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * A result iterator to support lazy unwriting
 *
 * @package sfLucene
 * @subpackage Engine
 * @author Carl Vondrick
 */
final class xfLuceneHits implements SeekableIterator, Countable, Serializable
{
  /**
   * The engine
   *
   * @var xfLuceneEngine
   */
  private $engine;

  /**
   * The found hits
   *
   * @var array
   */
  private $hits = array();

  /**
   * The hit cache
   *
   * @var array
   */
  private $hitCache = array();

  /**
   * The internal pointer
   *
   * @var int
   */
  private $pointer = 0;

  /**
   * Constructor to set initial hits.
   *
   * @param xfLuceneEngine $engine The engine where the hits came from
   * @param array $hits The array of hits of Zend_Search_Lucene_Search_QueryHit
   */
  public function __construct(xfLuceneEngine $engine, array $hits)
  {
    $this->engine = $engine;
    $this->hits = $hits;
  }

  /**
   * Gets the current document from the pointer.
   *
   * @returns xfDocumentHit
   */
  public function current()
  {
    if (!isset($this->hitCache[$this->pointer]))
    {
      $hit = $this->hits[$this->pointer];
      $doc = $this->engine->unwriteDocument($hit->getDocument());

      $hit = new xfDocumentHit($doc, array(
        'score' => $hit->score,
        'id' => $hit->id,
      ));

      $this->hitCache[$this->pointer] = $hit;
    }

    return $this->hitCache[$this->pointer];
  }

  /**
   * Gets the current key
   *
   * @returns int
   */
  public function key()
  {
    return $this->pointer;
  }

  /**
   * Advances the pointer
   */
  public function next()
  {
    $this->pointer++;
  }

  /**
   * Checks to see if the pointer is valid.
   */
  public function valid()
  {
    return isset($this->hits[$this->pointer]);
  }

  /**
   * Resets the pointer
   */
  public function rewind()
  {
    $this->pointer = 0;
  }

  /**
   * Seeks the pointer
   * 
   * @param int $index 
   */
  public function seek($index)
  {
    $this->pointer = (int) $index;
  }

  /**
   * Counts the total hits
   *
   * @returns int
   */
  public function count()
  {
    return count($this->hits);
  }

  /**
   * Magic method for serializing.
   *
   * @returns string The serialization
   */
  public function serialize()
  {
    $data = array();
    $data['engine'] = $this->engine;
    $data['pointer'] = $this->pointer;

    $data['cache'] = array();
    foreach ($this->hits as $hit)
    {
      $data['cache'][$hit->id] = $hit->score;
    }
    
    return serialize($data);
  }

  /**
   * Magic method for unserializing
   *
   * @param string $serialized The serialized string
   */
  public function unserialize($serialized)
  {
    $data = unserialize($serialized);

    $this->engine = $data['engine'];

    foreach ($data['cache'] as $id => $score)
    {
      $hit = new Zend_Search_Lucene_Search_QueryHit($this->engine->getIndex());
      $hit->id = $id;
      $hit->score = $score;

      $this->hits[] = $hit;
    }

    $this->pointer = $data['pointer'];
  }
}
