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

/**
 * @package    qubit
 * @subpackage repository
 * @author     Peter Van Garderen <peter@artefactual.com>
 */
class QubitRepository extends BaseRepository
{
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
      case 'background':
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
      case 'background':
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

  public function getCountryCode()
  {
    if ($this->getPrimaryContact())
    {
      if ($countryCode = $this->getPrimaryContact()->getCountryCode())
      {
        return $countryCode;
      }
    }
    if (count($contacts = $this->getContactInformation()) > 0)
    {
      foreach ($contacts as $contact)
        {
        if ($countryCode = $contact->getCountryCode())
        {
          return $countryCode;
        }
      }
    }
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
    $du = 0;
    $repoDir = sfConfig::get('app_upload_dir').'/r/'.$this->slug;

    if (!file_exists($repoDir))
    {
      return 0;
    }

    // Derived from http://www.php.net/manual/en/function.filesize.php#94566
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($repoDir));
    foreach ($iterator as $item)
    {
      $du += $item->getSize();
    }

    // Set metric units for return value
    if (isset($options['units']))
    {
      switch (strtolower($options['units']))
      {
        case 'g':
          $du /= pow(10, 3);
        case 'm':
          $du /= pow(10, 3);
        case 'k':
          $du /= pow(10, 3);
      }

      $du = round($du, 2);
    }

    return $du;
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
    return $this->getUploadsPath($absolute).'/logo.png';
  }

  /**
   * Get banner image path within the repository uploads directory
   *
   * @return string
   */
  public function getBannerPath($absolute = false)
  {
    return $this->getUploadsPath($absolute).'/banner.png';
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
