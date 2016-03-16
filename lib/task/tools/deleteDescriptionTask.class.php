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

class deleteDescriptionTask extends sfBaseTask
{
  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('slug', sfCommandArgument::REQUIRED, 'Slug.')
    ));

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application name', true),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'cli'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'propel'),
      new sfCommandOption('no-confirmation', 'B', sfCommandOption::PARAMETER_NONE, 'Do not ask for confirmation'),
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
    $databaseManager = new sfDatabaseManager($this->configuration);
    $conn = $databaseManager->getDatabase('propel')->getConnection();
    sfContext::createInstance($this->configuration);

    if (null === $informationObject = QubitInformationObject::getBySlug($arguments['slug']))
    {
      throw new sfException('The description cannot be found in the database.');
    }

    $descriptions = $informationObject->descendants->andSelf()->orderBy('rgt');
    $totalDescs = count($descriptions);

    if (!$options['no-confirmation'])
    {
      if (!$this->getConfirmation($informationObject->getTitle(array('cultureFallback' => true)), $totalDescs))
      {
        return;
      }
    }

    $n = 0;

    foreach ($descriptions as $desc)
    {
      // Delete related digitalObjects
      foreach ($desc->digitalObjects as $digitalObject)
      {
        $digitalObject->informationObjectId = null;
        $digitalObject->delete();
      }

      $this->logSection('Deleting "'.$desc->getTitle(array('cultureFallback' => true)).
                        '" ('.++$n.'/'.$totalDescs.')');
      $desc->delete();
    }

    $this->logSection('Finished!');
  }

  private function getConfirmation($descTitle, $totalDescs)
  {
    return $this->askConfirmation(array(
      'WARNING: You are about to delete the record "'.$descTitle.'" and '.($totalDescs - 1).
      ' descendant records.', 'Are you sure you want to proceed? (y/N)'),
      'QUESTION_LARGE', false
    );
  }
}
