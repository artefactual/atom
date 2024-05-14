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

        $client = $solr->getClient();
        if (!$arguments['query']) {
          $this->log('Please specify a search query.');
        } else {
          $this->runSolrQuery($client, $arguments['query'], $options['rows']);
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
            new sfCommandOption('rows', null, sfCommandOption::PARAMETER_OPTIONAL, 'Number of rows to return in the results', 5),
            //new sfCommandOption('fields', null, sfCommandOption::PARAMETER_OPTIONAL, 'Fields to query("comma seperated")', null),
        ]);

        $this->namespace = 'solr';
        $this->name = 'search';

        $this->briefDescription = 'Search the search index for a result';
        $this->detailedDescription = <<<'EOF'
The [solr:search] task runs a search query on solr. Usage:
  php symfony solr:search <query>
EOF;
    }

    private function runSolrQuery($client, $queryText, $rows) {
      $query = new SolrQuery();
      $query->setQuery(arSolrPluginUtil::escapeTerm($queryText));

      $query->setStart(0);
      $rows ? $query->setRows((int)$rows) : $query->setRows(100);

      $searchResponse = $client->query($query);

      $response = $searchResponse->getResponse()->response;
      if ($response->docs) {
          foreach ($response->docs as $resp) {
            //$this->log(print_r($resp, true));
            $this->log(sprintf('%s - %s', $resp['id'], $resp['i18n.en.title'][0]));
          }
      } else {
        $this->log("No results found");
      }
    }
}
