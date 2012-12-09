<?php

/*
 * This file is part of the Access to Memory (AtoM) software.
 *
 * Access to Memory (AtoM) is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Access to Memory (AtoM) is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Access to Memory (AtoM).  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * This class is used to provide a singleton object that uses an ElasticSearch instance for
 * application indexing, querying, faceting, etc.
 *
 * @package    arElasticSearchPlugin
 * @author     MJ Suhonos <mj@suhonos.ca>
 */
class arElasticSearchPlugin
{
  // allow disabling search index via boolean flag
  public $disabled = false;

  // allow modifying the batch size
  public $batchSize;

  private $batchMode = false;
  private $batchDocs = array();

  public $index = null;

  // Enable singleton creation via getInstance()
  protected static
    $_instance,
    $conn,
    $statements,
    $counter = 0;

  public static function getInstance()
  {
    if (null === self::$_instance)
    {
      self::$_instance = new self();
    }

    return self::$_instance;
  }

  // constructor
  public function __construct()
  {
    $this->batchSize = arElasticSearchPluginConfiguration::$batchSize;

    // I don't understand how a heart is a spade
    // But somehow the vital connection is made
    $client = new Elastica_Client(arElasticSearchPluginConfiguration::$server);
    $this->index = $client->getIndex(arElasticSearchPluginConfiguration::$index);

    $this->initialize();
  }

  // destructor
  public function __destruct()
  {
    // if there are still documents in the batch queue, send them
    if ($this->batchMode && count($this->batchDocs) > 0)
    {
      $this->index->addDocuments($this->batchDocs);
      $this->index->flush();
    }

    // I don't understand how the last card is played
    // But somehow the vital connection is made
    $this->index->refresh();
  }

  protected function initialize()
  {
    try
    {
      $this->index->open();
    }
    catch (Exception $e)
    {
      // If the index has not been initialized, create it
      if ($e instanceof Elastica_Exception_Response)
      {
        $this->index->create(array(), true);
      }

      // Apply type mappings for each indexed object type
      // TODO: can load these dynamically from the ./model directory
      foreach (array('QubitInformationObject', 'QubitActor', 'QubitTerm', 'QubitRepository') as $type)
      {
        $mapping = new Elastica_Type_Mapping();

        $mapping->setType($this->index->getType($type));
        $mapping->setProperties(call_user_func(array($type . 'Mapping', 'getProperties')));

        $mapping->send();
      }
    }
  }

  /*
   * Elastica methods
   */
  public function save($object)
  {
    $type = get_class($object);

    if (!class_exists($type . 'Mapping'))
    {
      return;
    }

    $document = new Elastica_Document($object->id, $this->serialize($object));
    $document->setType($type);

    if ($this->batchMode)
    {
      // add this document to the batch queue
      $this->batchDocs[] = $document;

      // if we have a full batch, send in bulk
      if (count($this->batchDocs) >= $this->batchSize)
      {
        $this->index->addDocuments($this->batchDocs);
        $this->index->flush();

        $this->batchDocs = array();
      }
    }
    else
    {
      $this->index->getType($type)->addDocument($document);
      $this->index->flush();
    }
  }

  public function delete($object)
  {
    $this->index->getType(get_class($object))->deleteById($object->id);
  }

  public function parse($querystring)
  {
    if (empty($querystring))
    {
      throw new Exception(sfContext::getInstance()->i18n->__('No search terms specified.'));
    }

    $query = new Elastica_Query_QueryString($querystring);
    $query->setDefaultOperator('AND');

    return $query;
  }

  /*
   * ZSL compatibility methods
   */
  public function optimize()
  {
    $this->index->optimize();
  }

  public function enableBatch()
  {
    $this->batchMode = true;
  }

  public function disableBatch()
  {
    $this->batchMode = false;
  }

  public function deleteById($id)
  {
    // TODO: handle QubitInformationObject objects?
    $this->index->getType('QubitActor')->deleteById($id);
  }

  /*
   * ======= END ZSL compatibility methods
   */

