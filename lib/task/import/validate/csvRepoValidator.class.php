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
 * CSV Repository Validator. Check if repo exists or not in the AtoM DB.
 * Issue a warning if the CSV will result in the creation of a new Repo.
 *
 * @author     Steve Breker <sbreker@artefactual.com>
 */
class CsvRepoValidator extends CsvBaseValidator
{
    public const TITLE = 'Repository Check';
    public const LIMIT_TO = ['QubitInformationObject'];

    protected $existingRepositories = [];
    protected $newRepositories = [];

    public function __construct(?array $options = null)
    {
        $this->setTitle(self::TITLE);
        parent::__construct($options);

        $this->setRequiredColumns(['repository']);
    }

    public function reset()
    {
        $this->newRepositories = [];

        parent::reset();
    }

    public function testRow(array $header, array $row)
    {
        if (!parent::testRow($header, $row)) {
            return;
        }

        $row = $this->combineRow($header, $row);

        if (empty($row['repository'])) {
            return;
        }

        if (!$this->repositoryExists($row['repository'])) {
            $this->testData->addDetail(implode(',', $row));
        }
    }

    public function getTestResult()
    {
        if (!$this->columnPresent('repository')) {
            // Repository column not present in file.
            $this->testData->addResult(sprintf("'repository' column not present in file."));

            return parent::getTestResult();
        }

        if ($this->columnDuplicated('repository')) {
            $this->appendDuplicatedColumnError('repository');

            return parent::getTestResult();
        }

        if (0 < count($this->newRepositories)) {
            $this->testData->setStatusWarn();
            $this->testData->addResult(sprintf('Number of NEW repository records that will be created by this CSV: %s', count($this->newRepositories)));
            $this->testData->addResult(sprintf('New repository records will be created for: %s', implode(',', $this->newRepositories)));
        } else {
            $this->testData->addResult('No issues detected with repository values.');
        }

        return parent::getTestResult();
    }

    protected function repositoryExists($name)
    {
        if (isset($this->existingRepositories[$name])) {
            return true;
        }

        $repo = $this->ormClasses['QubitFlatfileImport']::createOrFetchRepository(
            $name,
            true
        );

        if (null !== $repo) {
            $this->existingRepositories[$repo->authorizedFormOfName] = $repo->authorizedFormOfName;

            return true;
        }

        $this->newRepositories[$name] = $name;

        return false;
    }
}
