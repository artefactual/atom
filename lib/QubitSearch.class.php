<?php

/*
 * This file is part of Qubit Toolkit.
 *
 * Qubit Toolkit is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Qubit Toolkit is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Qubit Toolkit.  If not, see <http://www.gnu.org/licenses/>.
 */

class QubitSearch extends xfIndexSingle
{
  // allow disabling search index via boolean flag
  public $disabled = false;

  /*
   * Enable singleton creation via getInstance()
   */
  protected static
    $_instance,
    $conn,
    $statements,
    $counter = 0;

  public static function getInstance()
  {
    if (null === self::$_instance)
    {
      // If the ElasticSearch plugin is enabled, use that instead
      if (in_array('qtElasticSearchPlugin', sfConfig::get('sf_enabled_modules')))
      {
        self::$_instance = new qtElasticSearchPlugin();
      }
      else
      {
        self::$_instance = new self();
      }
    }

    return self::$_instance;
  }

  public function parse($query)
  {
    // Parse query string
    $query = Zend_Search_Lucene_Search_QueryParser::parse($query, 'UTF-8');

    if ($query instanceOf Zend_Search_Lucene_Search_Query_Insignificant) {
      throw new Exception('No search terms specified.');
    }
    else if ($query instanceOf Zend_Search_Lucene_Search_Query_MultiTerm) {
      throw new Exception('Error parsing search terms.');
    }

    return $query;
  }

  public function addTerm($text, $field)
  {
    return new Zend_Search_Lucene_Search_Query_Term(new Zend_Search_Lucene_Index_Term($text, $field));
  }

  /**
   * @see xfIndex
   */
  protected function initialize()
  {
    if (self::$_instance instanceof qtElasticSearchPlugin)
    {
      return;
    }

    $this->setEngine(new xfLuceneEngine(sfConfig::get('sf_data_dir').'/index'));
    $this->getEngine()->open();

    // Sync sfLucenePlugin->_anaylzer and ZSL default analyzer
    Zend_Search_Lucene_Analysis_Analyzer::setDefault($this->getEngine()->getAnalyzer());

    // Default to "AND" searches
    Zend_Search_Lucene_Search_QueryParser::setDefaultOperator(Zend_Search_Lucene_Search_QueryParser::B_AND);
  }

  /**
   * @see xfIndex
   */
  public function qubitPopulate($options)
  {
    if (self::getInstance() instanceof qtElasticSearchPlugin)
    {
      self::getInstance()->logger = $this->getLogger();
      self::getInstance()->qubitPopulate($options);
      return;
    }

    if (!isset(self::$conn))
    {
      self::$conn = Propel::getConnection();
    }

    $this->timer = new QubitTimer;
    $this->getLogger()->log('Populating index...', $this->getName());

    // If doing full re-index, then delete previous data
    if (!isset($options['skip']))
    {
      $this->getEngine()->erase();
      $this->getLogger()->log('Index erased.', $this->getName());
    }
    else
    {
      $this->optimize();
    }

    // set buffering and updates to be batched for better performance
    // NB: not sure why this doesn't work in object scope
    self::getInstance()->getEngine()->enableBatchMode();

    // index actors
    if (!isset($options['skip']) || 'actors' != $options['skip'])
    {
      // Get count of all actors
      $sql = 'SELECT COUNT(*) from '.QubitActor::TABLE_NAME;
      $rs = self::$conn->query($sql);
      $rowcount = $rs->fetchColumn(0);

      // Loop through results, and add to search index
      foreach (QubitActor::getAll() as $key => $actor)
      {
        self::addActorIndex($actor);

        $this->getLogger()->log('QubitActor - "'.$actor->__toString().'" inserted ('.$this->timer->elapsed().'s) ('.($key + 1).'/'.$rowcount.')', $this->getName());
      }
    }
    else
    {
      $this->getLogger()->log('Skipping actors.');
    }

    // index information objects
    if (!isset($options['skip']) || 'io' != $options['skip'])
    {
      $this->populateInformationObjects();
    }
    else
    {
      $this->getLogger()->log('Skip information objects.');
    }

    // index accessions
    if (!isset($options['skip']) || 'accessions' != $options['skip'])
    {
      // Get count of all actors
      $sql = 'SELECT COUNT(*) from '.QubitAccession::TABLE_NAME;
      $rs = self::$conn->query($sql);
      $rowcount = $rs->fetchColumn(0);

      // Loop through results, and add to search index
      foreach (QubitAccession::getAll() as $key => $actor)
      {
        self::addAccessionIndex($actor);

        $this->getLogger()->log('QubitAccession - "'.$actor->__toString().'" inserted ('.$this->timer->elapsed().'s) ('.($key + 1).'/'.$rowcount.')', $this->getName());
      }
    }
    else
    {
      $this->getLogger()->log('Skip accessions.');
    }

    $this->getLogger()->log('Index populated in "'.$this->timer->elapsed().'" seconds.', $this->getName());
  }

