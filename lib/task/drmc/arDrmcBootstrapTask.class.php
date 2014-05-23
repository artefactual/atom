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

    $this->briefDescription = 'Bootstrap DRMC database';
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
      $this->addTerms();
      $this->addDrmcQueryTable();
      $this->addFixityReportTable();
    }

    if ($options['add-dummy-data'])
    {
      $this->addDummyAips();

      // The order matters, as the artwork record tree will try to reuse
      // existing technology records
      $this->addDummyTechnologyRecordTree();
      $this->addDummyArtworkRecordTree();
    }
  }

  protected function addLevelsOfDescriptions()
  {
    // Remove AtoM's defaults
    foreach (QubitTaxonomy::getTaxonomyTerms(QubitTaxonomy::LEVEL_OF_DESCRIPTION_ID, array('level' => 'top')) as $item)
    {
      $target = array('Fonds', 'Subfonds', 'Collection', 'Series', 'Subseries', 'File', 'Item', 'Part');
      $name = $item->getName(array('culture' => 'en'));
      if (in_array($name, $target))
      {
        $item->delete();
      }
    }

    // Levels of description specific for MoMA DRMC
    $levels = array(
      'Artwork record',
      'Description',
      'Component',
      'Artist supplied master',
      'Artist verified proof',
      'Archival master',
      'Exhibition format',
      'Documentation',
      'Miscellaneous',
      'Supporting technology record',
      'AIP',
      'Digital object'
    );

    // Find a specific level of description by its name (in English)
    $find = function($name)
    {
      $criteria = new Criteria;
      $criteria->add(QubitTerm::TAXONOMY_ID, QubitTaxonomy::LEVEL_OF_DESCRIPTION_ID);
      $criteria->addJoin(QubitTerm::ID, QubitTermI18n::ID);
      $criteria->add(QubitTermI18n::NAME, $name);
      $criteria->add(QubitTermI18n::CULTURE, 'en');

      return null !== QubitTerm::getOne($criteria);
    };

    foreach ($levels as $level)
    {
      // Don't duplicate
      if (true === $find($level))
      {
        continue;
      }

      $term = new QubitTerm;
      $term->name  = $level;
      $term->taxonomyId = QubitTaxonomy::LEVEL_OF_DESCRIPTION_ID;
      $term->parentId = QubitTerm::ROOT_ID;
      $term->culture = 'en';
      $term->save();
    }
  }

  protected function addTaxonomies()
  {
    $taxonomies = array(
      'Classifications',
      'Departments',
      'Component types',
      'Supporting technologies relation types');

    foreach ($taxonomies as $name)
    {
      $criteria = new Criteria;
      $criteria->add(QubitTaxonomy::PARENT_ID, QubitTaxonomy::ROOT_ID);
      $criteria->add(QubitTaxonomyI18n::NAME, $name);
      $criteria->add(QubitTaxonomyI18n::CULTURE, 'en');
      $criteria->addJoin(QubitTaxonomy::ID, QubitTaxonomyI18n::ID);
      if (null !== QubitTaxonomy::getOne($criteria))
      {
        continue;
      }

      $taxonomy = new QubitTaxonomy;
      $taxonomy->parentId = QubitTaxonomy::ROOT_ID;
      $taxonomy->name = $name;
      $taxonomy->culture = 'en';
      $taxonomy->save();
    }
  }

  protected function addTerms()
  {
    $terms = array(
      array(
        'parentId' => QubitTerm::ROOT_ID,
        'taxonomyId' => QubitTaxonomy::NOTE_TYPE_ID,
        'name' => 'InstallComments'
      ),
      array(
        'parentId' => QubitTerm::ROOT_ID,
        'taxonomyId' => QubitTaxonomy::NOTE_TYPE_ID,
        'name' => 'PrepComments'
      ),
      array(
        'parentId' => QubitTerm::ROOT_ID,
        'taxonomyId' => QubitTaxonomy::NOTE_TYPE_ID,
        'name' => 'StorageComments'
      ),
      array(
        'parentId' => QubitTerm::ROOT_ID,
        'taxonomyId' => QubitTaxonomy::RELATION_TYPE_ID,
        'name' => 'Supporting technology relation types'
      )
    );

    foreach ($terms as $item)
    {
      $criteria = new Criteria;
      $criteria->add(QubitTerm::PARENT_ID, $item['parentId']);
      $criteria->add(QubitTerm::TAXONOMY_ID, $item['taxonomyId']);
      $criteria->add(QubitTermI18n::NAME, $item['name']);
      $criteria->add(QubitTermI18n::CULTURE, 'en');
      $criteria->addJoin(QubitTerm::ID, QubitTermI18n::ID);
      if (null !== QubitTerm::getOne($criteria))
      {
        continue;
      }

      $term = new QubitTerm;
      $term->parentId = $item['parentId'];
      $term->taxonomyId = $item['taxonomyId'];
      $term->sourceCulture = 'en';
      $term->setName($item['name'], array('culture' => 'en'));
      $term->save();
    }

    $criteria = new Criteria;
    $criteria->add(QubitTaxonomyI18n::NAME, 'Supporting technologies relation types');
    $criteria->add(QubitTaxonomyI18n::CULTURE, 'en');
    $criteria->addJoin(QubitTaxonomy::ID, QubitTaxonomyI18n::ID);
    if (null !== $taxonomy = QubitTaxonomy::getOne($criteria))
    {
      foreach (array(
        'isPartOf',
        'isFormatOf',
        'isVersionOf',
        'references',
        'requires') as $type)
      {
        // Make sure that the term hasn't been added already
        $criteria = new Criteria;
        $criteria->add(QubitTerm::PARENT_ID, QubitTerm::ROOT_ID);
        $criteria->add(QubitTerm::TAXONOMY_ID, $taxonomy->id);
        $criteria->add(QubitTermI18n::CULTURE, 'en');
        $criteria->add(QubitTermI18n::NAME, $type);
        $criteria->addJoin(QubitTerm::ID, QubitTermI18n::ID);
        if (null !== QubitTerm::getOne($criteria))
        {
          continue;
        }

        $term = new QubitTerm;
        $term->parentId = QubitTerm::ROOT_ID;
        $term->taxonomyId = $taxonomy->id;
        $term->sourceCulture = 'en';
        $term->setName($type, array('culture' => 'en'));
        $term->save();
      }
    }
  }

  protected function addDrmcQueryTable()
  {
    $sql = <<<sql

DROP TABLE IF EXISTS `drmc_query`;

sql;

    QubitPdo::modify($sql);

    $sql = <<<sql

CREATE TABLE `drmc_query`
(
  `id` INTEGER  NOT NULL,
  `type` VARCHAR(20),
  `name` VARCHAR(255),
  `description` VARCHAR(1024),
  `query` TEXT,
  `user_id` INTEGER,
  `created_at` DATETIME  NOT NULL,
  `updated_at` DATETIME  NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `drmc_query_FK_1`
    FOREIGN KEY (`id`)
    REFERENCES `object` (`id`)
    ON DELETE CASCADE,
  INDEX `drmc_query_FI_2` (`user_id`),
  CONSTRAINT `drmc_query_FK_2`
    FOREIGN KEY (`user_id`)
    REFERENCES `user` (`id`)
    ON DELETE SET NULL
)Engine=InnoDB;

sql;

    QubitPdo::modify($sql);
  }

  protected function addFixityReportTable()
  {
    $sql = <<<sql

DROP TABLE IF EXISTS `fixity_report`;

sql;

    QubitPdo::modify($sql);

    $sql = <<<sql

CREATE TABLE `fixity_report`
(
  `id` INTEGER  NOT NULL,
  `success` TINYINT,
  `message` VARCHAR(255),
  `failures` TEXT,
  `session_uuid` VARCHAR(36),
  `aip_id` INTEGER,
  `uuid` VARCHAR(36),
  `time_started` DATETIME,
  `time_completed` DATETIME,
  PRIMARY KEY (`id`),
  CONSTRAINT `fixity_report_FK_1`
    FOREIGN KEY (`id`)
    REFERENCES `object` (`id`)
    ON DELETE CASCADE,
  INDEX `fixity_report_FI_2` (`aip_id`),
  CONSTRAINT `fixity_report_FK_2`
    FOREIGN KEY (`aip_id`)
    REFERENCES `aip` (`id`)
    ON DELETE SET NULL
)Engine=InnoDB;

sql;

    QubitPdo::modify($sql);
  }

  protected function addDummyAips()
  {
    $names = file(sfConfig::get('sf_plugins_dir').'/arDrmcPlugin/frontend/mock_api/sample_data/names.txt');

    for ($i = 1; $i <= 50; $i++)
    {
      // Make new info object every few AIPs
      if (!$infoObject || rand(1, 3))
      {
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
      $aip->filename = trim($names[array_rand($names)]);
      $aip->digitalObjectCount = 1;
      $aip->partOf = $infoObject->id;
      $aip->sizeOnDisk = rand(1000, 10000000);
      $aip->createdAt = date('c'); // date is in ISO 8601

      $aip->save();
    }
  }

  protected function addDummyArtworkRecordTree()
  {
    $json = <<<EOT
{
    "level": "Artwork record",
    "title": "Play Dead; Real Time",
    "children": [
        {
            "children": [
                {
                    "level": "AIP",
                    "title": "Installation documentation",
                    "children": [
                        {
                            "level": "Digital object",
                            "title": "Picture I",
                            "filename": "pic-1.png"
                        },
                        {
                            "level": "Digital object",
                            "title": "Picture II",
                            "filename": "pic-2.png"
                        },
                        {
                            "level": "Digital object",
                            "title": "Picture III",
                            "filename": "pic-3.png",
                            "supporting_technologies": [
                                "FLAC",
                                "Vorbis"
                            ]
                        }
                    ]
                },
                {
                    "children": [
                        {
                            "level": "Exhibition format",
                            "title": "1098.2005.a.AV",
                            "supporting_technologies": [
                                "FLAC"
                            ]
                        },
                        {
                            "level": "Exhibition format",
                            "title": "1098.2005.b.AV"
                        },
                        {
                            "level": "Exhibition format",
                            "title": "1098.2005.c.AV"
                        }
                    ],
                    "level": "Description",
                    "title": "Exhibition files"
                }
            ],
            "level": "Description",
            "title": "MoMA 2012"
        },
        {
            "children": [
                {
                    "children": [
                        {
                            "level": "Artist verified proof",
                            "title": "1098.2005.a.x2"
                        },
                        {
                            "level": "Artist verified proof",
                            "title": "1098.2005.a.x3",
                            "supporting_technologies": [
                                "WMV 8"
                            ]
                        }
                    ],
                    "level": "Artist supplied master",
                    "title": "1098.2005.a.x1"
                },
                {
                    "children": [
                        {
                            "level": "Artist verified proof",
                            "title": "1098.2005.b.x2"
                        },
                        {
                            "level": "Artist verified proof",
                            "title": "1098.2005.b.x3"
                        }
                    ],
                    "level": "Artist supplied master",
                    "title": "1098.2005.b.x1"
                },
                {
                    "children": [
                        {
                            "level": "Artist verified proof",
                            "title": "1098.2005.c.x2"
                        },
                        {
                            "level": "Artist verified proof",
                            "title": "1098.2005.c.x3"
                        }
                    ],
                    "level": "Artist supplied master",
                    "title": "1098.2005.c.x1"
                }
            ],
            "level": "Description",
            "title": "Supplied by artist"
        },
        {
            "children": [
                {
                    "level": "Archival master",
                    "title": "1098.2005.a.x4"
                },
                {
                    "level": "Archival master",
                    "title": "1098.2005.b.x4"
                },
                {
                    "level": "Archival master",
                    "title": "1098.2005.c.x4"
                }
            ],
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

        if (isset($item['supporting_technologies']))
        {
          foreach ($item['supporting_technologies'] as $item)
          {
            // Populate or create technology record
            $criteria = new Criteria;
            $criteria->add(QubitInformationObject::LEVEL_OF_DESCRIPTION_ID, sfConfig::get('app_drmc_lod_supporting_technology_record_id'));
            $criteria->add(QubitInformationObjectI18n::TITLE, $item);
            $criteria->addJoin(QubitInformationObject::ID, QubitInformationObjectI18n::ID);
            if (null === $tr = QubitInformationObject::getOne($criteria))
            {
              $tr = new QubitInformationObject;
              $tr->parentId = QubitInformationObject::ROOT_ID;
              $tr->levelOfDescriptionId = sfConfig::get('app_drmc_lod_supporting_technology_record_id');
              $tr->setPublicationStatusByName('Published');
              $tr->title = $item;
              $tr->save();
            }

            // Associate technology record
            // Technology record will be always the object?
            $relation = new QubitRelation;
            $relation->subjectId = $io->id;
            $relation->objectId = $tr->id;
            $relation->typeId = QubitTerm::AIP_RELATION_ID;
            $relation->save();
          }
        }

        if (isset($item['children']))
        {
          $add($item['children'], $io->id);
        }
      }
    };

    $add(array($tree));
  }

  protected function addDummyTechnologyRecordTree()
  {
    $json = <<<EOT
{
    "title": "Codecs",
    "children": [
        {
            "title": "Video codecs",
            "children": [
                {
                    "title": "Open source",
                    "children": [
                        {
                            "title": "x264"
                        },
                        {
                            "title": "x265"
                        },
                        {
                            "title": "Xvid"
                        }
                    ]
                },
                {
                    "title": "Propietary",
                    "children": [
                        {
                            "title": "WMV 7"
                        },
                        {
                            "title": "WMV 8"
                        },
                        {
                            "title": "WMV 9"
                        }
                    ]
                }
            ]
        },
        {
            "title": "Audio codecs",
            "children": [
                {
                    "title": "Open source",
                    "children": [
                        {
                            "title": "FLAC"
                        },
                        {
                            "title": "Vorbis"
                        }
                    ]
                },
                {
                    "title": "Propietary",
                    "children": [
                        {
                            "title": "WMA"
                        },
                        {
                            "title": "MP3"
                        }
                    ]
                }
            ]
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

        $io = new QubitInformationObject;
        $io->setLevelOfDescriptionByName('Supporting technology record');
        $io->setPublicationStatusByName('Published');
        $io->parentId = $parentId;
        $io->title = $item['title'];
        $io->culture = 'en';

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

function gen_uuid()
{
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
