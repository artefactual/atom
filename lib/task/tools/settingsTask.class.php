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
 * Set or get setting values.
 *
 * @author  Mike Cantelon <mike@artefactual.com>
 */
class settingsTask extends arBaseTask
{
    protected $ormClasses;

    public function __construct(sfEventDispatcher $dispatcher, sfFormatter $formatter)
    {
        $this->setOrmClasses([
            'setting' => QubitSetting::class,
        ]);

        parent::__construct($dispatcher, $formatter);
    }

    public function setOrmClasses(array $classes): void
    {
        $this->ormClasses = $classes;
    }

    /**
     * @see sfTask
     *
     * @param mixed $arguments
     * @param mixed $options
     */
    public function execute($arguments = [], $options = [])
    {
        parent::execute($arguments, $options);

        $this->validateOptions($arguments, $options);

        try {
            $this->dispatchOperation($arguments, $options);
        } catch (ValueError $e) {
            throw new sfException($e->getMessage());
        }

        $this->log('Done.');
    }

    public function dispatchOperation($arguments, $options)
    {
        switch (strtolower($arguments['operation'])) {
            case 'get':
                $value = $this->getOperation($arguments['name'], $options);

                if (!empty($value)) {
                    $this->log(sprintf('Value: %s', $value));
                }

                break;

            case 'set':
                $this->setOperation($arguments['name'], $arguments['value'], $options);

                break;

            case 'list':
                $this->log($this->listOperation());

                break;

            default:
                throw new sfException('Invalid operation.');
        }
    }

    public function getOperation($name, $options = [])
    {
        $value = $this->getSettingValue($name, $options);

        if (!empty($options['file'])) {
            $result = file_put_contents($options['file'], $value);

            if (!$result) {
                throw new sfException('Error writing file.');
            }
        } else {
            return $value;
        }
    }

    public function setOperation($name, $value, $options = [])
    {
        if (!empty($options['file'])) {
            $value = file_get_contents($options['file']);
        }

        $this->setSettingValue($name, $value, $options);
    }

    public function getSetting($name, $options = [])
    {
        $criteria = new Criteria();
        $criteria->add($this->ormClasses['setting']::NAME, $name);

        if (!empty($options['scope'])) {
            $criteria->add($this->ormClasses['setting']::SCOPE, $options['scope']);
        }

        return $this->ormClasses['setting']::getOne($criteria);
    }

    public function getSettingValue($name, $options)
    {
        try {
            $setting = $this->getSetting($name, $options);
        } catch (Exception $e) {
            throw new Exception('Setting does not exist.');
        }

        if (empty($setting)) {
            throw new Exception('Setting does not exist.');
        }

        return $setting->getValue(['culture' => $options['culture']]);
    }

    public function setSettingValue($name, $value, $options)
    {
        $setting = $this->getSetting($name, $options);

        if (empty($setting)) {
            if (empty($options['strict'])) {
                $setting = new $this->ormClasses['setting']();

                if (!empty($options['scope'])) {
                    $setting->scope = $options['scope'];
                }

                $setting->name = $name;
            } else {
                throw new Exception("Settings can't be created in strict mode.");
            }
        }

        $setting->setValue($value, ['culture' => $options['culture']]);
        $setting->save();
    }

    public function listOperation()
    {
        $output = '';

        $longestSettingName = $this->getLongestSettingName();

        // Display header
        $output .= str_repeat('-', $longestSettingName + 20)."\n";
        $output .= str_pad('Name', $longestSettingName + 2).'Scope'."\n";
        $output .= str_repeat('-', $longestSettingName + 20)."\n";

        // Display available settings
        foreach ($this->getCurrentSettings() as $setting) {
            $output .= str_pad($setting['name'], $longestSettingName + 2).$setting['scope']."\n";
        }

        return $output;
    }

    public function getLongestSettingName()
    {
        $longestName = 0;

        // Cycle through settings to determine the longest name
        foreach ($this->getCurrentSettings() as $setting) {
            if (strlen($setting['name']) > $longestName) {
                $longestName = strlen($setting['name']);
            }
        }

        return $longestName;
    }

