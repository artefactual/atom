<?php
/**
 * This file is part of the sfSearch package.
 * (c) Carl Vondrick <carl.vondrick@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once 'engine/xfEngine.interface.php';

/**
 * A mock engine.
 *
 * @package xfSearch
 * @subpackage Mock
 */
class xfMockEngine implements xfEngine
{
  public $open = false, $documents = array(), $optimized = 0;

  public function open()
  {
    $this->open = true;
  }

  public function close()
  {
    $this->open = false;
  }

  public function erase()
  {
    $this->documents = array();
  }

  public function find(xfCriterion $query)
  {
    $hits = array();

    foreach ($this->documents as $result)
    {
      $hits[] = new xfDocumentHit($result);
    }

    return new ArrayIterator($hits);
  }

  public function findGuid($guid)
  {
    return $this->documents[$guid];
  }

  public function count()
  {
    return count($this->documents);
  }

  public function add(xfDocument $doc)
  {
    $this->documents[$doc->getGuid()] = $doc;
  }

  public function delete($guid)
  {
    unset($this->documents[$guid]);
  }

  public function optimize()
  {
    $this->optimized++;
  }

  public function describe()
  {
    return array(
      'Engine' => 'Mock vINF',
    );
  }

  public function getDocuments()
  {
    return $this->documents;
  }

  public function id()
  {
    return 'mock';
  }
}
