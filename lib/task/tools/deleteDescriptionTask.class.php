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

class deleteDescriptionTask extends arBaseTask
{
  private $nDeleted = 0;

  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('slug', sfCommandArgument::REQUIRED, 'Description slug to delete. '.
                            'Note: if --repository is set, this is instead a repository slug whose descriptions we will target for deletion.')
    ));

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application name', true),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'cli'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'propel'),
      new sfCommandOption('no-confirmation', 'B', sfCommandOption::PARAMETER_NONE, 'Do not ask for confirmation'),
      new sfCommandOption('repository', 'r', sfCommandOption::PARAMETER_NONE, 'Delete descriptions under repository specified by slug.'),
    ));

    $this->namespace = 'tools';
    $this->name = 'delete-description';
    $this->briefDescription = 'Delete description given its slug.';

    $this->detailedDescription = <<<EOF
Delete archival descriptions by slug.
EOF;
  }

  /**
   * @see sfTask
   */
  public function execute($arguments = array(), $options = array())
  {
    parent::execute($arguments, $options);

    $this->resourceType = $options['repository'] ? 'QubitRepository' : 'QubitInformationObject';
    $this->fetchResource($arguments['slug']);

    if (!$this->confirmDeletion($options['no-confirmation']))
    {
      $this->logSection('delete-description', sprintf('[%s] Task aborted.', strftime('%r')));
      return;
    }

    // User wishes to proceed, delete targeted information objects.
    switch ($this->resourceType)
    {
      case 'QubitRepository':
        $this->deleteDescriptionsFromRepository();
        break;
      case 'QubitInformationObject':
        $this->deleteDescriptions($this->resource);
        break;
    }

    $this->logSection('delete-description', sprintf('[%s] Finished: %d descriptions deleted.', strftime('%r'), $this->nDeleted));
  }

  /**
   * Allow the user to bail out if they aren't sure they want to delete targeted descriptions.
   *
   * @param $noConfirmation  Whether or not to bypass the confirmation warning (true = bypass).
   * @return bool  True if we want to proceed with the task, false if we want to abort.
   */
  private function confirmDeletion($noConfirmation)
  {
    if ($noConfirmation)
    {
      return true;
    }

    switch ($this->resourceType)
    {
      case 'QubitRepository':
        $confirmWarning = sprintf('WARNING: You are about to delete all the records under the repository "%s".',
                                  $this->resource->getAuthorizedFormOfName(array('cultureFallback' => true)));
        break;
      case 'QubitInformationObject':
        $confirmWarning = sprintf('WARNING: You are about to delete the record "%s" and %d descendant records.',
                                  $this->resource->getTitle(array('cultureFallback' => true)),
                                  count($this->resource->descendants));
        break;
    }

    if ($this->askConfirmation(array($confirmWarning, 'Are you sure you want to proceed? (y/N)'),
                               'QUESTION_LARGE', false))
    {
      return true;
    }

    return false;
  }

  /**
   * Get AtoM resource specified by resource type and slug.
   *
   * @param string $slug  String indicating the resource's slug.
   */
  private function fetchResource($slug)
  {
    $c = new Criteria;
    $c->addJoin(constant("{$this->resourceType}::ID"), QubitSlug::OBJECT_ID);
    $c->add(QubitSlug::SLUG, $slug);

    if (null === $this->resource = call_user_func_array("{$this->resourceType}::getOne", array($c)))
    {
      throw new sfException(sprintf('Resource (slug: %s, type: %s) not found in database.',
                                    $slug, $this->resourceType));
    }
  }

  /**
   * Delete specified description & its descendants from AtoM.
   *
   * @param $root  A top level QubitInformationObject which will be deleted along with its descendants.
   */
  private function deleteDescriptions($root)
  {
    $this->logSection('delete-description', sprintf('[%s] Deleting description "%s" (slug: %s, +%d descendants)', strftime('%r'),
      $root->getTitle(array('cultureFallback' => true)),
      $root->slug, count($root->descendants)));

    $this->nDeleted += $root->deleteFullHierarchy();
  }

  /**
   * Delete all top level descriptions in specified repository (selected by slug in CLI), as
   * well as their descendants.
   */
  private function deleteDescriptionsFromRepository()
  {
    $this->logSection('delete-description', sprintf('[%s] Removing descriptions from repository "%s" (slug: %s)...', strftime('%r'),
      $this->resource->getAuthorizedFormOfName(array('cultureFallback' => true)),
      $this->resource->slug));

    $rows = QubitPdo::fetchAll('SELECT id FROM information_object WHERE parent_id = ? AND repository_id = ?',
                               array(QubitInformationObject::ROOT_ID, $this->resource->id));

    //
    // Loop over each top level description (TLD) id and fetch via getById, then delete each record
    // hierarchy. We do it this way instead of a single ORM query since the nested set updates between
    // TLD hierarchy deletions were leaving the iterators with outdated lft/rgt values resulting in them
    // attempting to delete unrelated records.
    //
    foreach ($rows as $row)
    {
      if (null === $io = QubitInformationObject::getById($row->id))
      {
        throw new sfException("Failed to get information object {$row->id} in deleteDescriptionsFromRepository");
      }

      $this->deleteDescriptions($io);
    }
  }
}