  public function optimize()
  {
    if (self::getInstance() instanceof qtElasticSearchPlugin)
    {
      return;
    }

    $timer = new QubitTimer;
    $this->getLogger()->log('Optimizing index...', $this->getName());
    $this->getEngine()->optimize();
    $this->getLogger()->log('Index optimized in "'.$timer->elapsed().'" seconds.', $this->getName());
  }

  public function enableBatch()
  {
    $this->getEngine()->enableBatchMode();
  }

  public function disableBatch()
  {
    $this->getEngine()->disableBatchMode();
  }

  /*
   * ======================================================================
   * In lieu of a service registry (the "right" way to implement these methods)
   * we have engine-specific handling below here; these are based on Zend Lucene
   * ======================================================================
   */

  /**
   * Delete an existing document from the index
   *
   * @param integer $id object identifier
   * @return void
   */
  public static function deleteById($id)
  {
    // have to use another search object to perform the querying
    $querier = new QubitSearch();
    $query = new Zend_Search_Lucene_Search_Query_Term(new Zend_Search_Lucene_Index_Term($id, 'id'));

    foreach ($querier->getEngine()->getIndex()->find($query) as $hit)
    {
      self::getInstance()->getEngine()->getIndex()->delete($hit->id);
    }
  }

  /**
   * Delete an existing document from the index
   *
   * @param integer $id object identifier
   * @param string $language ISO-639-1 code
   * @return void
   */
  public static function deleteByIdLanguage($id, $language)
  {
    // have to use another search object to perform the querying
    $querier = new QubitSearch();
    $query = new Zend_Search_Lucene_Search_Query_MultiTerm;
    $query->addTerm(new Zend_Search_Lucene_Index_Term($id, 'id'), true);
    $query->addTerm(new Zend_Search_Lucene_Index_Term($language, 'culture'), true);

    foreach ($querier->getEngine()->getIndex()->find($query) as $hit)
    {
      self::getInstance()->getEngine()->getIndex()->delete($hit->id);
    }
  }

  /**
   * Get list of currently enabled languages from config
   *
   * @return array enabled language codes and names
   */
  public static function getEnabledI18nLanguages()
  {
    //determine the currently enabled i18n languages
    $enabledI18nLanguages = array();

    foreach (sfConfig::getAll() as $setting => $value)
    {
      if (0 === strpos($setting, 'app_i18n_languages'))
      {
        $enabledI18nLanguages[substr($setting, 19)] = $value;
      }
    }

    return $enabledI18nLanguages;
  }

  /**
   * Get translated language codes
   *
   * @return array list of translated languages (ISO-639-1 code)
   */
  public static function getTranslatedLanguages(&$object)
  {
    if (!isset(self::$conn))
    {
      self::$conn = Propel::getConnection();
    }

    // If this class has an i18n table
    if (class_exists(get_class($object).'I18n'))
    {
      $i18nTableName = constant(get_class($object).'I18n::TABLE_NAME');

      $stmt = self::$conn->prepare('SELECT culture FROM '.$i18nTableName.' WHERE id = ? GROUP BY culture');
      $stmt->execute(array($object->id));

      while ($row = $stmt->fetch())
      {
        $translatedLanguages[] = $row['culture'];
      }

      $stmt->closeCursor();
      self::$conn->clearStatementCache();
    }
    else
    {
      $translatedLanguages = self::getEnabledI18nLanguages();
    }

    return $translatedLanguages;
  }

  /*
   * ======================================================================
   * TERMS
   * ======================================================================
   */

  public static function updateTermIndex($term)
  {
    if (self::getInstance()->disabled)
    {
      return;
    }
    else if ($term::ROOT_ID == $term->id)
    {
      // Don't index root object
      return;
    }
  }

  /*
   * ======================================================================
   * ACTORS
   * ======================================================================
   */

  public static function updateActorIndex($actor)
  {
    if (self::getInstance()->disabled)
    {
      return;
    }
    else if ($actor::ROOT_ID == $actor->id)
    {
      // Don't index root object
      return;
    }

    self::deleteById($actor->id);
    self::addActorIndex($actor);
  }

