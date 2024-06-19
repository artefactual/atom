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
            $this->runSolrQuery($solr, $arguments['query'], (int) $options['rows'], (int) $options['start'], $options['fields']);
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

To get paginated results, use rows and start. For example:
  php symfony solr:search fonds --rows=5 --start=10

This wll get 5 search results starting from the 10th result

To search specific fields use the --fields option. For example:
  php symfony solr:search fonds --fields=i18n.%s.title^10,identifier^5

This will search only i18n.(language code).title and identifier fields and
boost them by 10 and 5 respectively

EOF;
    }

    private function runSolrQuery($solrInstance, $queryText, $rows, $start, $fields)
    {
        $query = new arSolrQuery(arSolrPluginUtil::escapeTerm($queryText));
        $query->setSize($rows);
        $query->setOffset($start);
        if ($fields) {
            $fieldsArr = explode(',', $fields);
            $newFields = [];
            foreach ($fieldsArr as $field) {
                $newField = explode('^', $field);
                $fieldName = $newField[0];
                $fieldBoost = $newField[1];
                if (!$fieldBoost) {
                    $fieldBoost = 1;
                }
                $newFields[$fieldName] = (int) $fieldBoost;
            }
            $query->setFields(arSolrPluginUtil::getBoostedSearchFields($newFields));
        }

        $docs = $solrInstance->search($query, 'QubitInformationObject');
        if ($docs) {
            foreach ($docs as $resp) {
                $this->log(sprintf('%s - %s', $resp->id, $resp->{'QubitInformationObject.i18n.en.title'}[0]));

                // print entire object if no title is present
                if (!$resp->{'QubitInformationObject.i18n.en.title'}[0]) {
                    $this->log(var_export($resp, true));
                }
            }
        } else {
            $this->log('No results found');
            $this->log(print_r($docs, true));
        }
    }
}
