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
 * Import csv repository data
 *
 * @package    symfony
 * @subpackage task
 * @author     Mike Gale <MikeG@Artefactual.com>
 * @author     Mike Cantelon <mike@artefactual.com>
 */

class csvRepositoryImportTask extends csvImportBaseTask
{
  protected $namespace           = 'csv';
  protected $name                = 'repository-import';
  protected $briefDescription    = 'Import csv repository data';
  protected $detailedDescription = <<<EOF
Import CSV repository data
EOF;

  /**
   * @see csvImportBaseTask
   */
  protected function configure()
  {
    parent::configure();

    $this->addOptions(array(new sfCommandOption('update', null, sfCommandOption::PARAMETER_NONE,
      'Update existing repositories if names match. Create a new repository otherwise.')));

    $this->addOptions(array(new sfCommandOption('upload-limit', null, sfCommandOption::PARAMETER_OPTIONAL,
      'Set the upload limit for repositories getting imported (default: disable uploads)')));

    $this->addOptions(array(new sfCommandOption('source-name', null, sfCommandOption::PARAMETER_OPTIONAL,
      'Set the source name for this import')));
  }

  /**
   * @see sfTask
   */
  public function execute($arguments = array(), $options = array())
  {
    $this->validateOptions($options);

    $this->logSection("Importing repository objects from CSV to AtoM");

    $skipRows = ($options['skip-rows']) ? $options['skip-rows'] : 0;

    if (false === $fh = fopen($arguments['filename'], 'rb'))
    {
      throw new sfException('You must specify a valid filename');
    }

    $databaseManager = new sfDatabaseManager($this->configuration);
    $conn = $databaseManager->getDatabase('propel')->getConnection();

    // Load taxonomies into variables to avoid use of magic numbers
    $termData = QubitFlatfileImport::loadTermsFromTaxonomies(array(
      QubitTaxonomy::NOTE_TYPE_ID                => 'noteTypes',
      QubitTaxonomy::ACTOR_ENTITY_TYPE_ID        => 'actorTypes',
      QubitTaxonomy::ACTOR_RELATION_TYPE_ID      => 'actorRelationTypes',
      QubitTaxonomy::DESCRIPTION_STATUS_ID       => 'descriptionStatusTypes',
      QubitTaxonomy::DESCRIPTION_DETAIL_LEVEL_ID => 'detailLevelTypes'
    ));

    if (!isset($options['source-name']) || !$options['source-name'])
    {
      $options['source-name'] = $arguments['filename']; // Default source-name to the file name
    }

    $import = new QubitFlatfileImport(array(
      'context' => sfContext::createInstance($this->configuration),
      'className' => 'QubitRepository',

      /* the status array is a place to put data that should be accessible
         from closure logic using the getStatus method */
      'status' => array(
        'update'    => $options['update'],
        'sourceName' => $options['source-name']
      ),

      /* import columns that map directory to QubitRepository properties */
      'standardColumns' => array(
        'authorizedFormOfName',
        'identifier',
        'openingTimes',
        'geoculturalContext',
        'holdings',
        'findingAids',
        'uploadLimit',
        'history',
        'mandates',
        'internalStructures',
        'collectingPolicies',
        'buildings',
        'accessConditions',
        'disabledAccess',
        'researchServices',
        'reproductionServices',
        'publicFacilities',
        'descIdentifier',
        'descInstitutionIdentifier',
        'descRules',
        'descRevisionHistory',
        'descSources',
        'culture'
      ),

      'columnMap' => array(
      ),

      /* import columns that can be added as QubitNote objects */
      'noteMap' => array(
      ),

      /* these values get stored to the rowStatusVars array */
      'variableColumns' => array(
        'contactPerson',
        'streetAddress',
        'phone',
        'email',
        'fax',
        'city',
        'region',
        'website',
        'legacyId',
        'parallelFormsOfName',
        'otherFormsOfName',
        'types',
        # TODO: Parse the below fields

        'languages',
        'scripts',
        'thematicAreas',
        'geographicSubregions',
        'descStatus',
        'descDetail'
      ),

      /* import logic to execute before saving QubitRepository */
      'preSaveLogic' => function(&$self)
      {
        $opts = $self->getStatus('options');

        if (isset($opts['upload-limit']) && !isset($self->object->uploadLimit))
        {
          $self->object->uploadLimit = $opts['upload-limit'];
        }

        addTypes($self->rowStatusVars['types'], $self->object);
        addScriptsAndLangs($self->object, $self->rowStatusVars);
      },

      /* import logic to execute after saving QubitRepository */
      'postSaveLogic' => function(&$self)
      {
        createContact($self->object->id, $self->rowStatusVars);
        createKeymapEntry($self->rowStatusVars['legacyId'], $self->getStatus('sourceName'), $self->object->id);

        createOtherNames($self->object->id, QubitTerm::PARALLEL_FORM_OF_NAME_ID,
                         $self->rowStatusVars['parallelFormsOfName']);

        createOtherNames($self->object->id, QubitTerm::OTHER_FORM_OF_NAME_ID,
                         $self->rowStatusVars['otherFormsOfName']);
      }
    ));

    $import->csv($fh, $skipRows);
    $this->logSection("Imported repositories successfully!");
  }
}