  public function qubitPopulate($options)
  {
    sfContext::createInstance(sfProjectConfiguration::getApplicationConfiguration('qubit', 'cli', true));

    // if we are skipping existing objects, optimize the index instead of deleting
    if (!isset($options['skip']))
    {
      $this->index->delete();
      $this->initialize();
      $this->logger->log('Index erased.', 'arElasticSearch');
    }
    else
    {
      $skips = explode(',', $options['skip']);
      $this->optimize();
    }

    // set buffering and updates to be batched for better performance
    $this->enableBatch();

    $this->timer = new QubitTimer;
    $this->logger->log('Populating index...', 'arElasticSearch');
    $total = 0;

    // repositories
    if (!in_array('repos', $skips))
    {
      self::$counter = 0;
      $this->logger->log('Indexing Repositories...', 'arElasticSearch');

      $criteria = new Criteria;
      $criteria->add(QubitRepository::ID, QubitRepository::ROOT_ID, Criteria::NOT_EQUAL);
      $repositories = QubitRepository::get($criteria);
      $total = $total + count($repositories);

      foreach ($repositories as $key => $repository)
      {
        $this->save($repository);

        if ($options['verbose'])
        {
          $this->logger->log('QubitRepository "'.$repository->__toString().'" inserted ('.$this->timer->elapsed().'s) ('.($key + 1).'/'.count($repositories).')', 'arElasticSearch');
        }
      }
    }

    // information objects
    if (!in_array('ios', $skips))
    {
      self::$counter = 0;
      $this->logger->log('Indexing Information Objects...', 'arElasticSearch');
      $total = $total + $this->populateInformationObjects($options);
    }

    // terms
    if (!in_array('terms', $skips))
    {
      self::$counter = 0;
      $this->logger->log('Indexing Terms...', 'arElasticSearch');
      $total = $total + $this->addTerms($options);
    }

    // actors
    if (!in_array('actors', $skips))
    {
      self::$counter = 0;
      $this->logger->log('Indexing Actors...', 'arElasticSearch');
      $total = $total + $this->addActors($options);
    }

    // if there are still documents in the batch queue, send them
    if ($this->batchMode && count($this->batchDocs) > 0)
    {
      $this->index->addDocuments($this->batchDocs);
      $this->index->flush();
      $this->batchDocs = array();
    }

    $this->logger->log('Index populated with "'.($total).'" documents in "'.$this->timer->elapsed().'" seconds.', 'arElasticSearch');
  }

  /*
  * NB: object classes should implement a static ::serialize() method, which returns a
  * JSON-encoded string of the multi-array compatible with the PHP Serializable interface
  *
  * http://php.net/manual/en/class.serializable.php
  */
  public function serialize($object)
  {
    // take an object and return an associative multidimensional array of properties
    if (class_exists(get_class($object) . 'Mapping'))
    {
      $serialized = call_user_func_array(array(get_class($object) . 'Mapping', 'serialize'), array($object));

      // TODO: trim empty/null/blank elements in the array
      return $serialized;
    }
  }

  /*
   * PORTED FROM QUBITSEARCH CLASS
   */

  public function populateInformationObjects($options = array())
  {
    if (!isset(self::$conn))
    {
      self::$conn = Propel::getConnection();
    }

    // Get count of all information objects
    $sql  = 'SELECT COUNT(*)';
    $sql .= ' FROM '.QubitInformationObject::TABLE_NAME;
    $sql .= ' WHERE id > ?';

    $totalRows = QubitPdo::fetchColumn($sql, array(QubitInformationObject::ROOT_ID));

    // Recursively descend down hierarchy
    $this->recursivelyAddInformationObjects(QubitInformationObject::ROOT_ID, $totalRows, $options);

    return $totalRows;
  }

  public function recursivelyAddInformationObjects($parentId, $totalRows, $options = array())
  {
    // Get information objects
    if (!isset(self::$statements['getChildren']))
    {
      $sql  = 'SELECT
                  io.id,
                  io.lft,
                  io.rgt';
      $sql .= ' FROM '.QubitInformationObject::TABLE_NAME.' io';
      $sql .= ' WHERE io.parent_id = ?';
      $sql .= ' ORDER BY io.lft';

      self::$statements['getChildren'] = self::$conn->prepare($sql);
    }

    self::$statements['getChildren']->execute(array($parentId));

    // Loop through results, and add to search index
    foreach (self::$statements['getChildren']->fetchAll(PDO::FETCH_OBJ) as $item)
    {
      $object = new QubitPdoInformationObject($item->id, $options);

      $serialized = $object->serialize();
/*
      if ($comp = $this->array_compare($this->serialize(QubitInformationObject::getById($item->id)), $serialized))
      {
        // WARNING: PDO object is not serialized correctly
        echo '=== QubitInformationObject #'.$item->id.': '."\n";
        echo var_dump($comp[0]);
        echo '=== QubitPdoInformationObject #'.$item->id.': '."\n";
        echo var_dump($comp[1]);
      }
*/
      $document = new Elastica_Document($item->id, $serialized);
      $document->setType('QubitInformationObject');

      // add this document to the batch queue
      $this->batchDocs[] = $document;

      // if we have a full batch, send in bulk
      if (count($this->batchDocs) >= $this->batchSize)
      {
        $this->index->addDocuments($this->batchDocs);
        $this->index->flush();

        $this->batchDocs = array();
      }

      // Log it
      self::$counter++;

      if ($options['verbose'])
      {
        $this->logger->log('QubitInformationObject "#'.$item->id.'" inserted ('.$this->timer->elapsed().'s) ('.self::$counter.'/'.$totalRows.')', 'arElasticSearch');
      }

      // Descend hierarchy
      if (1 < ($item->rgt - $item->lft))
      {
        // Pass ancestors and repository down to descendants
        $this->recursivelyAddInformationObjects($item->id, $totalRows, array(
          'ancestors'  => array_merge($object->getAncestors(), array($object)),
          'repository' => $object->getRepository(),
          'verbose' => $options['verbose']));
      }

    }
  }