  public static function addActorIndex(QubitActor $actor)
  {
    // Don't index root object
    if ($actor::ROOT_ID == $actor->id)
    {
      return;
    }

    foreach ($actor->actorI18ns as $actorI18n)
    {
      $doc = new Zend_Search_Lucene_Document;

      $doc->addField(Zend_Search_Lucene_Field::Keyword('id', $actor->id));
      $doc->addField(Zend_Search_Lucene_Field::Keyword('className', $actor->className));
      $doc->addField(Zend_Search_Lucene_Field::Keyword('culture', $actorI18n->culture));

      $doc->addField(Zend_Search_Lucene_Field::UnStored('identifier', $actor->descriptionIdentifier));

      // Boost authorized form of name
      $field = Zend_Search_Lucene_Field::UnStored('authorizedFormOfName', $actorI18n->authorizedFormOfName);
      $field->boost = 10;
      $doc->addField($field);

      // Boost dates of existence
      $field = Zend_Search_Lucene_Field::UnStored('datesOfExistence', $actorI18n->datesOfExistence);
      $field->boost = 5;
      $doc->addField($field);

      // Boost history
      $history = Zend_Search_Lucene_Field::UnStored('history', $actorI18n->history);
      $history->boost = 3;
      $doc->addField($history);

      $doc->addField(Zend_Search_Lucene_Field::UnStored('places', $actorI18n->places));
      $doc->addField(Zend_Search_Lucene_Field::UnStored('legalStatus', $actorI18n->legalStatus));
      $doc->addField(Zend_Search_Lucene_Field::UnStored('functions', $actorI18n->functions));
      $doc->addField(Zend_Search_Lucene_Field::UnStored('mandates', $actorI18n->mandates));
      $doc->addField(Zend_Search_Lucene_Field::UnStored('internalStructures', $actorI18n->internalStructures));
      $doc->addField(Zend_Search_Lucene_Field::UnStored('generalContext', $actorI18n->generalContext));

      // Add other forms of name for this culture
      if (!isset($otherNameStmt))
      {
        $conn = Propel::getConnection();
        $sql = 'SELECT i18n.name
           FROM other_name JOIN other_name_i18n i18n ON other_name.id = i18n.id
           WHERE object_id = ? and culture = ?';
        $otherNameStmt = $conn->prepare($sql);
      }
      $otherNameStmt->execute(array($actor->id, $actorI18n->culture));

      $otherNames = array();
      while ($otherName = $otherNameStmt->fetchColumn())
      {
        $otherNames[] = $otherName;
      }

      if (0 < count($otherNames))
      {
        $field = Zend_Search_Lucene_Field::UnStored('otherFormsOfName', implode(' ', $otherNames));
      }
      else
      {
        $field = Zend_Search_Lucene_Field::UnStored('otherFormsOfName', null);
      }

      // Boost other names
      $field->boost = 8;
      $doc->addField($field);

      // QubitRepository fields
      if ('QubitRepository' == $actor->className)
      {
        foreach ($actor->repositoryI18ns as $repositoryI18n)
        {
          if ($actorI18n->culture != $repositoryI18n->culture)
          {
            continue;
          }

          $doc->addField(Zend_Search_Lucene_Field::UnStored('holdings', $repositoryI18n->holdings));
          $doc->addField(Zend_Search_Lucene_Field::UnStored('collectingPolicies', $repositoryI18n->collectingPolicies));

          break;
        }
      }

      self::getInstance()->getEngine()->getIndex()->addDocument($doc);
    }
  }

  /*
   * ======================================================================
   * ACCESSIONS
   * ======================================================================
   */

  public static function updateAccessionIndex(QubitAccession $accession)
  {
    if (self::getInstance()->disabled)
    {
      return;
    }

    self::deleteById($accession->id);
    self::addAccessionIndex($accession);
  }

