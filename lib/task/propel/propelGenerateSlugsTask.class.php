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
 * Generate missing slugs
 *
 * @package    symfony
 * @subpackage task
 * @author     David Juhasz <david@artefactual.com>
 */
class propelGenerateSlugsTask extends arBaseTask
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
      new sfCommandOption('delete', null, sfCommandOption::PARAMETER_NONE, 'Delete existing slugs before generating'),
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
    parent::execute($arguments, $options);

    $databaseManager = new sfDatabaseManager($this->configuration);
    $conn = $databaseManager->getDatabase('propel')->getConnection();

    $tables = array(
      'actor' => 'QubitActor',
      'term' => 'QubitTerm',
      'information_object' => 'QubitInformationObject',
      'physical_object' => 'QubitPhysicalObject',
      'event' => 'QubitEvent',
      'accession' => 'QubitAccession'
    );

    // Optionally delete existing slugs
    if ($options['delete'])
    {
      foreach ($tables as $table => $classname)
      {
        $this->logSection('propel', "Delete $table slugs...");

        $sql = "DELETE FROM slug WHERE object_id IN (SELECT id FROM $table)";

        if (defined("$classname::ROOT_ID"))
        {
          $sql .= ' AND object_id != '.$classname::ROOT_ID;
        }

        $conn->query($sql);
      }
    }

    // Create hash of slugs already in database
    $sql = "SELECT slug FROM slug ORDER BY slug";
    foreach ($conn->query($sql, PDO::FETCH_NUM) as $row)
    {
      $this->slugs[$row[0]] = true;
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

        case 'accession':
          $sql = 'SELECT base.id, base.identifier';
          break;

        default:
          $sql = 'SELECT base.id, i18n.name';
      }

      $sql .= ' FROM '.constant($classname.'::TABLE_NAME').' base';
      $sql .= ' INNER JOIN '.constant($classname.'I18n::TABLE_NAME').' i18n';
      $sql .= '  ON base.id = i18n.id';
      $sql .= ' LEFT JOIN '.QubitSlug::TABLE_NAME.' sl';
      $sql .= '  ON base.id = sl.object_id';
      $sql .= ' WHERE';

      if (defined("$classname::ROOT_ID"))
      {
        $sql .= '  base.id != '. $classname::ROOT_ID .' AND';
      }

      $sql .= '  base.source_culture = i18n.culture';
      $sql .= '  AND sl.id is NULL';

      foreach ($conn->query($sql, PDO::FETCH_NUM) as $row)
      {
        // Get unique slug
        $slug = QubitSlug::slugify($this->getStringToSlugify($row, $table));

        if (!$slug)
        {
          $slug = $this->getRandomSlug();
        }

        // Truncate at 250 chars
        if (250 < strlen($slug))
        {
          $slug = substr($slug, 0, 250);
        }

        $count = 0;
        $suffix = '';

        while (isset($this->slugs[$slug.$suffix]))
        {
          $count++;
          $suffix = '-'.$count;
        }

        $slug .= $suffix;

        $this->slugs[$slug] = true; // Add to lookup table
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
          $sql .= sprintf('("%s", "%s"), ', $newRows[$j][0], $newRows[$j][1]);
        }

        $sql = substr($sql, 0, -2).';';

        $conn->exec($sql);
      }
    }

    $this->logSection(
      'propel',
      'Note: you will need to rebuild your search index for slug changes to show up in search results.'
    );

    $this->logSection('propel', 'Done!');
  }

  private function getRandomSlug()
  {
    $slug = QubitSlug::random();

    while (isset($this->slugs[$slug]))
    {
      $slug = QubitSlug::random();
    }

    return $slug;
  }

  /**
   * Call table specific handlers to return an appropriate string to base the slug off of.
   *
   * For now we only have special slug basis settings for information objects, but other
   * class types may get their own custom settings in the future.
   *
   * @return string  The string to base our slug off of.
   */
  private function getStringToSlugify($row, $table)
  {
    switch ($table)
    {
      case 'information_object':
        return $this->getInformationObjectStringToSlugify($row);

      default:
        return $row[1];
    }
  }

  /**
   * Get string to slugify for an information object, based on the slug basis setting.
   *
   * @param array $row  Data pulled from the database about the information object.
   * @return string  The string to use to slugify.
   */
  private function getInformationObjectStringToSlugify($row)
  {
    if (null === $basis = QubitSetting::getByName('slug_basis_informationobject'))
    {
      return $row[1]; // Fall back to title as the slug basis if no setting present
    }

    // Note: pull reference codes from ES, as hydrating an ORM object and building the inherited
    // reference code on-the-fly is not performant.
    switch ($basis->getValue())
    {
      case QubitSlug::SLUG_BASIS_REFERENCE_CODE:
        return $this->getSlugStringFromES($row[0], 'referenceCode');

      case QubitSlug::SLUG_BASIS_REFERENCE_CODE_NO_COUNTRY_REPO:
        return $this->getSlugStringFromES($row[0], 'referenceCodeWithoutCountryAndRepo');

      case QubitSlug::SLUG_BASIS_IDENTIFIER:
        return $this->getSlugStringFromES($row[0], 'identifier');

      case QubitSlug::SLUG_BASIS_TITLE:
        return $row[1];

      default:
        throw new sfException('Unsupported slug basis specified in settings: '.$basis->getValue());
    }
  }

  /**
   * Get an information object string from ES to use as the basis for generating a slug.
   *
   * @param int $id  The id for the information object we're looking up.
   *
   * @param string $property  Depending on the slug basis, this is the property that contains the string we want.
   *                          e.g., referenceCode, identifier, etc.
   *
   * @return string  Return the specified string to use as a basis to generate the slug.
   */
  private function getSlugStringFromES($id, $property)
  {
    $query = new \Elastica\Query;
    $queryBool = new \Elastica\Query\BoolQuery;

    $queryBool->addMust(new \Elastica\Query\Term(array('_id' => $id)));
    $query->setQuery($queryBool);
    $query->setLimit(1);

    $results = QubitSearch::getInstance()->index->getType('QubitInformationObject')->search($query);

    if (!$results->count())
    {
      return null;
    }

    $doc = $results[0]->getData();

    if (!array_key_exists($property, $doc))
    {
      throw new sfException("ElasticSearch document for information object (id: $id) has no property $property");
    }

    return $doc[$property];
  }
}