  public function addTerms($options = array())
  {
    if (!isset(self::$conn))
    {
      self::$conn = Propel::getConnection();
    }

    $sql  = 'SELECT term.id';
    $sql .= ' FROM '.QubitTerm::TABLE_NAME.' term';
    $sql .= ' JOIN '.QubitObject::TABLE_NAME.' object ON (term.id = object.id)';
    $sql .= ' WHERE term.taxonomy_id IN (:subject, :place)';
    $sql .= ' AND term.id != '.QubitTerm::ROOT_ID;

    $terms = QubitPdo::fetchAll($sql, array(
      ':subject' => QubitTaxonomy::SUBJECT_ID,
      ':place' => QubitTaxonomy::PLACE_ID));
    $numRows = count($terms);

    // Loop through results, and add to search index
    foreach ($terms as $item)
    {
      $term = QubitTerm::getById($item->id);
      $this->save($term);

      // Log it
      self::$counter++;

      if ($options['verbose'])
      {
        $this->logger->log('QubitTerm "#'.$item->id.'" inserted ('.$this->timer->elapsed().'s) ('.self::$counter.'/'.$numRows.')', 'arElasticSearch');
      }
    }

    return $numRows;
  }

  public function addActors($options = array())
  {
    if (!isset(self::$conn))
    {
      self::$conn = Propel::getConnection();
    }

    $sql  = 'SELECT
                  actor.id';
    $sql .= ' FROM '.QubitActor::TABLE_NAME.' actor';
    $sql .= ' JOIN '.QubitObject::TABLE_NAME.' object';
    $sql .= ' ON actor.id = object.id';
    $sql .= ' WHERE actor.id != ?';
    $sql .= ' AND object.class_name = ?';
    $sql .= ' ORDER BY actor.lft';

    $actors = QubitPdo::fetchAll($sql, array(QubitActor::ROOT_ID, 'QubitActor'));
    $numRows = count($actors);

    // Loop through results, and add to search index
    foreach ($actors as $item)
    {
      $object = new QubitPdoActor($item->id);

      $serialized = $object->serialize();
/*
      if ($comp = $this->array_compare($this->serialize(QubitActor::getById($item->id)), $serialized))
      {
        // WARNING: PDO object is not serialized correctly
        echo '=== QubitActor #'.$item->id.': '."\n";
        echo var_dump($comp[0]);
        echo '=== QubitPdoActor #'.$item->id.': '."\n";
        echo var_dump($comp[1]);
      }
*/
      $document = new Elastica_Document($item->id, $serialized);
      $document->setType('QubitActor');

      // add this document to the batch queue
      $this->batchDocs[] = $document;

      // if we have a full batch, send in bulk
      if (count($this->batchDocs) >= $this->batchSize)
      {
        $this->index->addDocuments($this->batchDocs);
        $this->index->flush();

        $this->batchDocs = array();
      }

      // Log it
      self::$counter++;

      if ($options['verbose'])
      {
        $this->logger->log('QubitActor "#'.$item->id.'" inserted ('.$this->timer->elapsed().'s) ('.self::$counter.'/'.$numRows.')', 'arElasticSearch');
      }
    }

    return $numRows;
  }

  public function array_compare($array1, $array2)
  {
    $diff = false;

    // Left-to-right
    foreach ($array1 as $key => $value)
    {
      if (!array_key_exists($key,$array2))
      {
        $diff[0][$key] = $value;
      }
      else if (is_array($value))
      {
        if (!is_array($array2[$key]))
        {
          $diff[0][$key] = $value;
          $diff[1][$key] = $array2[$key];
        }
        else
        {
          $new = $this->array_compare($value, $array2[$key]);
          if ($new !== false)
          {
            if (isset($new[0])) $diff[0][$key] = $new[0];
            if (isset($new[1])) $diff[1][$key] = $new[1];
          }
        }
      }
      else if ($array2[$key] !== $value)
      {
        $diff[0][$key] = $value;
        $diff[1][$key] = $array2[$key];
      }
    }

    // Right-to-left
    foreach ($array2 as $key => $value)
    {
      if (!array_key_exists($key,$array1))
      {
        $diff[1][$key] = $value;
      }

      // No direct comparsion because matching keys were compared in the
      // left-to-right loop earlier, recursively.
    }

    return $diff;
  }
}
