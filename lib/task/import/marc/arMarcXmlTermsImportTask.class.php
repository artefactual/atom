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
 * Parse MARC XML file and create QubitTerms from <marc:record> elements
 *
 * @package    symfony
 * @subpackage task
 */
class arMarcXmlTermsImportTask extends arBaseTask
{
  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('file', sfCommandArgument::REQUIRED, 'The MARC XML input file'),
      new sfCommandArgument('taxonomy', sfCommandArgument::REQUIRED, 'Taxonomy slug where to import the terms')
    ));

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application name', true),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'cli'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'propel')
    ));

    $this->namespace = 'import';
    $this->name = 'marc-xml';
    $this->briefDescription = 'Import terms from a MARC XML file to a taxonomy';

    $this->detailedDescription = <<<EOF
Parse a MARC XML file and create QubitTerms from <marc:record> elements, the terms
will be part of a taxanomy specified by slug.
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

    $taxonomy = QubitTaxonomy::getBySlug($arguments['taxonomy']);

    if (!isset($taxonomy))
    {
      $this->log('Could not find taxonomy with slug: ' . $arguments['taxonomy']);

      return;
    }

    if (in_array($taxonomy->id, QubitTaxonomy::$lockedTaxonomies))
    {
      $this->log(sprintf('Taxonomy "%s" is locked.', $taxonomy));

      return;
    }

    $parser = new arMarcXmlParser($this->dispatcher, $this->formatter, $taxonomy);

    if (!$parser->parse($arguments['file']))
    {
      $errorData = $parser->getErrorData();
      $this->log($this->context->i18n->__('SAX xml parse error %code% on line %line% in input file: %message%', array('%code%' => $errorData['code'], '%message%' => $errorData['string'], '%line%' => $errorData['line'])));
    }
  }
}