    public function getCurrentSettings()
    {
        $settings = [];

        $criteria = new Criteria();
        $criteria->addAscendingOrderByColumn('name');
        $criteria->addAscendingOrderByColumn('scope');

        foreach ($this->ormClasses['setting']::get($criteria) as $setting) {
            $settings[] = [
                'name' => $setting->name,
                'scope' => $setting->scope,
            ];
        }

        return $settings;
    }

    public function validateOptions($arguments, $options)
    {
        // Make sure culture is valid if operation is 'get' or 'set'
        if (in_array($arguments['operation'], ['get', 'set']) && !sfCultureInfo::validCulture($options['culture'])) {
            throw new Exception('Culture is invalid.');
        }

        // Check that the "value" argument is being used for the appropriate operation
        if (!empty($arguments['value']) && 'set' != $arguments['operation']) {
            throw new Exception("The 'value' option must only be used with the 'set' operation.");
        }

        // Check that the "file" option is being used for an appropriate operation
        if (!empty($options['file']) && !in_array($arguments['operation'], ['get', 'set'])) {
            throw new Exception("The 'file' option must only be used with the 'get' or 'set' operations.");
        }

        // Check that "file" option isn't being used at the same time as the optional "value" argument
        if (!empty($options['file']) && !empty($arguments['value'])) {
            throw new Exception("The 'value' argument and 'file' option can't be used at the same time.");
        }

        // Check that the file specified by the "file" option actually exists if doing a "set" operation
        if (!empty($options['file']) && 'set' == $arguments['operation'] && !file_exists($options['file'])) {
            throw new Exception("During a 'set' operation the 'file' option must refer to an existing file.");
        }

        // Check that the file specified by the "file" option can be read if doing a "set" operation
        if (!empty($options['file']) && 'set' == $arguments['operation'] && !is_readable($options['file'])) {
            throw new Exception("The 'file' option must refer to a readable file.");
        }

        // Check that the file specified by the "file" option doesn't already exist if doing a "get" operation
        if (!empty($options['file']) && 'get' == $arguments['operation'] && file_exists($options['file'])) {
            throw new Exception("During a 'get' operation the 'file' option mustn't refer to an already existing file.");
        }

        // Check that the file specified by the "file" option can be written to if doing a "get" operation
        if (!empty($options['file']) && 'get' == $arguments['operation']) {
            // Do test file write
            $fp = fopen($options['file'], 'w');

            if (false === $fp) {
                throw new Exception("The 'file' option must refer to a writeable file path.");
            }

            // Clean up after test file write
            fclose($fp);
            unlink($options['file']);
        }
    }

    /**
     * @see sfBaseTask
     */
    protected function configure()
    {
        $this->addArguments([
            new sfCommandArgument(
                'operation',
                sfCommandArgument::REQUIRED,
                'Setting operation ("get", "set", or "list").'
            ),
            new sfCommandArgument(
                'name',
                sfCommandArgument::OPTIONAL,
                'Name of setting (for "get" or "set" operation).'
            ),
            new sfCommandArgument(
                'value',
                sfCommandArgument::OPTIONAL,
                'value of setting (for "set" operation if not using the "file" option).'
            ),
        ]);

        $this->addOptions([
            new sfCommandOption(
                'application',
                null,
                sfCommandOption::PARAMETER_OPTIONAL,
                'The application name',
                'qubit'
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

            // Tool options
            new sfCommandOption(
                'culture',
                null,
                sfCommandOption::PARAMETER_OPTIONAL,
                'Culture (for "get" or "set" operation, default: "en"))',
                'en'
            ),
            new sfCommandOption(
                'scope',
                null,
                sfCommandOption::PARAMETER_OPTIONAL,
                'Scope of setting to get or set',
                null
            ),
            new sfCommandOption(
                'file',
                null,
                sfCommandOption::PARAMETER_OPTIONAL,
                'File to write value to (for "get" operation) or read value from (for "set" operation)',
                null
            ),
            new sfCommandOption(
                'strict',
                null,
                sfCommandOption::PARAMETER_NONE,
                'Prevent creation of new settings when performing "set" operation',
                null
            ),
        ]);

        $this->namespace = 'tools';
        $this->name = 'settings';
        $this->briefDescription = 'Get or set settings.';
        $this->detailedDescription = <<<'EOF'
Get or set settings.
EOF;
    }
}
