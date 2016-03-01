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

class QubitAccession extends BaseAccession
{
  public function __toString()
  {
    return (string) $this->identifier;
  }

  protected function insert($connection = null)
  {
    if (!$this->identifier)
    {
      $this->identifier = self::generateAccessionIdentifier(true);
    }

    if (!isset($this->slug))
    {
      $this->slug = QubitSlug::slugify($this->__get('identifier', array('sourceCulture' => true)));
    }

    parent::insert($connection);
  }

  public function save($connection = null)
  {
    parent::save($connection);

    // Save updated related events (update search index after updating all
    // related objects that are included in the index document)
    foreach ($this->eventsRelatedByobjectId as $item)
    {
      $item->indexOnSave = false;

      // TODO Needed if $this is new, should be transparent
      $item->object = $this;
      $item->save($connection);
    }

    QubitSearch::getInstance()->update($this);

    return $this;
  }

  public function delete($connection = null)
  {
    QubitSearch::getInstance()->delete($this);

    return parent::delete($connection);
  }

  public function isAccrual()
  {
    if (!isset($this->id))
    {
      return false;
    }

    $criteria = new Criteria;
    $criteria->add(QubitRelation::TYPE_ID, QubitTerm::ACCRUAL_ID);
    $criteria->add(QubitRelation::SUBJECT_ID, $this->id);

    return 0 < count(QubitRelation::get($criteria));
  }

  public static function getAccessionNumber($incrementCounter)
  {
    if ($incrementCounter)
    {
      $con = Propel::getConnection();
      try
      {
        $con->beginTransaction();

        $setting = QubitSetting::getByName('accession_counter');
        $value = $setting->getValue(array('sourceCulture' => true)) + 1;
        $setting->setValue($value, array('sourceCulture' => true));
        $setting->save();

        $con->commit();
      }
      catch (PropelException $e)
      {
        $con->rollback();

        throw $e;
      }
    }
    else
    {
      $setting = QubitSetting::getByName('accession_counter');
      $value = $setting->getValue(array('sourceCulture' => true)) + 1;
    }

    return $value;
  }

  public static function generateAccessionIdentifier($incrementCounter = false)
  {
    return preg_replace_callback('/([#%])([A-z]+)/', function($match) use ($incrementCounter)
    {
      if ('%' == $match[1])
      {
        return strftime('%'.$match[2]);
      }
      else if ('#' == $match[1])
      {
        if (0 < preg_match('/^i+$/', $match[2], $matches))
        {
          $pad = strlen($matches[0]);
          $number = QubitAccession::getAccessionNumber($incrementCounter);

          return str_pad($number, $pad, 0, STR_PAD_LEFT);
          // return sprintf('%0' . $pad . 'd', $number);
        }
        else
        {
          return $match[2];
        }
      }
    }, sfConfig::get('app_accession_mask'));
  }

  /**
   * Get related actors 
   */
  public function getActors($options = array())
  {
    $criteria = new Criteria;
    $criteria->addJoin(QubitActor::ID, QubitEvent::ACTOR_ID);
    $criteria->add(QubitEvent::OBJECT_ID, $this->id);

    if (isset($options['eventTypeId']))
    {
      $criteria->add(QubitEvent::TYPE_ID, $options['eventTypeId']);
    }

    if (isset($options['cultureFallback']) && true === $options['cultureFallback'])
    {
      $criteria->addAscendingOrderByColumn('authorized_form_of_name');
      $criteria = QubitCultureFallback::addFallbackCriteria($criteria, 'QubitActor', $options);
    }

    $actors = QubitActor::get($criteria);

    return $actors;
  }

  /**
   * Get creators
   */
  public function getCreators($options = array())
  {
    return $this->getActors($options = array('eventTypeId' => QubitTerm::CREATION_ID));
  }

  /**
   * Related events which have a date
   */
  public function getDates(array $options = array())
  {
    $criteria = new Criteria;
    $criteria->add(QubitEvent::OBJECT_ID, $this->id);

    $criteria->addMultipleJoin(array(
      array(QubitEvent::ID, QubitEventI18n::ID),
      array(QubitEvent::SOURCE_CULTURE, QubitEventI18n::CULTURE)),
      Criteria::LEFT_JOIN);

    $criteria->add($criteria->getNewCriterion(QubitEvent::END_DATE, null, Criteria::ISNOTNULL)
      ->addOr($criteria->getNewCriterion(QubitEvent::START_DATE, null, Criteria::ISNOTNULL))
      ->addOr($criteria->getNewCriterion(QubitEventI18n::DATE, null, Criteria::ISNOTNULL)));

    if (isset($options['type_id']))
    {
      $criteria->add(QubitEvent::TYPE_ID, $options['type_id']);
    }

    $criteria->addDescendingOrderByColumn(QubitEvent::START_DATE);

    return QubitEvent::get($criteria);
  }
}
