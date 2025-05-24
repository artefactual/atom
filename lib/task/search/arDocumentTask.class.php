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
 * Display search index document.
 *
 * @author     Mike Cantelon <mike@artefactual.com>
 */
class arSearchDocumentTask extends arBaseTask
{
    /**
     * @see sfTask
     *
     * @param mixed $arguments
     * @param mixed $options
     */
    public function execute($arguments = [], $options = [])
    {
        parent::execute($arguments, $options);

        if (null !== $slugObject = QubitObject::getBySlug($arguments['slug'])) {
            $this->log(sprintf("Fetching data for %s ID %d...\n", $slugObject->className, $slugObject->id));

            // Dummy type is needed for ES 6, this should be updated in ES 7.x when type is not required for getDocument()
            $esType = QubitSearch::getInstance()::ES_TYPE;
            $doc = QubitSearch::getInstance()->index->getIndex($slugObject->className)->getType($esType)->getDocument($slugObject->id);

            echo json_encode($doc->getData(), JSON_PRETTY_PRINT)."\n";
        } else {
            throw new sfException('Slug not found');
        }
    }

    /**
     * @see sfTask
     */
    protected function configure()
    {
        $this->addArguments([
            new sfCommandArgument('slug', sfCommandArgument::REQUIRED, 'Slug of resource'),
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
        ]);

        $this->namespace = 'search';
        $this->name = 'document';
        $this->briefDescription = 'Output search index document data corresponding to an AtoM resource';
        $this->detailedDescription = <<<'EOF'
      Output search index document data corresponding to an AtoM resource
EOF;
    }
}
