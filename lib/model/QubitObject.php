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

class QubitObject extends BaseObject implements Zend_Acl_Resource_Interface
{
  public function __isset($name)
  {
    $args = func_get_args();

    switch ($name)
    {
      case 'slug':

        if (!array_key_exists('slug', $this->values))
        {
          $connection = Propel::getConnection(QubitObject::DATABASE_NAME);

          $statement = $connection->prepare('
            SELECT '.QubitSlug::SLUG.'
            FROM '.QubitSlug::TABLE_NAME.'
            WHERE ? = '.QubitSlug::OBJECT_ID);
          $statement->execute(array($this->id));
          $row = $statement->fetch();
          $this->values['slug'] = $row[0];
        }

        return isset($this->values['slug']);

      default:

        return call_user_func_array(array($this, 'BaseObject::__isset'), $args);
    }
  }

  public function __get($name)
  {
    $args = func_get_args();

    switch ($name)
    {
      case 'slug':

        if (!array_key_exists('slug', $this->values))
        {
          $connection = Propel::getConnection(QubitObject::DATABASE_NAME);

          $statement = $connection->prepare('
            SELECT '.QubitSlug::SLUG.'
            FROM '.QubitSlug::TABLE_NAME.'
            WHERE ? = '.QubitSlug::OBJECT_ID);
          $statement->execute(array($this->id));
          $row = $statement->fetch();
          $this->values['slug'] = $row[0];
        }

        return $this->values['slug'];

      default:

        return call_user_func_array(array($this, 'BaseObject::__get'), $args);
    }
  }

  public function __set($name, $value)
  {
    $args = func_get_args();

    switch ($name)
    {
      case 'slug':
        $this->values['slug'] = $value;

        return $this;

      default:

        return call_user_func_array(array($this, 'BaseObject::__set'), $args);
    }
  }

  public function save($connection = null)
  {
    parent::save($connection);

    // Save updated objectTermRelations
    foreach ($this->objectTermRelationsRelatedByobjectId as $relation)
    {
      $relation->indexOnSave = false;
      $relation->object = $this;
      $relation->save();
    }

    // Save updated notes
    foreach ($this->notes as $note)
    {
      $note->indexOnSave = false;
      $note->object = $this;
      $note->save();
    }

    // Save updated properties
    foreach ($this->propertys as $property)
    {
      $property->indexOnSave = false;
      $property->object = $this;
      $property->save();
    }

    // Save updated object relations
    foreach ($this->relationsRelatedByobjectId->transient as $relation)
    {
      $relation->indexOnSave = false;
      $relation->object = $this;
      $relation->save();
    }

    // Save updated subject relations
    foreach ($this->relationsRelatedBysubjectId->transient as $relation)
    {
      $relation->indexOnSave = false;
      $relation->subject = $this;
      $relation->save();
    }

    // Save updated other namnes
    foreach ($this->otherNames as $otherName)
    {
      $otherName->object = $this;
      $otherName->save();
    }

    return $this;
  }

  protected function insert($connection = null)
  {
    if (!isset($connection))
    {
      $connection = QubitTransactionFilter::getConnection(QubitObject::DATABASE_NAME);
    }

    parent::insert($connection);

    self::insertSlug($connection);

    return $this;
  }

  public function insertSlug($connection)
  {
    if (!isset($connection))
    {
      $connection = QubitTransactionFilter::getConnection(QubitObject::DATABASE_NAME);
    }

    if (isset($this->slug))
    {
      $statement = $connection->prepare('
        INSERT INTO '.QubitSlug::TABLE_NAME.' ('.QubitSlug::OBJECT_ID.', '.QubitSlug::SLUG.')
        VALUES (?, ?)');

      // Unless it is set, get random, digit and letter slug
      if (1 > strlen($this->slug))
      {
        $statement->execute(array($this->id, QubitSlug::getUnique($connection)));

        return $this;
      }

      // Truncate to 235 characters to prevent issue of long title collision
      // causing an infinite loop when computing a unique slug
      $this->slug = substr($this->slug, 0, 235);

      // Compute unique slug adding contiguous numeric suffix
      $suffix = 2;
      $triedQuery = false;
      do
      {
        try
        {
          $statement->execute(array($this->id, $this->slug));
          unset($suffix);
        }
        // Collision? Try next suffix
        catch (PDOException $e)
        {
          $stem = preg_replace('/-\d+$/', '', $this->slug, 1);

          if (!$triedQuery)
          {
            $triedQuery = true;

            // Try getting value of last suffix for this slug in database to
            // avoid long loops trying to find next suffix
            $query = 'SELECT slug FROM slug WHERE slug LIKE \''.$stem.'-%\' ORDER BY id DESC LIMIT 1;';
            $stmt2 = $connection->query($query);

            if ($lastSlugInSet = $stmt2->fetchColumn())
            {
              if (preg_match('/-(\d+)$/', $lastSlugInSet, $matches))
              {
                $suffix = intval($matches[1]) + 1;
              }
            }
          }
          else
          {
            // Simple increment in case SQL query doesn't work for some reason
            $suffix++;
          }

          $this->slug = "$stem-$suffix";
        }
      }
      while (isset($suffix));
    }

    return $this;
  }

  public function delete($connection = null)
  {
    if (!isset($connection))
    {
      $connection = QubitTransactionFilter::getConnection(QubitObject::DATABASE_NAME);
    }

    $statement = $connection->prepare('
      DELETE FROM '.QubitSlug::TABLE_NAME.'
      WHERE '.QubitSlug::OBJECT_ID.' = ?');
    $statement->execute(array($this->id));

    // Delete other names
    if (0 < count($this->otherNames))
    {
      foreach ($this->otherNames as $otherName)
      {
        $otherName->delete();
      }
    }

    parent::delete($connection);
  }

  public static function getBySlug($slug)
  {
    $criteria = new Criteria;
    $criteria->add(QubitSlug::SLUG, $slug);
    $criteria->addJoin(QubitSlug::OBJECT_ID, QubitObject::ID);

    return QubitObject::get($criteria)->__get(0);
  }

  /**
   * Required by Zend_Acl_Resource_Interface interface
   */
  public function getResourceId()
  {
    return $this->id;
  }

  /********************
        Status
  *********************/

  public function setStatus($options = array())
  {
    $status = $this->getStatus(array('typeId' => $options['typeId']));
    // only create a new status object if type is not already set
    if ($status === null)
    {
      $status = new QubitStatus;
      $status->setTypeId($options['typeId']);
    }
    $status->setStatusId($options['statusId']);
    $this->statuss[] = $status;

    return $this;
  }

  public function getStatus($options = array())
  {
    $criteria = new Criteria;
    $criteria->add(QubitStatus::OBJECT_ID, $this->id);
    $criteria->add(QubitStatus::TYPE_ID, $options['typeId']);

    return QubitStatus::getOne($criteria);
  }

  public function getNotesByType(array $options = array())
  {
    $criteria = new Criteria;
    $criteria->addJoin(QubitNote::TYPE_ID, QubitTerm::ID);
    $criteria->add(QubitNote::OBJECT_ID, $this->id);
    if (isset($options['noteTypeId']))
    {
      $criteria->add(QubitNote::TYPE_ID, $options['noteTypeId']);
    }
    if (isset($options['exclude']))
    {
      // Turn exclude string into an array
      $excludes = (is_array($options['exclude'])) ? $options['exclude'] : array($options['exclude']);

      foreach ($excludes as $exclude)
      {
        $criteria->addAnd(QubitNote::TYPE_ID, $exclude, Criteria::NOT_EQUAL);
      }
    }

    return QubitNote::get($criteria);
  }

  public function getNotesByTaxonomy(array $options = array())
  {
    $criteria = new Criteria;
    $criteria->addJoin(QubitNote::TYPE_ID, QubitTerm::ID);
    $criteria->add(QubitNote::OBJECT_ID, $this->id);
    if (isset($options['taxonomyId']))
    {
      $criteria->add(QubitTerm::TAXONOMY_ID, $options['taxonomyId']);
    }

    return QubitNote::get($criteria);
  }

  /********************
       Other names
  *********************/

  public function getOtherNames($options = array())
  {
    $criteria = new Criteria;
    $criteria->add(QubitOtherName::OBJECT_ID, $this->id);

    if (isset($options['typeId']))
    {
      $criteria->add(QubitOtherName::TYPE_ID, $options['typeId']);
    }

    return QubitOtherName::get($criteria);
  }

  /********************
       Rights
  *********************/

  public function getRights($options = array())
  {
    return QubitRelation::getRelationsBySubjectId($this->id, array('typeId' => QubitTerm::RIGHT_ID));
  }

  /********************
       Properties
  *********************/

  /**
   * Get first matching related property by name (optionally scope).
   * Return an empty QubitProperty object if a matching one doesn't exist.
   *
   * @param string $name
   * @param array $options
   * @return QubitProperty
   */
  public function getPropertyByName($name, $options = array())
  {
    if (null === $property = QubitProperty::getOneByObjectIdAndName($this->id, $name, $options))
    {
      $property = new QubitProperty;
    }

    return $property;
  }
}
