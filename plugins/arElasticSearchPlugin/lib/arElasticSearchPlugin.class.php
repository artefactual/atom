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
 * @subpackage  arElasticSearchPlugin
 * @author      MJ Suhonos <mj@suhonos.ca>
 * @author      Jesús García Crepso <jesus@sevein.com>
 */
class arElasticSearchPlugin extends QubitSearchEngine
{
  /**
   * Elastic_Client object
   *
   * @var mixed Defaults to null.
   */
  protected $client = null;

  /**
   * Elastic_Index object
   *
   * @var mixed Defaults to null.
   */
  protected $index = null;

  /**
   * Constructor
   */
  public function __construct()
  {
    $this->config = arElasticSearchPluginConfiguration::$config;

    $this->client = new Elastica_Client($this->config['server']);

    $this->index = $this->client->getIndex($this->config['index']['name']);

    $this->initialize();
  }

  public function __destruct()
  {

  }

  /**
   * Initialize ES index if it does not exist
   */
  protected function initialize()
  {
    try
    {
      // Uncomment if you want to delete the index and create it again
      // $this->index->delete();

      $this->index->open();
    }
    catch (Exception $e)
    {
      // If the index has not been initialized, create it
      if ($e instanceof Elastica_Exception_Response)
      {
        $this->index->create($this->config['index']['configuration'], true);
      }

      foreach ($this->config['mappings'] as $typeName => $typeProperties)
      {
        // If _attributes is set, parse and delete it
        if ('__attributes' == $typeName)
        {
          foreach ($typeProperties as $attributeName => $attributeValue)
          {
            if (!$attributeValue)
            {
              continue;
            }

            switch ($attributeName)
            {
              case 'i18n':
                $typeProperties['source_culture'] = array('type' => 'string', 'index' => 'not_analyzed', 'include_in_all' => false);
                $typeProperties['i18n'] = array(
                  'type' => 'object',
                  'include_in_root' => true,
                  'properties' => array(
                    'culture' => array('type' => 'string', 'index' => 'not_analyzed', 'include_in_all' => false)));

                break;

              case 'timestamp':
                $typeProperties['created_at'] = array('type' => 'date');
                $typeProperties['updated_at'] = array('type' => 'date');

                break;
            }
          }

          unset($typeProperties['_attributes']);
        }

        // Define mapping in elasticsearch
        $mapping = new Elastica_Type_Mapping();
        $mapping->setType($this->index->getType($typeName));
        $mapping->setProperties($typeProperties);
        $mapping->send();
      }
    }
  }

  /**
   * Optimize ES index
   */
  public function optimize($args = array())
  {
    return $this->client->optimizeAll($args);
  }
}
