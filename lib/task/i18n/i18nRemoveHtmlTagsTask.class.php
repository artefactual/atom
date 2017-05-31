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
 * Remove HTML tags from various i18n information object fields.
 *
 * @package    symfony
 * @subpackage task
 * @author     Mike Cantelon <mike@artefactual.com>
 * @author     Mike Gale <mikeg@artefactual.com>
 */
class i18nRemoveHtmlTagsTask extends sfBaseTask
{
  public static $tables = array(
    'information_object_i18n' => array(
      'title',
      'alternate_title',
      'edition',
      'extent_and_medium',
      'archival_history',
      'acquisition',
      'scope_and_content',
      'appraisal',
      'accruals',
      'arrangement',
      'access_conditions',
      'reproduction_conditions',
      'physical_characteristics',
      'finding_aids',
      'location_of_originals',
      'location_of_copies',
      'related_units_of_description',
      'institution_responsible_identifier',
      'rules',
      'sources',
      'revision_history',
    ),
    'actor_i18n' => array(
      'authorized_form_of_name',
      'dates_of_existence',
      'history',
      'places',
      'legal_status',
      'functions',
      'mandates',
      'internal_structures',
      'general_context',
      'institution_responsible_identifier',
      'rules',
      'sources',
      'revision_history',
    ),
    'note_i18n' => array(
      'note_i18n',
    ),
    'repository_i18n' => array(
      'geocultural_context',
      'collecting_policies',
      'buildings',
      'holdings',
      'finding_aids',
      'opening_times',
      'access_conditions',
      'disabled_access',
      'research_services',
      'reproduction_services',
      'public_facilities',
      'desc_institution_identifier',
      'desc_rules',
      'desc_sources',
      'desc_revision_history',
    ),
    'rights_i18n' => array(
      'rights_note',
      'copyright_note',
      'identifier_value',
      'identifier_type',
      'identifier_role',
      'license_terms',
      'license_note',
      'statute_jurisdiction',
      'statute_note',
    ),
  );

  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application name', true),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'cli'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'propel'),
    ));

    $this->namespace = 'i18n';
    $this->name = 'remove-html-tags';
    $this->briefDescription = 'Remove HTML tags from inside various i18n fields, and convert HTML entities';

    $this->detailedDescription = <<<EOF
