<?php

/*
 * This file is part of Qubit Toolkit.
 *
 * Qubit Toolkit is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Qubit Toolkit is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Qubit Toolkit.  If not, see <http://www.gnu.org/licenses/>.
 */

ProjectConfiguration::getActive()->loadHelpers('I18N');

/**
 * Global form definition for settings module - with validation.
 *
 * @package    qubit
 * @subpackage settings
 * @version    svn: $Id$
 */
class SettingsSecurityForm extends sfForm
{
  public function configure()
  {
    // Build widgets
    $this->setWidgets(array(
      'limit_admin_ip' => new sfWidgetFormInput,
      'require_ssl_admin' => new sfWidgetFormSelectRadio(array('choices'=>array(1=>'yes', 0=>'no')), array('class'=>'radio')),
      'require_strong_passwords' => new sfWidgetFormSelectRadio(array('choices'=>array(1=>'yes', 0=>'no')), array('class'=>'radio'))
    ));

    // Add labels
    $this->widgetSchema->setLabels(array(
      'limit_admin_ip' => __('Limit administrator functionality to a static IP address or range'),
      'require_ssl_admin' => __('Require SSL for all administrator functionality'),
      'require_strong_passwords' => __('Require strong passwords')
    ));

    // Add helper text
    $this->widgetSchema->setHelps(array(
      // 'limit_admin_ip' => __('')
      // 'require_ssl_admin' => __('')
      // 'require_strong_passwords' => __('')
    ));

    $this->validatorSchema['limit_admin_ip'] = new sfValidatorString(array('required' => false));
    $this->validatorSchema['require_ssl_admin'] = new sfValidatorInteger(array('required' => false));
    $this->validatorSchema['require_strong_passwords'] = new sfValidatorInteger(array('required' => false));

    // Set decorator
    $decorator = new QubitWidgetFormSchemaFormatterList($this->widgetSchema);
    $this->widgetSchema->addFormFormatter('list', $decorator);
    $this->widgetSchema->setFormFormatterName('list');

    // Set wrapper text for security settings
    $this->widgetSchema->setNameFormat('security[%s]');
  }
}
