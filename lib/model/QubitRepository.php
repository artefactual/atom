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
 * @package    AccesstoMemory
 * @subpackage repository
 * @author     Peter Van Garderen <peter@artefactual.com>
 */
class QubitRepository extends BaseRepository
{
  const
    ROOT_ID = 6;

  public function __get($name)
  {
    $args = func_get_args();

    $options = array();
    if (1 < count($args))
    {
      $options = $args[1];
    }

    switch ($name)
    {
      case 'backgroundColor':
      case 'htmlSnippet':

        if (!isset($this->values[$name]))
        {
          $criteria = new Criteria;
          $this->addPropertysCriteria($criteria);
          $criteria->add(QubitProperty::NAME, $name);

          if (1 == count($query = QubitProperty::get($criteria)))
          {
            $this->values[$name] = $query[0];
          }
        }

        if (isset($this->values[$name]))
        {
          return $this->values[$name];
        }

        break;

      default:

        return call_user_func_array(array($this, 'BaseRepository::__get'), $args);
    }
  }

  public function __set($name, $value)
  {
    $args = func_get_args();

    $options = array();
    if (2 < count($args))
    {
      $options = $args[2];
    }

    switch ($name)
    {
      case 'backgroundColor':
      case 'htmlSnippet':

        if (!isset($this->values[$name]))
        {
          $criteria = new Criteria;
          $this->addPropertysCriteria($criteria);
          $criteria->add(QubitProperty::NAME, $name);

          if (1 == count($query = QubitProperty::get($criteria)))
          {
            $this->values[$name] = $query[0];
          }
          else
          {
            $this->values[$name] = new QubitProperty;
            $this->values[$name]->name = $name;
            $this->propertys[] = $this->values[$name];
          }
        }

        $this->values[$name]->__set('value', $value, $options);

        return $this;

      default:

        return call_user_func_array(array($this, 'BaseRepository::__set'), $args);
    }
  }

  public function save($connection = null)
  {
    parent::save($connection);

    QubitSearch::getInstance()->update($this);

    // Trigger updating of associated information objects, if any
    $operationDescription = sfContext::getInstance()->i18n->__('updated');
    $this->updateInformationObjects($this->getRelatedInformationObjectIds(), $operationDescription);

    // Remove adv. search repository options from cache
    QubitCache::getInstance()->removePattern('search:list-of-repositories:*');

    return $this;
  }

  public function getRelatedInformationObjectIds()
  {
    $sql = "SELECT id FROM ". QubitInformationObject::TABLE_NAME ." WHERE repository_id=:repository_id";

    $params = array(':repository_id' => $this->id);

    return QubitPdo::fetchAll($sql, $params, array('fetchMode' => PDO::FETCH_COLUMN));
  }

  public function updateInformationObjects($ioIds, $operationDescription)
  {
    if (empty($ioIds))
    {
      return;
    }

    // Handle web request asynchronously
    $context = sfContext::getInstance();

    if (!in_array($context->getConfiguration()->getEnvironment(), array('cli', 'worker')))
    {
      // Let user know related descriptions update has started
      $jobsUrl = $context->routing->generate(null, array('module' => 'jobs', 'action' => 'browse'));
      $messageParams = array('%1' => $operationDescription, '%2' => $jobsUrl);
      $message = $context->i18n->__('Your repository has been %1. Its related descriptions are being updated asynchronously – check the <a href="%2">job scheduler page</a> for status and details.', $messageParams);
      $context->user->setFlash('notice', $message);

      // Update asynchronously the saved IOs ids
      $jobOptions = array(
        'ioIds' => $ioIds,
        'updateIos' => true,
        'updateDescendants' => true
      );
      QubitJob::runJob('arUpdateEsIoDocumentsJob', $jobOptions);

      return;
    }

    // Handle CLI and worker requests synchronously
    foreach ($ioIds as $id)
    {
      $io = QubitInformationObject::getById($id);
      QubitSearch::getInstance()->update($io, array('updateDescendants' => true));

      // Keep caches clear to prevent memory use from ballooning
      Qubit::clearClassCaches();
    }
  }

  /**
   * Add repository specific logic to the insert action
   *
   * @param mixed $connection The database connection object
   * @return QubitRepository self-reference
   */
  protected function insert($connection = null)
  {
    // When creating a new repository, set the upload_limit to the default
    // value (app_repository_quota)
    if (null == $this->__get('uploadLimit'))
    {
      $this->__set('uploadLimit', sfConfig::get('app_repository_quota'));
    }

    parent::insert($connection);

    return $this;
  }