Remove HTML tags from inside information object, actor, note, repository, and rights i18n fields.
HTML character entities are also converted to their non-HTML representations.
EOF;
  }

  /**
   * @see sfTask
   */
  public function execute($arguments = array(), $options = array())
  {
    $rowCount           = 0;
    $changedCount       = 0;
    $columnsChangedCount = 0;

    $rootIds = implode(', ', array(QubitInformationObject::ROOT_ID, QubitActor::ROOT_ID,
                       QubitRepository::ROOT_ID));

    foreach (i18nRemoveHtmlTagsTask::$tables as $tableName => $columns)
    {
      // Fetch all information object i18n rows
      $query = 'SELECT * FROM '.$tableName.' WHERE id NOT IN ('.$rootIds.')';
      $statement = QubitPdo::prepareAndExecute($query);

      while ($io = $statement->fetch(PDO::FETCH_OBJ))
      {
        // Process HTML in row's columns
        $columnsChanged = $this->processI18nHtml($io, $tableName, $columns);

        // Update total column values changed
        if ($columnsChanged)
        {
          $changedCount++;
          $columnsChangedCount += $columnsChanged;
        }

        // Report progress
        $message = 'Processed object '.$io->id;

        if ($columnsChanged)
        {
          $message .= ' ('. $columnsChanged . ' changes)';
        }

        $this->logSection('i18n', $message);
        $rowCount++;
      }
    }

    // Report summary of processing
    $message = 'Processed '. $rowCount .' objects.';

    if ($changedCount)
    {
      $message .= ' Changed '. $changedCount .' objects';
      $message .= ' ('. $columnsChangedCount .' field values changed).';
    }

    $this->logSection('i18n', $message);
  }

  /**
   * Determine which i18n columns are populated and update them.
   *
   * @param stdClass $io  row of information object i18n data
   *
   * @return integer  number of columns changed
   */
  private function processI18nHtml(&$io, $tableName, $columns)
  {
    // Determine what column values contain HTML
    $columnValues = array();

    foreach ($columns as $column)
    {
      // Store column name/value for processing if it contains tags
      if ($io->{$column} && (($io->{$column} != strip_tags($io->{$column})) || ($io->{$column} != html_entity_decode($io->{$column}))))
      {
        $columnValues[$column] = $io->{$column};
      }
    }

    // Update database with transformed column values
    $this->transformHtmlInI18nTableColumns($tableName, $io->id, $io->culture, $columnValues);

    return count($columnValues);
  }

  /**
   * Transform HTML column values into text and update specified i18n table row
   *
   * @param string  i18n table name
   * @param integer $id  ID of row in an i18n table
   * @param string $culture  culture code of a row in an i18n table
   * @param array $columnValues  key/value array of column/value data to process
   *
   * @return void
   */
  private function transformHtmlInI18nTableColumns($table, $id, $culture, $columnValues)
  {
    // Assemble query and note parsed column values
    $values = array();

    $query = 'UPDATE '. $table .' SET ';

    foreach ($columnValues as $column => $value)
    {
      // Only update if tags or HTML entities are found
      if ($value != strip_tags($value) || $value != html_entity_decode($value))
      {
        $transformedValue = $this->transformHtmlToText($value);

        $query .= (count($values)) ? ', ' : '';

        $query .= $column ."=?";

        $values[] = $transformedValue;
      }
    }

    $query .= " WHERE id='". $id ."' AND culture='". $culture ."'";

    if (count($values))
    {
      QubitPdo::prepareAndExecute($query, $values);
    }
  }

  /**
   * Transform HTML into text using the Document Object Model
   *
   * @param string $html  HTML to transform into text
   *
   * @return string  transformed text
   */
  private function transformHtmlToText($html)
  {
    // Parse HTML
    $doc = new DOMDocument();
    $doc->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));

    // Apply transformations
    $this->transformDocument($doc);

    // Convert to string and strip leading/trailing whitespace
    return trim(htmlspecialchars_decode(strip_tags($doc->saveXml($doc->documentElement))));
  }

  /**
   * Transform specific tags within a DOM document
   *
   * @param DOMDocument  $doc  DOM document
   *
   * @return void
   */
  private function transformDocument(&$doc)
  {
    // Create text representations of various HTML tags
    $this->transformDocumentLinks($doc);
    $this->transformDocumentLists($doc);
    $this->transformDocumentDescriptionLists($doc);
    $this->transformDocumentBreaks($doc);

    // Deal with paragraphs last, as other transformations create them
    $this->transformDocumentParasIntoNewlines($doc);
  }

  /**
   * Transform link tags into text
   *
   * @param DOMDocument  $doc  DOM document
   *
   * @return void
   */
  private function transformDocumentLinks(&$doc)
  {
    $linkList = $doc->getElementsByTagName('a');

    // Loop through each <a> tag and replace with text content
    while ($linkList->length > 0)
    {
      $linkNode = $linkList->item(0);

      $linkText = $linkNode->textContent;
      $linkHref = $linkNode->getAttribute('href');

      if ($linkHref)
      {
        // Convert <a href="url">label</a> link to Redmine style "label":url link.
        $linkText = sprintf('"%s":%s', $linkText, $linkHref);
      }

      $newTextNode = $doc->createTextNode($linkText);
      $linkNode->parentNode->replaceChild($newTextNode, $linkNode);
    }
  }

  /**
   * Transform unordered list-related tags into text and enclose in
   * <p> tags
   *
   * @param DOMDocument  $doc  DOM document
   *
   * @return void
   */
  private function transformDocumentLists(&$doc)
  {
    $ulList = $doc->getElementsByTagName('ul');

    // Loop through each <ul> tag and change to a <p> tag
    while ($ulList->length > 0)
    {
      $listNode = $ulList->item(0);

      $newParaNode = $doc->createElement('p');

      // Assemble text representation of list
      $paraText = '';

      foreach($listNode->childNodes as $childNode)
      {
        $paraText .= '* '. $childNode->textContent ."\n";
      }

      // Set <p> element's text
      $newTextNode = $doc->createTextNode($paraText);
      $newParaNode->appendChild($newTextNode);

      $listNode->parentNode->replaceChild($newParaNode, $listNode);
    }
  }

  /**
   * Transform description list-related tags, removing description
   * terms and enclosing the description text in <p> tags
   *
   * @param DOMDocument  $doc  DOM document
   *
   * @return void
   */
  private function transformDocumentDescriptionLists(&$doc)
  {
    $termList = $doc->getElementsByTagName('dt');

    // Loop through each <dt> tag and remove it
    while ($termList->length > 0)
    {
      $termNode = $termList->item(0);
      $termNode->parentNode->removeChild($termNode);
    }

    $descriptionList = $doc->getElementsByTagName('dd');

    // Look through each <dd> element and change to a <p> element
    while ($descriptionList->length > 0)
    {
      $descriptionNode = $descriptionList->item(0);

      // Create <p> node with description's text
      $newParaNode = $doc->createElement('p');
      $newTextNode = $doc->createTextNode($descriptionNode->textContent);
      $newParaNode->appendChild($newTextNode);

      $descriptionNode->parentNode->replaceChild($newParaNode, $descriptionNode);
    }
  }

  /**
   * Transform break tags into newlines
   *
   * @param DOMDocument  $doc  DOM document
   *
   * @return void
   */
  private function transformDocumentBreaks(&$doc)
  {
    $breakList = $doc->getElementsByTagName('br');

    // Loop through each <p> and replace with text
    while ($breakList->length)
    {
      $breakNode = $breakList->item(0);

      $newTextNode = $doc->createTextNode("\n");

      $breakNode->parentNode->replaceChild($newTextNode, $breakNode);
    }
  }

  /**
   * Transform paragraph tags into newlines
   *
   * @param DOMDocument  $doc  DOM document
   *
   * @return void
   */
  private function transformDocumentParasIntoNewlines(&$doc)
  {
    $paraList = $doc->getElementsByTagName('p');

    // Loop through each <p> and replace with text
    while ($paraList->length)
    {
      $paraNode = $paraList->item(0);

      $paraText = "\n". $paraNode->textContent ."\n";
      $newTextNode = $doc->createTextNode($paraText);

      $paraNode->parentNode->replaceChild($newTextNode, $paraNode);
    }
  }
}
