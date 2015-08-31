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
    $this->setDefault('copyrightStatementEnabled', sfConfig::get('app_digitalobject_copyright_statement_enabled', false));
    $this->setValidator('copyrightStatementEnabled', new sfValidatorInteger(array('required' => false)));

    $this->setWidget('copyrightStatement', new sfWidgetFormTextArea(array(), array('rows' => 4)));
    $this->setDefault('copyrightStatement', sfConfig::get('app_digitalobject_copyright_statement', false));
    $this->setValidator('copyrightStatement', new sfValidatorString);
  }
}