  public static function addAccessionIndex(QubitAccession $accession)
  {
    if (self::getInstance()->disabled)
    {
      return;
    }

    foreach ($accession->accessionI18ns as $accessionI18n)
    {
      $doc = new Zend_Search_Lucene_Document;

      $doc->addField(Zend_Search_Lucene_Field::Keyword('id', $accession->id));
      $doc->addField(Zend_Search_Lucene_Field::Keyword('className', $accession->className));
      $doc->addField(Zend_Search_Lucene_Field::Keyword('culture', $accession->culture));

      $doc->addField(Zend_Search_Lucene_Field::UnStored('identifier', $accession->identifier));

      if (isset($accession->date))
      {
        $date = new DateTime($accession->date);
        $doc->addField(Zend_Search_Lucene_Field::Unstored('date', $date->format('Ymd')));
      }

      $doc->addField(Zend_Search_Lucene_Field::UnStored('appraisal', $accessionI18n->appraisal));
      $doc->addField(Zend_Search_Lucene_Field::UnStored('archivalHistory', $accessionI18n->archivalHistory));
      $doc->addField(Zend_Search_Lucene_Field::UnStored('locationInformation', $accessionI18n->locationInformation));
      $doc->addField(Zend_Search_Lucene_Field::UnStored('physicalCharacteristics', $accessionI18n->physicalCharacteristics));
      $doc->addField(Zend_Search_Lucene_Field::UnStored('processingNotes', $accessionI18n->processingNotes));
      $doc->addField(Zend_Search_Lucene_Field::UnStored('receivedExtentUnits', $accessionI18n->receivedExtentUnits));
      $doc->addField(Zend_Search_Lucene_Field::UnStored('scopeAndContent', $accessionI18n->scopeAndContent));
      $doc->addField(Zend_Search_Lucene_Field::UnStored('sourceOfAcquisition', $accessionI18n->sourceOfAcquisition));
      $doc->addField(Zend_Search_Lucene_Field::UnStored('title', $accessionI18n->title));

      // Donors
      $criteria = new Criteria;
      $criteria->addJoin(QubitRelation::OBJECT_ID, QubitActorI18n::ID);
      $criteria->addJoin(QubitRelation::OBJECT_ID, QubitDonor::ID);
      $criteria->add(QubitActorI18n::CULTURE, $accessionI18n->culture);
      $criteria->add(QubitRelation::TYPE_ID, QubitTerm::DONOR_ID);
      $criteria->add(QubitRelation::SUBJECT_ID, $accession->id);

      if (0 < count($donorI18ns = QubitActorI18n::get($criteria)))
      {
        foreach ($donorI18ns as $donorI18n)
        {
          $donors[] = $donorI18n->authorizedFormOfName;
        }

        $doc->addField(Zend_Search_Lucene_Field::UnStored('donors', implode(' ', $donors)));
      }

      // Deaccessions
      $criteria = new Criteria;
      $criteria->add(QubitDeaccession::ACCESSION_ID, $accession->id);

      if (0 < count($items = QubitDeaccession::get($criteria)))
      {
        foreach ($items as $deaccession)
        {
          $deaccessions[] = $deaccession->__toString();
        }

        $doc->addField(Zend_Search_Lucene_Field::UnStored('deaccessions', implode(' ', $deaccessions)));
      }

      self::getInstance()->getEngine()->getIndex()->addDocument($doc);
    }
  }

  /*
   * ======================================================================
   * INFORMATION OBJECTS
   * ======================================================================
   */

  public static function deleteInformationObject($informationObject, $options = array())
  {
    self::deleteById($informationObject->id);
  }

  public static function updateInformationObject($informationObject, $options = array())
  {
    if (self::getInstance()->disabled)
    {
      return;
    }
    else if (null === $informationObject->parent)
    {
      // Only ROOT node should have no parent, don't index
      return;
    }

    if (0 < count($languages = self::getTranslatedLanguages($informationObject)))
    {
      foreach ($languages as $language)
      {
        self::updateInformationObjectIndex($informationObject, $language, $options);
      }
    }
  }

  public static function updateInformationObjectIndex(QubitInformationObject $informationObject, $language, $options = array())
  {
    if (self::getInstance()->disabled)
    {
      return;
    }
    else if (null === $informationObject->parent)
    {
      // Only ROOT node should have no parent, don't index
      return;
    }

    self::deleteByIdLanguage($informationObject->id, $language);

    $node = new QubitSearchInformationObject($informationObject->id, $language, $options);
    $node->addToIndex();
  }

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
    $this->recursivelyAddInformationObjects(QubitInformationObject::ROOT_ID, $totalRows);
  }

  public function recursivelyAddInformationObjects($parentId, $totalRows, $options = array())
  {
    // Get information objects
    if (!isset(self::$statements['getChildren']))
    {
      $sql  = 'SELECT
                  io.id,
                  io.lft,
                  io.rgt,
                  i18n.culture,
                  i18n.title';
      $sql .= ' FROM '.QubitInformationObject::TABLE_NAME.' io';
      $sql .= ' JOIN '.QubitInformationObjectI18n::TABLE_NAME.' i18n
                  ON io.id = i18n.id';
      $sql .= ' WHERE io.parent_id = ?';
      $sql .= ' ORDER BY io.lft';

      self::$statements['getChildren'] = self::$conn->prepare($sql);
    }

    self::$statements['getChildren']->execute(array($parentId));

    // Loop through results, and add to search index
    foreach (self::$statements['getChildren']->fetchAll(PDO::FETCH_OBJ) as $item)
    {
      // Add resource to index
      $node = new QubitSearchInformationObject($item->id, $item->culture, $options);
      $node->addToIndex();

      // Log it
      self::$counter++;
      $this->getLogger()->log('QubitInformationObject - "'.$item->title.'" inserted ('.$this->timer->elapsed().'s) ('.self::$counter.'/'.$totalRows.')');

      // Descend hierarchy
      if (1 < ($item->rgt - $item->lft))
      {
        // Pass ancestors and repository down to descendants
        $this->recursivelyAddInformationObjects($item->id, $totalRows, array(
          'ancestors'  => array_merge($node->getAncestors(), array($node)),
          'repository' => $node->getRepository()));
      }
    }
  }
}
