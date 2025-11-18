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

class renameSlugTask extends arBaseTask
{
    protected $failedSlugs = [];

    protected $logFile;

    protected function configure()
    {
        $this->addArguments([
            new sfCommandArgument('oldSlug', sfCommandArgument::OPTIONAL, 'The slug to update'),
            new sfCommandArgument('newSlug', sfCommandArgument::OPTIONAL, 'Updated slug text'),
        ]);

        $this->addOptions([
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application name', true),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'cli'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'propel'),
            new sfCommandOption('csv', null, sfCommandOption::PARAMETER_OPTIONAL, 'CSV file containing old and new slugs for batch update'),
        ]);

        $this->namespace = 'tools';
        $this->name = 'rename-slug';
        $this->briefDescription = 'Update slug(s) to use a different name';
        $this->detailedDescription = <<<'EOF'
The [tools:rename-slug] task can be used to either rename a single slug,
or do a batch update via CSV file.

To rename a single slug, run:
    php symfony tools:rename old-slug new-slug

To do a batch update via CSV, run:
    php symfony tools:rename --csv=file/path/to/csv-file.csv

The supplied CSV file must contain an oldSlug and a newSlug column.
EOF;
    }

    protected function execute($arguments = [], $options = [])
    {
        parent::execute($arguments, $options);

        if ($arguments['oldSlug'] && $arguments['newSlug']) {
            $this->renameSlug($arguments['oldSlug'], $arguments['newSlug']);
        } elseif ($options['csv']) {
            $this->updateSlugsFromCSV($options['csv']);
        } else {
            throw new Exception('Either provide old and new slug values, or use the CSV option and supply a CSV file containing those values.');
        }

        if (!empty($this->failedSlugs)) {
            $this->logSection('rename-slug', 'The following slugs were not updated:', null, 'ERROR');
            foreach ($this->failedSlugs as $err) {
                $this->logSection('rename-slug', $err, null, 'ERROR');
            }
        }

        $f = $this->logFile;
        $this->logSection('rename-slug', "Log file: {$f}.");
    }

    protected function updateSlugsFromCSV($filename)
    {
        if (false === $fh = fopen($filename, 'rb')) {
            throw new sfException('You must specify a valid filename');
        }

        // Read the first row, check if columns labeled oldSlug and newSlug exist
        $header = fgetcsv($fh, 1000);
        if (false === $this->header) {
            throw new sfException('Could not read initial row. File could be empty.');
        }
        if (false === $oldSlugColumn = array_search('oldSlug', $header)) {
            throw new sfException('You must have a column named oldSlug in your CSV file.');
        }
        if (false === $newSlugColumn = array_search('newSlug', $header)) {
            throw new sfException('You must have a column named newSlug in your CSV file.');
        }

        while ($item = fgetcsv($fh, 1000)) {
            $oldSlug = trim($item[$oldSlugColumn]);
            $newSlug = trim($item[$newSlugColumn]);
            if ($oldSlug && $newSlug) {
                $this->renameSlug($oldSlug, $newSlug);
            }
        }
    }

    protected function renameSlug($oldSlug, $newSlug)
    {
        // Search for both the new and old slugs
        $criteria = new Criteria();
        $c1 = $criteria->getNewCriterion(QubitSlug::SLUG, $oldSlug);
        $c2 = $criteria->getNewCriterion(QubitSlug::SLUG, $newSlug);
        $c1->addOr($c2);
        $criteria->add($c1);

        $oldSlugObject = null;
        $newSlugObject = null;

        foreach (QubitSlug::get($criteria) as $slug) {
            if ($slug->slug === $oldSlug) {
                $oldSlugObject = $slug;
            } elseif ($slug->slug === $newSlug) {
                $newSlugObject = $slug;
            }
        }

        if (null !== $newSlugObject) {
            $this->failedSlugs[] = "{$newSlug} already exists.";
        } elseif (null === $oldSlugObject) {
            $this->failedSlugs[] = "{$oldSlug} not found.";
        } else {
            $oldSlugObject->slug = $newSlug;
            $oldSlugObject->save();
            QubitObject::getBySlug($newSlug)->save();
            $this->logSection('rename-slug', "Slug {$oldSlug} updated to {$newSlug} successfully.");
            $this->addToLogFile($oldSlug, $newSlug);
        }
    }

    protected function initLogFile()
    {
        $dateFormat = date('Y-m-d-H-i');
        $f = $this->logFile = sfConfig::get('sf_log_dir')."/rename-slug-{$dateFormat}.log.txt";

        $file = fopen($f, 'w');

        if ($file) {
            $header = "rename-slug-{$dateFormat} report:\n\n";
            fwrite($file, $header);
            fclose($file);
        } else {
            $this->logSection('rename-slug', "Log file {$f} failed to open for writing.", null, 'ERROR');
        }
    }

    protected function addToLogFile($oldSlug, $newSlug)
    {
        if (!isset($this->logFile)) {
            $this->initLogFile();
        }

        $log = "/{$oldSlug} updated to /{$newSlug}.\n";
        $custom_logger = new sfFileLogger(new sfEventDispatcher(), ['file' => $this->logFile]);
        $custom_logger->info($log);
    }
}
