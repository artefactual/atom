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
 * Populate search index.
 */
class arSolrSearchTask extends sfBaseTask
{
    public function execute($arguments = [], $options = [])
    {
        sfContext::createInstance($this->configuration);
        sfConfig::add(QubitSetting::getSettingsArray());

        new sfDatabaseManager($this->configuration);

        $solr = new arSolrPlugin($options);

        if (!$arguments['query']) {
          $this->log('Please specify a search query.');
        } else {
          $this->runSolrQuery($solr, $arguments['query'], (int)$options['rows'], (int)$options['start'], $option['fields']);
        }
    }

    protected function configure()
    {
        $this->addArguments([
            new sfCommandArgument('query', sfCommandArgument::REQUIRED, 'Search query.'),
        ]);

        $this->addOptions([
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application name', 'qubit'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'cli'),
            new sfCommandOption('start', null, sfCommandOption::PARAMETER_OPTIONAL, 'Offset for the result set output. 0 by default', 0),
            new sfCommandOption('rows', null, sfCommandOption::PARAMETER_OPTIONAL, 'Number of rows to return in the results', 5),
            new sfCommandOption('fields', null, sfCommandOption::PARAMETER_OPTIONAL, 'Fields to query("comma seperated")', null),
        ]);

        $this->namespace = 'solr';
        $this->name = 'search';

        $this->briefDescription = 'Search the search index for a result';
        $this->detailedDescription = <<<'EOF'
The [solr:search] task runs a search query on solr. Usage:
  php symfony solr:search <query>
EOF;
    }

    private function runSolrQuery($solrInstance, $queryText, $rows, $start, $fields) {
      $url = $solrInstance->getSolrUrl() .'/solr/'.$solrInstance->getSolrCollection().'/select';
      if(!$fields) {
          $fields = arSolrPluginUtil::getBoostedSearchFields([
              'identifier' => 10,
              'donors.i18n.en.authorizedFormOfName' => 10,
              'i18n.en.title' => 10,
              'i18n.en.scopeAndContent' => 10,
              'i18n.en.locationInformation' => 5,
              'i18n.en.processingNotes' => 5,
              'i18n.en.sourceOfAcquisition' => 5,
              'i18n.en.archivalHistory' => 5,
              //'i18n.en.appraisal' => 1,
              'i18n.en.physicalCharacteristics' => 1,
              'i18n.en.receivedExtentUnits' => 1,
              //'alternativeIdentifiers.i18n.en.name' => 1,
              'creators.i18n.en.authorizedFormOfName' => 1,
              //'alternativeIdentifiers.i18n.en.note' => 1,
              //'alternativeIdentifiers.type.i18n.en.name' => 1,
              //'accessionEvents.i18n.en.agent' => 1,
              //'accessionEvents.type.i18n.en.name' => 1,
              //'accessionEvents.notes.i18n.%s.content' => 1,
              'donors.contactInformations.contactPerson' => 1,
              'accessionEvents.dateString' => 1,
          ]);
      } else {
        $fields = arSolrPluginUtil::getBoostedSearchFields($fields);
      }

      $queryParams = [
        'params' => [
          'start' => $start,
          'rows' => $rows,
          'q.op' => 'AND',
          'defType' => 'edismax',
          'stopwords' => 'true',
          'q' => $queryText,
          'qf' => implode(' ', $fields),
        ]
      ];

      $response = arSolrPlugin::makeHttpRequest($url.$query, 'POST', json_encode($queryParams));

      $docs = $response->response->docs;
      if ($docs) {
          foreach ($docs as $resp) {
            $this->log(sprintf('%s - %s', $resp->id, $resp->{'i18n.en.title'}[0]));
          }
      } else {
        $this->log("No results found");
        $this->log(print_r($response->response, true));
        $this->log(print_r($queryParams, true));
      }
    }
}
