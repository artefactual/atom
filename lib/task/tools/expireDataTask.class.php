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

class expireDataTask extends arBaseTask
{
    // Arrays not allowed in class constants
    public static $TYPE_SPECIFICATONS = [
        'clipboard' => [
            'name' => 'saved clipboard',
            'plural_name' => 'saved clipboards',
            'age_setting_name' => 'app_clipboard_save_max_age',
        ],
        'job' => [
            'name' => 'job (and any related file)',
            'plural_name' => 'jobs (and any related files)',
        ],
    ];

    protected function configure()
    {
        $dataTypeArgDescription = sprintf(
            'Data type(s), comma-separated (supported types: %s)',
            $this->supportedTypesDescription()
        );

        $this->addArguments([
            new sfCommandArgument('data-type', sfCommandArgument::REQUIRED, $dataTypeArgDescription),
        ]);

        $this->addOptions([
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application name', true),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'cli'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'propel'),
            new sfCommandOption('older-than', null, sfCommandOption::PARAMETER_OPTIONAL, 'Expiry date expressed as YYYY-MM-DD'),
            new sfCommandOption('force', 'f', sfCommandOption::PARAMETER_NONE, 'Delete without confirmation', null),
        ]);

        $this->namespace = 'tools';
        $this->name = 'expire-data';
        $this->briefDescription = 'Delete expired data';
        $this->detailedDescription = <<<'EOF'
Delete expired data (in entirety or by age)
EOF;
    }

    protected function execute($arguments = [], $options = [])
    {
        parent::execute($arguments, $options);

        $dataTypes = explode(',', $arguments['data-type']);
        $this->validateDataTypes($dataTypes);

        foreach ($dataTypes as $dataType) {
            $typeSpec = self::$TYPE_SPECIFICATONS[$dataType];

            $options['older-than'] = $this->determineOlderThanDate($options, $typeSpec);

            // Abort if not forced or confirmed
            if (!$options['force'] && !$this->getConfirmation($options, $typeSpec['plural_name'])) {
                $this->logSection('expire-data', 'Aborted.');

                return;
            }

            // Expire data and report results
            $methodName = sprintf('%sExpireData', $dataType);
            $deletedCount = $this->{$methodName}($options);

            $this->logSection(
                'expire-data',
                sprintf('%d %s deleted.', $deletedCount, $typeSpec['plural_name'])
            );
        }

        $this->logSection('expire-data', 'Done!');
    }

    private function supportedTypesDescription()
    {
        $description = '';

        foreach (array_keys(self::$TYPE_SPECIFICATONS) as $dataType) {
            // Prepend with comma if not the first item
            $description = ($description) ? $description.', ' : $description;
            $description .= '"'.$dataType.'"';
        }

        return $description;
    }

    private function validateDataTypes($dataTypes)
    {
        foreach ($dataTypes as $dataType) {
            // Abort if data type isn't supported
            if (!in_array(strtolower($dataType), array_keys(self::$TYPE_SPECIFICATONS))) {
                throw new sfException(
                    sprintf('Aborted: unsupported data type: "%s".', $dataType)
                );
            }
        }
    }

    private function determineOlderThanDate($options, $typeSpec)
    {
        // Set older than option if not set and a non-zero maximum age is set for data type
        $maxAge = isset($typeSpec['age_setting_name'])
            ? sfConfig::get($typeSpec['age_setting_name'])
            : null;

        if (!isset($options['older-than']) && !empty($maxAge)) {
            // Throw error if setting value isn't an integer
            if (!is_numeric($maxAge) || $maxAge < 0) {
                throw new sfException(
                    sprintf(
                        'Error: setting %s value "%s" is not a valid number.',
                        $typeSpec['age_setting_name'],
                        $maxAge
                    )
                );
            }

            // Use date type's maximum age setting to calculate older than option
            $options['older-than'] = $this->calculateOlderThanDate($maxAge);

            // Let user know that date type's maximum age setting was used
            $this->logSection(
                'expire-data',
                sprintf(
                    'Used %s setting to set expiry date of %s.',
                    $typeSpec['age_setting_name'],
                    $options['older-than']
                )
            );
        }

        return $options['older-than'];
    }

    private function calculateOlderThanDate($maximumAgeInDays)
    {
        $date = new DateTime();
        $interval = new DateInterval(sprintf('P%dD', $maximumAgeInDays));
        $date->sub($interval);

        return $date->format('Y-m-d');
    }

    private function getConfirmation($options, $typeNamePlural)
    {
        $message = 'Are you sure you want to delete ';

        if (isset($options['older-than'])) {
            $message .= sprintf('%s older than %s?', $typeNamePlural, $options['older-than']);
        } else {
            $message .= sprintf('all %s?', $typeNamePlural);
        }

        return $this->askConfirmation($message, 'QUESTION_LARGE', false);
    }

    private function clipboardExpireData($options)
    {
        // Assemble criteria
        $criteria = new Criteria();

        if (isset($options['older-than'])) {
            $criteria->add(QubitClipboardSave::CREATED_AT, $options['older-than'], Criteria::LESS_THAN);
        }

        // Delete clipbooard saves and save items
        $deletedCount = 0;

        foreach (QubitClipboardSave::get($criteria) as $save) {
            $save->delete();
            ++$deletedCount;
        }

        return $deletedCount;
    }

    private function jobExpireData($options)
    {
        // Assemble criteria
        $criteria = new Criteria();

        if (isset($options['older-than'])) {
            $criteria->add(QubitJob::CREATED_AT, $options['older-than'], Criteria::LESS_THAN);
        }

        // Delete jobs and save items
        $deletedCount = 0;

        foreach (QubitJob::get($criteria) as $job) {
            // Jobs generate finding aids, which we *don't* want to delete... finding
            // aids won't be deleted by this logic, however, because the job doesn't
            // store the path to them after generating them
            if (!empty($job->downloadPath) && file_exists($job->downloadPath)) {
                unlink($job->downloadPath);
            }

            $job->delete();
            ++$deletedCount;
        }

        return $deletedCount;
    }
}
