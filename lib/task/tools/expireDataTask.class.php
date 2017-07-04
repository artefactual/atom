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
  public static
    $TYPE_SPECIFICATONS = array(
      'clipboard' => array(
        'name' => 'saved clipboard',
        'plural_name' => 'saved clipboards',
        'age_setting_name' => 'clipboard_save_max_age'
      )
    );

  protected function configure()
  {
    $dataTypeArgDescription = sprintf('Data type (supported types: %s)', $this->supportedTypesDescription());
    $this->addArguments(array(
      new sfCommandArgument('data-type', sfCommandArgument::REQUIRED, $dataTypeArgDescription)
    ));

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application name', true),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'cli'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'propel'),
      new sfCommandOption('older-than', null, sfCommandOption::PARAMETER_OPTIONAL, 'Expiry date expressed as YYYY-MM-DD'),
      new sfCommandOption('force', 'f', sfCommandOption::PARAMETER_NONE, 'Delete without confirmation', null),
    ));

    $this->namespace = 'tools';
    $this->name = 'expire-data';
    $this->briefDescription = 'Delete expired data';
    $this->detailedDescription = <<<EOF
Delete expired data (in entirety or by age)
EOF;
  }

  private function supportedTypesDescription()
  {
    $description = '';

    foreach (array_keys(self::$TYPE_SPECIFICATONS) as $dataType)
    {
      // Prepend with comma if not the first item
      $description = ($description) ? $description .', ' : $description;
      $description .= '"'. $dataType .'"';
    }

    return $description;
  }

  protected function execute($arguments = array(), $options = array())
  {
    parent::execute($arguments, $options);

    // Abort if data type isn't supported
    if (!in_array(strtolower($arguments['data-type']), array_keys(self::$TYPE_SPECIFICATONS)))
    {
      throw new sfException('Aborted: unsupported data type.');
    }

    $typeSpec = self::$TYPE_SPECIFICATONS[($arguments['data-type'])];

    // Set older than option if not set and a non-zero maximum age is set for data type
    $checkAgeSetting = !isset($options['older-than']) && isset($typeSpec['age_setting_name']);
    if ($checkAgeSetting && $maxAge = sfConfig::get('app_clipboard_save_max_age'))
    {
      // Throw error if setting value isn't an integer
      if (!ctype_digit($maxAge))
      {
        throw new sfException(
          sprintf('Error: setting %s value "%s" is non-numeric.', $typeSpec['age_setting_name'], $maxAge)
        );
      }

      // Use date type's maximum age setting to calculate older than option
      $date = new DateTime();
      $interval = new DateInterval('P'. sfConfig::get('app_clipboard_save_max_age') .'D');
      $date->sub($interval);
      $options['older-than'] = $date->format('Y-m-d');

      // Let user know that date type's maximum age setting was used
      $this->logSection(
        'expire-data',
        sprintf('Used %s setting to set expiry date of %s.', $typeSpec['age_setting_name'], $options['older-than'])
      );
    }

    // Abort if not forced or confirmed
    if (!$options['force'] && !$this->getConfirmation($options, $typeSpec['plural_name']))
    {
      $this->logSection('expire-data', 'Aborted.');
      return;
    }

    // Expire data and report results
    $methodName = $arguments['data-type'] .'ExpireData';
    $deletedCount = $this->$methodName($options);
    $this->logSection('expire-data', sprintf('Finished! %d saved clipboards deleted.', $deletedCount));
  }

  private function getConfirmation($options, $typeNamePlural)
  {
    $message = 'Are you sure you want to delete ';

    if (isset($options['older-than']))
    {
      $message .= sprintf('%s older than %s?', $typeNamePlural, $options['older-than']);
    }
    else
    {
      $message .= sprintf('all %s?', $typeNamePlural);
    }

    return $this->askConfirmation($message, 'QUESTION_LARGE', false);
  }

  private function clipboardExpireData($options)
  {
    // Assemble criteria
    $criteria = new Criteria;

    if (isset($options['older-than']))
    {
      $criteria->add(QubitClipboardSave::CREATED_AT, $options['older-than'], Criteria::LESS_THAN);
    }

    // Delete clipbooard saves and save items
    $deletedCount = 0;

    foreach(QubitClipboardSave::get($criteria) as $save)
    {
      $save->delete();
      $deletedCount++;
    }

    return $deletedCount;
  }
}
