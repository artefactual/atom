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

class SettingsPermissionsCopyrightStatementForm extends sfForm
{
  public function configure()
  {
    $this->getValidatorSchema()->setOption('allow_extra_fields', true);

    $this->setWidget('copyrightStatementEnabled', new sfWidgetFormSelectRadio(array('choices'=> array(1 => 'yes', 0 => 'no')), array('class'=>'radio')));
    $this->setValidator('copyrightStatementEnabled', new sfValidatorInteger(array('required' => false)));

    $default = false;
    if (null !== $setting = QubitSetting::getByName('digitalobject_copyright_statement_enabled'))
    {
      $value = $setting->getValue(array('sourceCulture' => true));
      if (!empty($value))
      {
        $default = $value;  
      }
    }
    $this->setDefault('copyrightStatementEnabled', $default); 

    $this->setWidget('copyrightStatement', new sfWidgetFormTextArea(array(), array('rows' => 4)));
    $this->setValidator('copyrightStatement', new sfValidatorString);
    if (null !== $setting = QubitSetting::getByName('digitalobject_copyright_statement'))
    {
      $this->setDefault('copyrightStatement', $setting->getValue());
    }
  }
}
