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

class deleteDraftsTask extends sfBaseTask
{
  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application name', true),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'cli'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'propel'),
      new sfCommandOption('no-confirmation', 'B', sfCommandOption::PARAMETER_NONE, 'Do not ask for confirmation'),
    ));

    $this->namespace = 'tools';
    $this->name = 'delete-drafts';
    $this->briefDescription = 'Delete all information objects with publication status: DRAFT';
    $this->detailedDescription = <<<EOF
Delete all information objects with publication status: DRAFT
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    $configuration = ProjectConfiguration::getApplicationConfiguration('qubit', 'test', false);
    $sf_context = sfContext::createInstance($configuration);

    $databaseManager = new sfDatabaseManager($this->configuration);
    $conn = $databaseManager->getDatabase('propel')->getConnection();

    $sqlQuery =  "SELECT s.object_id FROM information_object i JOIN status s ON i.id = s.object_id " .
                 "WHERE s.type_id = " . QubitTerm::STATUS_TYPE_PUBLICATION_ID . 
                 " AND s.status_id = " . QubitTerm::PUBLICATION_STATUS_DRAFT_ID .
                 " AND i.id <> 1"; // Don't delete root node!

    $this->logSection("delete-drafts", "Deleting all information objects marked as draft...");

    // Confirmation
    $question = 'Are you SURE you want to do this (y/N)?';
    if (!$options['no-confirmation'] && !$this->askConfirmation(array($question), 'QUESTION_LARGE', false))
    {
      return 1;
    }

    $n = 0;
    foreach($conn->query($sqlQuery, PDO::FETCH_ASSOC) as $row)
    {
      $id = $row['object_id'];
      $resource = QubitInformationObject::getById($id);

      if (!$resource)
        continue;

      foreach ($resource->descendants->andSelf()->orderBy('rgt') as $item)
      {
        try
        {
          $item->delete();
        }
        catch (Exception $e)
        {
          $this->log("Warning: got error while deleting: " . $e->getMessage());
        }

        if (++$n % 10 == 0)
        {
          print '.';
          fflush(STDOUT);
        }
      }
    }

    $this->logSection("delete-drafts", "Finished! {$n} items deleted.");
  }
}