  /**
   * Additional actions to take on delete
   *
   */
  public function delete($connection = null)
  {
    // Remove adv. search repository options from cache
    QubitCache::getInstance()->removePattern('search:list-of-repositories:*');

    // Get IDs of any associated information objects
    $ioIds = $this->getRelatedInformationObjectIds();

    if (!empty($ioIds))
    {
      // Remove associations between this repository and information objects
      $sql = "UPDATE " . QubitInformationObject::TABLE_NAME . " \r
              SET repository_id=NULL \r
              WHERE repository_id=:repository_id";

      QubitPdo::modify($sql, array(':repository_id' => $this->id));

      // Trigger updating of the information objects
      $operationDescription = sfContext::getInstance()->i18n->__('deleted');
      $this->updateInformationObjects($ioIds, $operationDescription);
    }

    // Events, relations and the Elasticsearch document are deleted in QubitActor
    parent::delete($connection);
  }

  /**
   * Create new related QubitNote
   *
   * @param integer $userId     QubitUser id
   * @param string  $note       Note text
   * @param integer $noteTypeId Type of note (QubitTerm pk)
   */
  public function setRepositoryNote($userId, $note, $noteTypeId)
  {
    $newNote = new QubitNote;
    $newNote->setObjectId($this->id);
    $newNote->setScope('QubitRepository');
    $newNote->setUserId($userId);
    $newNote->setContent($note);
    $newNote->setTypeId($noteTypeId);
    $newNote->save();
  }

  /**
   * Get related notes
   *
   * @return QubitQuery list of QubitNote objects
   */
  public function getRepositoryNotes()
  {
    $criteria = new Criteria;
    $criteria->addJoin(QubitNote::TYPE_ID, QubitTerm::ID);
    $criteria->add(QubitNote::OBJECT_ID, $this->id);
    $criteria->add(QubitNote::SCOPE, 'QubitRepository');
    QubitNote::addOrderByPreorder($criteria);

    return QubitNote::get($criteria);
  }

  /**
   * Get country of primary contact for repository (If one exists)
   *
   * @return string primary contact's country
   */
  public function getCountry()
  {
    if ($this->getCountryCode())
    {
      return format_country($this->getCountryCode());
    }
  }

  /**
   * Get a value from this repository's contact information.
   * This method will first check to see if there is a primary
   * contact and return the field from there if it is set.
   *
   * If there is no primary contact or the primary contact does not
   * have the specified field set, iterate over all contacts and return
   * the first one that has the field set.
   *
   * @param  $getFunction  The get function for the field we want to return.
   *                       e.g. getFromPrimaryOrFirstValidContact('getCity')
   *
   * @return  mixed  Returns the field if found, null otherwise
   */
  private function getFromPrimaryOrFirstValidContact($getFunction, $options)
  {
    $primaryContact = $this->getPrimaryContact();

    if ($primaryContact && $primaryContact->$getFunction($options))
    {
      return $primaryContact->$getFunction($options);
    }

    foreach ($this->getContactInformation() as $contact)
    {
      if ($contact->$getFunction($options))
      {
        return $contact->$getFunction($options);
      }
    }
  }

  public function getCountryCode($options = array())
  {
    return $this->getFromPrimaryOrFirstValidContact('getCountryCode', $options);
  }

  public function getRegion($options = array())
  {
    return $this->getFromPrimaryOrFirstValidContact('getRegion', $options);
  }

  public function getCity($options = array())
  {
    return $this->getFromPrimaryOrFirstValidContact('getCity', $options);
  }

  /**
   * Only find repository objects, not other actor types
   *
   * @param Criteria $criteria current search criteria
   * @return Criteria modified search critieria
   */
  public static function addGetOnlyRepositoryCriteria($criteria)
  {
    $criteria->addJoin(QubitRepository::ID, QubitObject::ID);
    $criteria->add(QubitObject::CLASS_NAME, 'QubitRepository');

    return $criteria;
  }

  public static function addCountryCodeCriteria($criteria, $countryCode)
  {
    if ($countryCode !== null)
    {
      $criteria->addJoin(QubitRepository::ID, QubitContactInformation::ACTOR_ID);
      $criteria->add(QubitContactInformation::PRIMARY_CONTACT, true);
      $criteria->add(QubitContactInformation::COUNTRY_CODE, $countryCode);
    }

    return $criteria;
  }

  /**
   * Return an options_for_select array
   *
   * @param mixed $default current selected value for select list
   * @param array $options optional parameters
   * @return array options_for_select compatible array
   */
  public static function getOptionsForSelectList($default, $options = array())
  {
    $repositories = self::getAll($options);

    foreach ($repositories as $repository)
    {
      // Don't display repositories with no name
      if ($name = $repository->getAuthorizedFormOfName($options))
      {
        $selectOptions[$repository->id] = $name;
      }
    }

    return options_for_select($selectOptions, $default, $options);
  }

