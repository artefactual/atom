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
 * arElasticSearchPlugin main class
 *
 * @package     AccesstoMemory
 * @subpackage  search
 */
class arDrmcBootstrapTask extends sfBaseTask
{
  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application name', 'qubit'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'cli'),
      new sfCommandOption('init', null, sfCommandOption::PARAMETER_NONE, 'Initialize'),
      new sfCommandOption('add-dummy-data', null, sfCommandOption::PARAMETER_NONE, 'Add dummy data'),
      new sfCommandOption('verbose', 'v', sfCommandOption::PARAMETER_NONE, 'If passed, progress is displayed for each object indexed')));

    $this->namespace = 'drmc';
    $this->name = 'bootstrap';

    $this->briefDescription = 'Bootstrap DRMC-MA database';
    $this->detailedDescription = <<<EOF
The [drmc:bootstrap|INFO] task adds the necessary initial data to your database
EOF;
  }

  public function execute($arguments = array(), $options = array())
  {
    sfContext::createInstance($this->configuration);
    new sfDatabaseManager($this->configuration);

    if ($options['init'])
    {
      $this->addLevelsOfDescriptions();
      $this->addTaxonomies();
      $this->addNoteTypes();
    }

    if ($options['add-dummy-data'])
    {
      $this->addDummyAips();
      $this->addDummyTree();
    }
  }

  protected function addLevelsOfDescriptions()
  {
    // Remove AtoM's defaults
    foreach (QubitTaxonomy::getTaxonomyTerms(QubitTaxonomy::LEVEL_OF_DESCRIPTION_ID, array('level' => 'top')) as $item)
    {
      $target = array('Fonds', 'Subfonds', 'Collection', 'Series', 'Subseries', 'File', 'Item');
      $name = $item->getName(array('culture' => 'en'));
      if (in_array($name, $target))
      {
        $item->delete();
      }
    }

    // Levels of description specific for MoMA DRMC-MA
    $levels = array(
      array('name' => 'Artwork record'),
      array('name' => 'Description'),
      array('name' => 'Component', 'children' => array(
        array('name' => 'Artist supplied master'),
        array('name' => 'Artist verified proof'),
        array('name' => 'Archival master'),
        array('name' => 'Exhibition format'),
        array('name' => 'Documentation'),
        array('name' => 'Miscellaneous'))),
      array('name' => 'Supporting technology record'),
      array('name' => 'AIP', 'children' => array(
        array('name' => 'Digital object'))));

    // Find a specific level of description by its name (in English)
    $find = function($name)
    {
      $criteria = new Criteria;
      $criteria->addJoin(QubitTerm::ID, QubitTermI18n::ID);
      $criteria->add(QubitTermI18n::NAME, $name);
      $criteria->add(QubitTermI18n::CULTURE, 'en');

      return null !== QubitTerm::getOne($criteria);
    };

    // Parse $levels recursively and add the levels to the taxonomy
    $add = function($levels, $parentId = false) use (&$find, &$add)
    {
      if (false === $parentId)
      {
        $parentId = QubitTerm::ROOT_ID;
      }

      foreach ($levels as $level)
      {
        // Don't duplicate
        if (true === $find($level['name']))
        {
          continue;
        }

        $term = new QubitTerm;
        $term->name  = $level['name'];
        $term->taxonomyId = QubitTaxonomy::LEVEL_OF_DESCRIPTION_ID;
        // $term->code
        $term->parentId = $parentId;
        $term->culture = 'en';
        $term->save();

        if (isset($level['children']))
        {
          $add($level['children'], $term->id);
        }
      }
    };

    $add($levels);
  }

  protected function addTaxonomies()
  {
    $taxonomies = array(
      'Classifications',
      'Departments',
      'Component types');

    foreach ($taxonomies as $name)
    {
      $taxonomy = new QubitTaxonomy;
      $taxonomy->parentId = QubitTaxonomy::ROOT_ID;
      $taxonomy->name = $name;
      $taxonomy->culture = 'en';
      $taxonomy->save();
    }
  }

  protected function addNoteTypes()
  {
    $noteTypes = array(
      'InstallComments',
      'PrepComments',
      'StorageComments');

    foreach ($noteTypes as $name)
    {
      $term = new QubitTerm;
      $term->parentId = QubitTerm::ROOT_ID;
      $term->taxonomyId = QubitTaxonomy::NOTE_TYPE_ID;
      $term->name = $name;
      $term->culture = 'en';
      $term->save();
    }
  }

  protected function addDummyAips()
  {
    $names = file(sfConfig::get('sf_plugins_dir').'/arDrmcPlugin/frontend/mock_api/sample_data/names.txt');

    for ($i = 1; $i <= 50; $i++)
    {
      // Make new info object every few AIPs
      if (!$infoObject || rand(1, 3)) {
        $infoObject = new QubitInformationObject();
        $infoObject->title = generateRandomString(20);
        $infoObject->levelOfDescriptionId = sfConfig::get('app_drmc_lod_artwork_record_id');
        $infoObject->parentId = QubitInformationObject::ROOT_ID;
        $infoObject->save();
      }

      // Store AIP data
      $aip = new QubitAip;
      $aip->typeId = rand(179, 182);
      $aip->uuid = gen_uuid();
      $aip->filename = $names[array_rand($names)];
      $aip->digitalObjectCount = 1;
      $aip->partOf = $infoObject->id;
      $aip->sizeOnDisk = rand(1000, 10000000);
      $aip->createdAt = date('c'); // date is in ISO 8601

      $aip->save();
    }
  }

  protected function addDummyTree()
  {
    $json = <<<EOT
{
    "id": 1,
    "level": "Artwork record",
    "title": "Play Dead; Real Time Pana",
    "children": [
        {
            "children": [
                {
                    "id": 3,
                    "level": "AIP",
                    "title": "Installation documentation",
                    "children": [
                        {
                            "id": 900,
                            "level": "Digital object",
                            "title": "Picture I",
                            "filename": "pic-1.png"
                        },
                        {
                            "id": 901,
                            "level": "Digital object",
                            "title": "Picture II",
                            "filename": "pic-2.png"
                        },
                        {
                            "id": 902,
                            "level": "Digital object",
                            "title": "Picture III",
                            "filename": "pic-3.png"
                        }
                    ]
                },
                {
                    "children": [
                        {
                            "id": 5,
                            "level": "Exhibition format",
                            "title": "1098.2005.a.AV"
                        },
                        {
                            "id": 6,
                            "level": "Exhibition format",
                            "title": "1098.2005.b.AV"
                        },
                        {
                            "id": 7,
                            "level": "Exhibition format",
                            "title": "1098.2005.c.AV"
                        }
                    ],
                    "id": 4,
                    "level": "Description",
                    "title": "Exhibition files"
                }
            ],
            "id": 2,
            "level": "Description",
            "title": "MoMA 2012"
        },
        {
            "children": [
                {
                    "children": [
                        {
                            "id": 10,
                            "level": "Artist verified proof",
                            "title": "1098.2005.a.x2"
                        },
                        {
                            "id": 11,
                            "level": "Artist verified proof",
                            "title": "1098.2005.a.x3"
                        }
                    ],
                    "id": 9,
                    "level": "Artist supplied master",
                    "title": "1098.2005.a.x1"
                },
                {
                    "children": [
                        {
                            "id": 13,
                            "level": "Artist verified proof",
                            "title": "1098.2005.b.x2"
                        },
                        {
                            "id": 14,
                            "level": "Artist verified proof",
                            "title": "1098.2005.b.x3"
                        }
                    ],
                    "id": 12,
                    "level": "Artist supplied master",
                    "title": "1098.2005.b.x1"
                },
                {
                    "children": [
                        {
                            "id": 16,
                            "level": "Artist verified proof",
                            "title": "1098.2005.c.x2"
                        },
                        {
                            "id": 17,
                            "level": "Artist verified proof",
                            "title": "1098.2005.c.x3"
                        }
                    ],
                    "id": 15,
                    "level": "Artist supplied master",
                    "title": "1098.2005.c.x1"
                }
            ],
            "id": 8,
            "level": "Description",
            "title": "Supplied by artist"
        },
        {
            "children": [
                {
                    "id": 31,
                    "level": "Archival master",
                    "title": "1098.2005.a.x4"
                },
                {
                    "id": 32,
                    "level": "Archival master",
                    "title": "1098.2005.b.x4"
                },
                {
                    "id": 33,
                    "level": "Archival master",
                    "title": "1098.2005.c.x4"
                }
            ],
            "id": 30,
            "level": "Description",
            "title": "Digital archival masters"
        }
    ]
}
EOT
;
    $tree = json_decode($json, true);

    $add = function($items, $parentId = false) use (&$add)
    {
      foreach ($items as $item)
      {
        if (false === $parentId)
        {
          $parentId = QubitInformationObject::ROOT_ID;
        }

        var_dump($item['title']);

        $io = new QubitInformationObject;
        $io->setLevelOfDescriptionByName($item['level']);
        $io->setPublicationStatusByName('Published');
        $io->parentId = $parentId;
        $io->title = $item['title'];
        $io->culture = 'en';

        if ($item['level'] === 'Digital object')
        {
          $item = sfConfig::get('sf_root_dir').'/lib/task/drmc/'.$item['filename'];

          $do = new QubitDigitalObject;
          $do->assets[] = new QubitAsset($item);
          $do->usageId = QubitTerm::MASTER_ID;
          $io->digitalObjects[] = $do;
        }

        $io->save();

        if (isset($item['children']))
        {
          $add($item['children'], $io->id);
        }
      }
    };

    $add(array($tree));
  }
}

function gen_uuid() {
  return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
    // 32 bits for "time_low"
    mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
    // 16 bits for "time_mid"
    mt_rand( 0, 0xffff ),
    // 16 bits for "time_hi_and_version",
    // four most significant bits holds version number 4
    mt_rand( 0, 0x0fff ) | 0x4000,
    // 16 bits, 8 bits for "clk_seq_hi_res",
    // 8 bits for "clk_seq_low",
    // two most significant bits holds zero and one for variant DCE1.1
    mt_rand( 0, 0x3fff ) | 0x8000,
    // 48 bits for "node"
    mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
  );
}

function generateRandomString($length = 10) {
  $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
  $randomString = '';
  for ($i = 0; $i < $length; $i++) {
    $randomString .= $characters[rand(0, strlen($characters) - 1)];
  }
  return $randomString;
}
