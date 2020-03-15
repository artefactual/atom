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

class arElasticSearchAccession extends arElasticSearchModelBase
{
  protected static $statements;

  public function load()
  {
    $accessionIds = QubitPdo::fetchAll(
      'SELECT id FROM '.QubitAccession::TABLE_NAME,
      array(),
      array('fetchMode' => PDO::FETCH_COLUMN)
    );

    $this->count = count($accessionIds);

    return $accessionIds;
  }

  public function populate()
  {
    $errors = array();

    foreach ($this->load() as $key => $id)
    {
      try
      {
        $data = self::serialize($id);

        $this->search->addDocument($data, 'QubitAccession');

        $this->logEntry($data['identifier'], $key + 1);
      }
      catch (sfException $e)
      {
        $errors[] = $e->getMessage();
      }
    }

    return $errors;
  }

  private static function serialize($id)
  {
    if (!isset(self::$conn))
    {
      self::$conn = Propel::getConnection();
    }

    if (!isset(self::$statements['accession']))
    {
      $sql = 'SELECT acc.*, slug.slug
        FROM '.QubitAccession::TABLE_NAME.' acc
        JOIN '.QubitSlug::TABLE_NAME.' slug ON acc.id = slug.object_id
        WHERE acc.id = :id';

      self::$statements['accession'] = self::$conn->prepare($sql);
    }

    self::$statements['accession']->execute(array(':id' => $id));
    $data = self::$statements['accession']->fetch(PDO::FETCH_ASSOC);

    if (false === $data)
    {
      throw new sfException("Couldn't find accession (id: $id)");
    }

    $serialized = array();
    $serialized['id'] = $id;
    $serialized['slug'] = $data['slug'];
    $serialized['identifier'] = $data['identifier'];
    $serialized['date'] = arElasticSearchPluginUtil::convertDate($data['date']);
    $serialized['createdAt'] = arElasticSearchPluginUtil::convertDate($data['created_at']);
    $serialized['updatedAt'] = arElasticSearchPluginUtil::convertDate($data['updated_at']);
    $serialized['sourceCulture'] = $data['source_culture'];
    $serialized['i18n'] = self::serializeI18ns($id, array('QubitAccession'));

    $sql = "SELECT o.id, o.source_culture FROM ".QubitOtherName::TABLE_NAME." o \r
              INNER JOIN ".QubitTerm::TABLE_NAME." t ON o.type_id=t.id \r
              WHERE o.object_id = ? AND t.taxonomy_id= ?";
    $params = array($id, QubitTaxonomy::ACCESSION_ALTERNATIVE_IDENTIFIER_TYPE_ID);

    foreach (QubitPdo::fetchAll($sql, $params) as $item)
    {
      $serialized['alternativeIdentifiers'][] = arElasticSearchOtherName::serialize($item);
    }

    foreach (QubitRelation::getRelationsBySubjectId($id, array('typeId' => QubitTerm::DONOR_ID)) as $item)
    {
      $serialized['donors'][] = arElasticSearchDonor::serialize($item->object);
    }

    foreach (QubitRelation::getRelationsByObjectId($id, array('typeId' => QubitTerm::CREATION_ID)) as $item)
    {
      $node = new arElasticSearchActorPdo($item->subject->id);
      $serialized['creators'][] = $node->serialize();
    }

    return $serialized;
  }

  public static function update($object)
  {
    $data = self::serialize($object->id);

    QubitSearch::getInstance()->addDocument($data, 'QubitAccession');

    return true;
  }
}