  /**
   * Get disk space used by digital objects in this repository
   *
   * @return integer disk usage in bytes
   */
  public function getDiskUsage($options = array())
  {
    $repoDir = sfConfig::get('app_upload_dir').'/r/'.$this->slug;

    if (!file_exists($repoDir))
    {
      return 0;
    }

    $size = Qubit::getDirectorySize($repoDir, $options);
    if ($size < 0)
    {
      $size = 0;
    }

    return $size;
  }


  /**************
  Import methods
  ***************/

  public function setTypeByName($name)
  {
    // See if type term already exists
    $criteria = new Criteria;
    $criteria->addJoin(QubitTerm::ID, QubitTermI18n::ID);
    $criteria->add(QubitTerm::TAXONOMY_ID, QubitTaxonomy::REPOSITORY_TYPE_ID);
    $criteria->add(QubitTermI18n::NAME, $name);

    if (null === $term = QubitTerm::getOne($criteria))
    {
      $term = new QubitTerm;
      $term->setTaxonomyId(QubitTaxonomy::REPOSITORY_TYPE_ID);
      $term->setName($name);
      $term->setRoot();
      $term->save();
    }

    foreach (self::getTermRelations(QubitTaxonomy::REPOSITORY_TYPE_ID) as $item)
    {
      // Faster than $item->term == $term
      if ($item->termId == $term->id)
      {
        return;
      }
    }

    $relation = new QubitObjectTermRelation;
    $relation->term = $term;

    $this->objectTermRelationsRelatedByobjectId[] = $relation;
  }

  public function setThematicAreaByName($name)
  {
    // see if type term already exists
    $criteria = new Criteria;
    $criteria->addJoin(QubitTerm::ID, QubitTermI18n::ID);
    $criteria->add(QubitTerm::TAXONOMY_ID, QubitTaxonomy::THEMATIC_AREA_ID);
    $criteria->add(QubitTermI18n::NAME, $name);

    if (null === $term = QubitTerm::getOne($criteria))
    {
      $term = new QubitTerm;
      $term->setTaxonomyId(QubitTaxonomy::THEMATIC_AREA_ID);
      $term->setName($name);
      $term->setRoot();
      $term->save();
    }

    foreach (self::getTermRelations(QubitTaxonomy::THEMATIC_AREA_ID) as $item)
    {
      // Faster than $item->term == $term
      if ($item->termId == $term->id)
      {
        return;
      }
    }

    $relation = new QubitObjectTermRelation;
    $relation->term = $term;

    $this->objectTermRelationsRelatedByobjectId[] = $relation;
  }

  public function setGeographicSubregionByName($name)
  {
    // see if type term already exists
    $criteria = new Criteria;
    $criteria->addJoin(QubitTerm::ID, QubitTermI18n::ID);
    $criteria->add(QubitTerm::TAXONOMY_ID, QubitTaxonomy::GEOGRAPHIC_SUBREGION_ID);
    $criteria->add(QubitTermI18n::NAME, $name);

    if (null === $term = QubitTerm::getOne($criteria))
    {
      $term = new QubitTerm;
      $term->setTaxonomyId(QubitTaxonomy::GEOGRAPHIC_SUBREGION_ID);
      $term->setName($name);
      $term->setRoot();
      $term->save();
    }

    foreach (self::getTermRelations(QubitTaxonomy::GEOGRAPHIC_SUBREGION_ID) as $item)
    {
      // Faster than $item->term == $term
      if ($item->termId == $term->id)
      {
        return;
      }
    }

    $relation = new QubitObjectTermRelation;
    $relation->term = $term;

    $this->objectTermRelationsRelatedByobjectId[] = $relation;
  }

  /**
   * Get the current repository uploads directory
   *
   * @return string
   */
  public function getUploadsPath($absolute = false)
  {
    return ($absolute ? sfConfig::get('sf_upload_dir') : '/uploads').'/r/'.$this->slug;
  }

  /**
   * Get logo image path within the repository uploads directory
   *
   * @return string
   */
  public function getLogoPath($absolute = false)
  {
    return $this->getUploadsPath($absolute).'/conf/logo.png';
  }

  /**
   * Get banner image path within the repository uploads directory
   *
   * @return string
   */
  public function getBannerPath($absolute = false)
  {
    return $this->getUploadsPath($absolute).'/conf/banner.png';
  }

  /**
   * Check if the logo asset exists
   *
   * @return boolean
   */
  public function existsLogo()
  {
    return is_file($this->getLogoPath(true));
  }

  /**
   * Check if the banner asset exists
   *
   * @return boolean
   */
  public function existsBanner()
  {
    return is_file($this->getBannerPath(true));
  }
}
