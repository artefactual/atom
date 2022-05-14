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
 * Query the AtoM Elasticsearch index.
 *
 * @author David Juhasz <david@artefactual.com>
 */
class arSearchQueryTask extends arBaseTask
{
    /**
     * @see sfTask
     *
     * @param mixed $arguments
     * @param mixed $options
     */
    public function execute($arguments = [], $options = [])
    {
        $className = QubitInformationObject::class;

        parent::execute($arguments, $options);

        if ($options['search-fields']) {
            $this->outputSearchFields($className);

            exit(0);
        }

        if (!empty($arguments['query'])) {
            $this->doSearch($arguments['query'], $className, $options);
        } else {
            throw new sfCommandArgumentsException('Error: No query specified');
        }
    }

    /**
     * @see sfTask
     */
    protected function configure()
    {
        $this->addArguments([
            new sfCommandArgument(
                'query', sfCommandArgument::OPTIONAL, 'Search query'
            ),
        ]);

        $this->addOptions([
            new sfCommandOption(
                'application',
                null,
                sfCommandOption::PARAMETER_OPTIONAL,
                'The application name',
                true
            ),
            new sfCommandOption(
                'env',
                null,
                sfCommandOption::PARAMETER_REQUIRED,
                'The environment',
                'cli'
            ),
            new sfCommandOption(
                'connection',
                null,
                sfCommandOption::PARAMETER_REQUIRED,
                'The connection name',
                'propel'
            ),
            new sfCommandOption(
                'search-fields',
                null,
                sfCommandOption::PARAMETER_NONE,
                'List search fields and exit',
            ),
            new sfCommandOption(
                'limit',
                null,
                sfCommandOption::PARAMETER_REQUIRED,
                'Limit number of results returned',
                100
            ),
        ]);

        $this->namespace = 'search';
        $this->name = 'query';
        $this->briefDescription = 'Search Elasticsearch';
        $this->detailedDescription = <<<'EOF'
Search the AtoM Elasticsearch index using the specified query string
EOF;
    }

    protected function doSearch($query, $className, $options = [])
    {
        $boolQuery = arElasticSearchPluginUtil::generateBoolQueryString(
            $query, $this->getSearchFields($className)
        );

        $resultSet = QubitSearch::getInstance()
            ->index
            ->getType($className)
            ->search($boolQuery, $options['limit'])
        ;

        $this->outputSearchResults($resultSet);
    }

    protected function getSearchFields($className)
    {
        $type = lcfirst(str_replace('Qubit', '', $className));

        return arElasticSearchPluginUtil::getAllFields($type);
    }

    protected function outputSearchResults($resultSet)
    {
        $this->context->getConfiguration()->loadHelpers(['Qubit']);

        if (0 === $resultSet->getTotalHits()) {
            $this->log('No search results found');

            exit(0);
        }

        foreach ($resultSet->getResults() as $i => $result) {
            $data = $result->getData();

            $this->log(
                sprintf('[%d] id:%d, slug:%s, title:"%s" (score:%f)',
                    $i + 1,
                    $result->getId(),
                    $data['slug'],
                    get_search_i18n($data, 'title', ['allowEmpty' => false]),
                    $result->getScore(),
                )
            );
        }

        $this->log(
            sprintf(
                '>>> Returned %d of %d total search results. <<<',
                $i + 1,
                $resultSet->getTotalHits()
            )
        );
    }

    protected function outputSearchFields($className)
    {
        $count = 0;
        $fields = $this->getSearchFields($className);

        foreach ($fields as $field => $boost) {
            if (1 != $boost) {
                $field = sprintf('%s (boost: %d)', $field, $boost);
            }

            $this->log(sprintf('%d. %s', ++$count, $field));
        }
    }
}
