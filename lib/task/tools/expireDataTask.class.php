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
    public const ACCESS_LOG_MAX_AGE_DAYS = 7;

    // Arrays not allowed in class constants
    public static $TYPE_SPECIFICATONS = [
        'access_log' => [
            'name' => 'access log',
            'plural_name' => 'access logs',
            'method_name' => 'accessLogExpireData',
            'default_max_age' => self::ACCESS_LOG_MAX_AGE_DAYS,
        ],
        'clipboard' => [
            'name' => 'saved clipboard',
            'plural_name' => 'saved clipboards',
            'method_name' => 'clipboardExpireData',
            'age_setting_name' => 'app_clipboard_save_max_age',
        ],
        'job' => [
            'name' => 'job (and any related file)',
            'plural_name' => 'jobs (and any related files)',
            'method_name' => 'jobExpireData',
        ],
    ];

    protected function configure()
    {
        $dataTypeArgDescription = sprintf(
            'Data type(s), comma-separated (supported types: %s)',
            $this->supportedTypesDescription()
        );

        $this->addArguments(
            [
                new sfCommandArgument(
                    'data-type',
                    sfCommandArgument::REQUIRED,
                    $dataTypeArgDescription
                ),
            ]
        );

        $this->addOptions(
            [
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
                    'older-than',
                    null,
                    sfCommandOption::PARAMETER_OPTIONAL,
                    'Expiry date expressed as YYYY-MM-DD',
                    null
                ),
                new sfCommandOption(
                    'force',
                    'f',
                    sfCommandOption::PARAMETER_NONE,
                    'Delete without confirmation',
                    null
                ),
            ]
        );

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

            $expiryDate = $this->getOlderThanDate(
                $options,
                $typeSpec
            );

            // Abort if not forced or confirmed
            if (
                !$options['force']
                && !$this->getConfirmation($expiryDate, $typeSpec['plural_name'])
            ) {
                $this->logSection('expire-data', 'Aborted.');

                return;
            }

            // Expire data and report results
            $deletedCount = $this->{$typeSpec['method_name']}($expiryDate);

            $this->logSection(
                'expire-data',
                sprintf(
                    '%d %s deleted.',
                    $deletedCount,
                    $typeSpec['plural_name']
                )
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
            if (!in_array($dataType, array_keys(self::$TYPE_SPECIFICATONS))) {
                throw new sfException(
                    sprintf('Aborted: unsupported data type: "%s".', $dataType)
                );
            }
        }
    }

    private function calculateExpiryDate($maximumAgeInDays)
    {
        $date = new DateTime();
        $interval = new DateInterval(sprintf('P%dD', $maximumAgeInDays));
        $date->sub($interval);

        return $date->format('Y-m-d');
    }

    private function getDateFromAgeSetting($name)
    {
        $value = sfConfig::get($name);

        if (!is_numeric($value) || intval($value) < 0) {
            // Throw error if setting value isn't an integer
            throw new sfException(
                sprintf(
                    'Error: setting %s value "%s" is not a valid integer.',
                    $typeSpec['age_setting_name'],
                    $value
                )
            );
        }

        // Use date type's maximum age setting to calculate older than option
        $date = $this->calculateExpiryDate(intval($value));

        // Let user know that date type's maximum age setting was used
        $this->logSection(
            'expire-data',
            sprintf('Used %s setting to set expiry date of %s.', $name, $date)
        );

        return $date;
    }

    private function getDateFromMaxAge($maxAge)
    {
        if (!is_numeric($maxAge) || intval($maxAge) < 0) {
            // Throw error if setting value isn't an integer
            throw new sfException(
                sprintf(
                    'Error: "default_max_age" of "%s" is not a valid integer.',
                    $maxAge
                )
            );
        }

        // Use date type's maximum age setting to calculate older than option
        $date = $this->calculateExpiryDate(intval($maxAge));

        // Let user know that date type's maximum age setting was used
        $this->logSection(
            'expire-data',
            sprintf(
                'Used "default_max_age" setting to set expiry date of %s.',
                $date
            )
        );

        return $date;
    }

    private function getOlderThanDate($options, $typeSpec)
    {
        // If an explicit older-than value is passed, use that
        if (isset($options['older-than'])) {
            return $options['older-than'];
        }

        // Calculate expiry date from max. age application setting (sfConfig)
        if (isset($typeSpec['age_setting_name'])) {
            return $this->getDateFromAgeSetting($typeSpec['age_setting_name']);
        }

        // Calculate expiry date from local 'default_max_age' value
        if (isset($typeSpec['default_max_age'])) {
            return $this->getDateFromMaxAge($typeSpec['default_max_age']);
        }
    }

    private function getConfirmation($expiryDate, $typeNamePlural)
    {
        $message = 'Are you sure you want to delete';

        if (isset($expiryDate)) {
            $message .= sprintf(
                ' %s older than %s',
                $typeNamePlural,
                $expiryDate
            );
        } else {
            $message .= sprintf(' all %s', $typeNamePlural);
        }

        $message .= ' (y/N)?';

        return $this->askConfirmation($message, 'QUESTION_LARGE', false);
    }

    /**
     * Expire old access_log data.
     *
     * @param string $expiryDate access_log rows before this date are deleted
     *
     * @return int number of rows deleted
     */
    private function accessLogExpireData(string $expiryDate): int
    {
        if (isset($expiryDate)) {
            return QubitAccessLog::expire($expiryDate);
        }

        return 0;
    }

    private function clipboardExpireData($expiryDate)
    {
        // Assemble criteria
        $criteria = new Criteria();

        if (isset($expiryDate)) {
            $criteria->add(
                QubitClipboardSave::CREATED_AT,
                $expiryDate,
                Criteria::LESS_THAN
            );
        }

        // Delete clipbooard saves and save items
        $deletedCount = 0;

        foreach (QubitClipboardSave::get($criteria) as $save) {
            $save->delete();
            ++$deletedCount;
        }

        return $deletedCount;
    }

    private function jobExpireData($expiryDate)
    {
        // Assemble criteria
        $criteria = new Criteria();

        if (isset($expiryDate)) {
            $criteria->add(
                QubitJob::CREATED_AT,
                $expiryDate,
                Criteria::LESS_THAN
            );
        }

        // Delete jobs and save items
        $deletedCount = 0;

        foreach (QubitJob::get($criteria) as $job) {
            // Jobs generate finding aids, which we *don't* want to delete...
            // finding aids won't be deleted by this logic, however, because the
            // job doesn't store the path to them after generating them
            if (!empty($job->downloadPath) && file_exists($job->downloadPath)) {
                unlink($job->downloadPath);
            }

            $job->delete();
            ++$deletedCount;
        }

        return $deletedCount;
    }
}
