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
 * Generate missing slugs
 *
 * @package    symfony
 * @subpackage task
 * @author     David Juhasz <david@artefactual.com>
 * @version    SVN: $Id: propelGenerateSlugsTask.class.php 10288 2011-11-08 21:25:05Z mj $
 */
class propelGenerateSlugsTask extends sfBaseTask
{
  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->addArguments(array(
    ));

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application name', true),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'cli'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'propel'),
    ));

    $this->namespace = 'propel';
    $this->name = 'generate-slugs';
    $this->briefDescription = 'Generate slugs for all slug-less objects.';

    $this->detailedDescription = <<<EOF
Generate slugs for all slug-less objects.
EOF;
  }

  /**
   * @see sfTask
   */
  public function execute($arguments = array(), $options = array())
  {
    $databaseManager = new sfDatabaseManager($this->configuration);
    $conn = $databaseManager->getDatabase('propel')->getConnection();

    $tables = array(
      'actor' => 'QubitActor',
      'information_object' => 'QubitInformationObject',
      'term' => 'QubitTerm',
      'event' => 'QubitEvent'
    );

    // Create hash of slugs already in database
    $sql = "SELECT slug FROM slug ORDER BY slug";
    foreach ($conn->query($sql, PDO::FETCH_NUM) as $row)
    {
      $slugs[$row[0]] = true;
    }

    foreach ($tables as $table => $classname)
    {
      $this->logSection('propel', "Generate $table slugs...");
      $newRows = array(); // reset

      switch ($table)
      {
        case 'actor':
          $sql = 'SELECT base.id, i18n.authorized_form_of_name';
          break;

        case 'information_object':
          $sql = 'SELECT base.id, i18n.title';
          break;

        default:
          $sql = 'SELECT base.id, i18n.name';
      }

      $sql .= ' FROM '.constant($classname.'::TABLE_NAME').' base';
      $sql .= ' INNER JOIN '.constant($classname.'I18n::TABLE_NAME').' i18n';
      $sql .= '  ON base.id = i18n.id';
      $sql .= ' LEFT JOIN '.QubitSlug::TABLE_NAME.' sl';
      $sql .= '  ON base.id = sl.object_id';
      $sql .= ' WHERE base.id > 3';
      $sql .= '  AND base.source_culture = i18n.culture';
      $sql .= '  AND sl.id is NULL';

      foreach($conn->query($sql, PDO::FETCH_NUM) as $row)
      {
        // Get unique slug
        if (null !== $row[1])
        {
          $slug = QubitSlug::slugify($row[1]);

          // Truncate at 250 chars
          if (250 < strlen($slug))
          {
            $slug = substr($slug, 0, 250);
          }

          $count = 0;
          $suffix = '';

          while (isset($slugs[$slug.$suffix]))
          {
            $count++;
            $suffix = '-'.$count;
          }

          $slug .= $suffix;
        }
        else
        {
          $slug = QubitSlug::random();

          while (isset($slugs[$slug]))
          {
            $slug = QubitSlug::random();
          }
        }

        $slugs[$slug] = true; // Add to lookup table
        $newRows[] = array($row[0], $slug); // To add to slug table
      }

      // Do inserts
      $inc = 1000;
      for ($i = 0; $i < count($newRows); $i += $inc)
      {
        $sql = "INSERT INTO slug (object_id, slug) VALUES ";

        $last = min($i+$inc, count($newRows));
        for ($j = $i; $j < $last; $j++)
        {
          $sql .= '('.$newRows[$j][0].', \''.$newRows[$j][1].'\'), ';
        }

        $sql = substr($sql, 0, -2).';';

        $conn->exec($sql);
      }
    }

    $this->logSection('propel', 'Done!');
  }
}
