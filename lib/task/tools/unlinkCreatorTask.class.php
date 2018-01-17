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
 * Unlink Creator Task
 *
 * @package    symfony
 * @subpackage task
 * @author     Steve Breker <sbreker@artefactual.com>
 */
class unlinkCreatorTask extends sfBaseTask
{
  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application name', true),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'cli'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'propel'),
      new sfCommandOption('creator-slug', null, sfCommandOption::PARAMETER_REQUIRED, 'Restrict changes to specific creator.', null),
      new sfCommandOption('description-slug', null, sfCommandOption::PARAMETER_REQUIRED, 'Restrict changes to this information object hierarchy.', null),
    ));

    $this->namespace = 'tools';
    $this->name = 'unlink-creators';
    $this->briefDescription = 'Unlink creators from descriptions so creator inheritance can be used.';
    $this->detailedDescription = <<<EOF
Unlink creators from descriptions so creator inheritance can be used.
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    if ($options['creator-slug'] && $options['description-slug'])
    {
      throw new Exception('Creator and description filters cannot be set at the same time. '.
                          'Remove one and try again.');
    }

    sfContext::createInstance($this->configuration);
    $databaseManager = new sfDatabaseManager($this->configuration);
    $conn = $databaseManager->getDatabase('propel')->getConnection();

    self::unlinkCreators($options);
    $this->log('Done!');
  }

  private function unlinkCreators($options = array())
  {
    // Get actor records from slug if supplied.
    if ($options['creator-slug'])
    {
      $criteria = new Criteria;
      $criteria->addJoin(QubitActor::ID, QubitSlug::OBJECT_ID);
      $criteria->add(QubitSlug::SLUG, $options['creator-slug']);
      $actor = QubitActor::getOne($criteria);
      if (null === $actor)
      {
        throw new Exception('Actor slug supplied but not found');
      }
    }

    // Get IO from slug if supplied.
    if ($options['description-slug'])
    {
      $criteria = new Criteria;
      $criteria->addJoin(QubitInformationObject::ID, QubitSlug::OBJECT_ID);
      $criteria->add(QubitSlug::SLUG, $options['description-slug']);
      $io = QubitInformationObject::getOne($criteria);
      if (null === $io)
      {
        throw new Exception('Description slug supplied but not found');
      }
      // If IO supplied get list of descendant IO's, including self.
      // We need ALL descendants because we are fixing all Creators for this IO.
      foreach ($io->descendants->andSelf()->orderBy('lft') as $item)
      {
        $ioList[] = $item->id;
      }
    }

    // Get affected io records via event table.
    $criteria = new Criteria;
    $criteria->addJoin(QubitInformationObject::ID, QubitEvent::OBJECT_ID);
    $criteria->addJoin(QubitActor::ID, QubitEvent::ACTOR_ID);
    $criteria->addGroupByColumn(QubitInformationObject::ID);

    // limit to a specific actor
    if (null !== $actor)
    {
      $criteria->add(QubitActor::ID, $actor->id, Criteria::EQUAL);
    }
    // limit to specific information object hierarchy
    if (null !== $io)
    {
      $criteria->add(QubitInformationObject::ID, $ioList, Criteria::IN);
    }

    // Loop over hierarchy of this Information Object from the top down.
    // Higher levels of IO must be corrected before lower nodes.
    foreach (QubitInformationObject::get($criteria)->orderBy('lft') as $io)
    {
      $deleteCreators = false;
      $creatorIds = array();
      $ancestorCreatorIds = array();

      $this->logSection('Description', sprintf('%s %d', $io->slug, $io->id));

      $creators = $io->getCreators();
      foreach ($creators as $creator)
      {
        $creatorIds[] = $creator->id;
      }

      // Nothing to do if this is the top level record or if no creators on this IO.
      if ($io->parentId == QubitInformationObject::ROOT_ID || 0 == count($creatorIds))
      {
        continue;
      }

      // If an actor was specified as params, that is the only actor we can remove.
      // If > 1 actor on this IO, we can't remove only one or the inheritance
      // will not work properly so if this is case - skip.
      if (null !== $actor && 1 < count($creatorIds))
      {
        continue;
      }
      // Get all ancestors of this IO and iterate from bottom up.
      foreach ($io->ancestors->andSelf()->orderBy('rgt') as $ancestor)
      {
        // if this ancestor is the root IO or self, skip it.
        if ($ancestor->id == QubitInformationObject::ROOT_ID || $ancestor->id == $io->id)
        {
          continue;
        }

        $ancestorCreators = $ancestor->getCreators();
        $this->logSection('Ancestor', sprintf('%s', $ancestor->slug));

        // Creator list must match exactly. Test count, and if equal, then look closer
        if (count($ancestorCreators) == count($creators))
        {
          foreach ($ancestorCreators as $ancestorCreator)
          {
            // Build ID array
            $ancestorCreatorIds[] = $ancestorCreator->id;
          }

          $diff = array_diff($creatorIds, $ancestorCreatorIds);
          // if the creator lists match exactly, then delete and inherit from ancestor.
          if (0 == count($diff))
          {
            $deleteCreators = true;
            break;
          }
          // If there are creators on the ancestors but they don't match:
          //   -- stop looking cause it does not matter what is above this node
          //   -- do not delete any creators because they do not match current ancestor.
          else
          {
            break;
          }
        }
        // If there are creators on the ancestors but they don't match:
        //   -- stop looking cause it does not matter what is above this node
        //   -- do not delete any creators because they do not match current ancestor.
        else if (0 < count($ancestorCreators))
        {
          break;
        }
      }

      if ($deleteCreators)
      {
        self::removeCreator($io, $creatorIds);
      }
    }
  }

  private function removeCreator($infoObj = null, $creatorIds)
  {
    // This will unlink this Actor from all creation events on this IO.
    foreach ($infoObj->getActorEvents(array('eventTypeId' => QubitTerm::CREATION_ID)) as $event)
    {
      if (in_array($event->actor->id, $creatorIds))
      {
        $this->logSection('Unlink', sprintf('%s', $event->actor->slug));
        $event->indexOnSave = true;
        unset($event->actor);
        $event->save();
        // Delete the event record too if there aren't any dates/times on it.
        if (null == $event->getPlace()->name && null == $event->date && null == $event->name && null == $event->description && null == $event->startDate && null == $event->endDate && null == $event->startTime && null == $event->endTime)
        {
          $event->delete();
        }
      }
    }
  }
}