function addScriptsAndLangs($repo, $rowStatusVars)
{
  $langCodeConvertor = new fbISO639_Map;

  $repo->script = array();
  $repo->language = array();

  $scripts = array();

  foreach (explode('|', $rowStatusVars['scripts']) as $script)
  {
    $script = trim($script);

    if (!$script)
    {
      continue;
    }

    $scripts[] = sfEacPlugin::from6392($script);
  }

  $languages = array();

  foreach (explode('|', $rowStatusVars['languages']) as $lang)
  {
    $lang = trim($lang);

    if (!$lang)
    {
      continue;
    }

    $languages[] = sfEacPlugin::from6392($lang);
  }

  $repo->language = $languages;
  $repo->script = $scripts;
}

function addTypes($types, $repo)
{
  foreach (explode('|', $types) as $type)
  {
    $type = trim($type);

    if (!$type)
    {
      continue;
    }

    $repo->setTypeByName($type);
  }
}

function createOtherNames($objectId, $typeId, $names)
{
  foreach (explode('|', $names) as $name)
  {
    $name = trim($name);

    if (!$name)
    {
      continue;
    }

    $item = new QubitOtherName;
    $item->name     = $name;
    $item->typeId   = $typeId;
    $item->objectId = $objectId;
    $item->save();
  }
}

function createContact($actorId, $rowStatusVars)
{
  $info = new QubitContactInformation();
  $info->actorId = $actorId;

  $info->contactPerson = $rowStatusVars['contactPerson'];
  $info->streetAddress = $rowStatusVars['streetAddress'];
  $info->phone         = $rowStatusVars['phone'];
  $info->email         = $rowStatusVars['email'];
  $info->fax           = $rowStatusVars['fax'];
  $info->website       = $rowStatusVars['website'];
  $info->city          = $rowStatusVars['city'];
  $info->region        = $rowStatusVars['region'];

  $info->save();
}

function createKeymapEntry($legacyId, $sourceName, $id)
{
  $sql = 'SELECT count(1) FROM keymap WHERE source_id=? AND source_name=? AND target_id=? AND target_name=?';

  if (QubitPdo::fetchColumn($sql, array($legacyId, $sourceName, $id, 'repository')) > 0)
  {
    return; // Avoid duplicate keymap entries
  }

  $keymap = new QubitKeymap;
  $keymap->sourceId   = $legacyId;
  $keymap->sourceName = $sourceName;
  $keymap->targetId   = $id;
  $keymap->targetName = 'repository';
  $keymap->save();
}
