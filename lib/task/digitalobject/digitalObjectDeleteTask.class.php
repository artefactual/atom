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

class digitalObjectDeleteTask extends arBaseTask
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
      new sfCommandOption('and-descendants', null, sfCommandOption::PARAMETER_NONE, 'Remove digital objects for descendant archival descriptions as well')

    ));

    $this->namespace = 'digitalobject';
    $this->name = 'delete';
    $this->briefDescription = 'Delete digital objects given an archival description slug.';

    $this->detailedDescription = <<<EOF
Delete digital objects given an archival description slug.
EOF;
  }

  /**
   * @see sfTask
   */
  public function execute($arguments = array(), $options = array())
  {
    parent::execute($arguments, $options);

    $t = new QubitTimer;
    $id = QubitPdo::fetchColumn('SELECT object_id FROM slug WHERE slug=?', array($arguments['slug']));

    if (!$id)
    {
      throw new sfException('Invalid slug "'.$arguments['slug'].'" entered.');
    }

    if (null === $infoObj = QubitInformationObject::getById($id))
    {
      throw new sfException('Failed to fetch information object with the slug given.');
    }

    $ios = $options['and-descendants'] ? $infoObj->descendants->andSelf() : array($infoObj);
    $nDeleted = 0;

    foreach ($ios as $io)
    {
      $this->logSection('digital-object', sprintf('(%d of %d) deleting digital object for: %s',
                        ++$nDeleted, count($ios), $io->getTitle(array('cultureFallback' => true))));

      // Remove appropriate digital object files, empty directories left behind, and db entries
      foreach ($io->digitalObjects as $do)
      {
        $this->deleteDigitalObjectFiles($do);
        QubitDigitalObject::pruneEmptyDirs(dirname($do->getAbsolutePath()));
        $do->delete();
      }
    }

    $this->logSection('digital-object', sprintf('%d digital objects deleted successfully (%.2fs elapsed)', $nDeleted, $t->elapsed()));
  }

  private function deleteDigitalObjectFiles($digitalObject)
  {
    $this->deleteDigitalObject($digitalObject);

    // Also delete derivative files
    $c = new Criteria;
    $c2 = new Criteria;

    $c2->add(QubitDigitalObject::USAGE_ID, QubitTerm::REFERENCE_ID);
    $c2->addOr(QubitDigitalObject::USAGE_ID, QubitTerm::THUMBNAIL_ID);

    $c->add(QubitDigitalObject::PARENT_ID, $digitalObject->id);
    $c->add($c2);

    foreach (QubitDigitalObject::get($c) as $derivative)
    {
      $this->deleteDigitalObject($derivative);
    }
  }

  private function deleteDigitalObject($digitalObject)
  {
    if ($digitalObject->usageId == QubitTerm::EXTERNAL_URI_ID || $digitalObject->usageId == QubitTerm::OFFLINE_ID)
    {
      return;
    }

    if (unlink($digitalObject->getAbsolutePath()) === false)
    {
      throw new sfException('Failed to delete file '.$digitalObject->getAbsolutePath(). ' -- please check your folder permissions.');
    }
  }
}
