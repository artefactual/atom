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
 * Global form definition for settings module - with validation.
 *
 * @package    AccesstoMemory
 * @subpackage settings
 * @author     David Juhasz <david@artefactual.com>
 */
class SettingsGlobalForm extends sfForm
{
  protected static $refImageMaxWidthMin = 100;
  protected static $refImageMaxWidthMax = 2000;

  protected static $hitsPerPageMin = 5;
  protected static $hitsPerPageMax = 100;

  public function configure()
  {
    $i18n = sfContext::getInstance()->i18n;

    // Build widgets
    $this->setWidgets(array(
      'version' => new sfWidgetFormInput(array(), array('class'=>'disabled', 'disabled'=>true)),
      'check_for_updates' => new sfWidgetFormSelectRadio(array('choices'=>array(1=>'yes', 0=>'no')), array('class'=>'radio')),
      'reference_image_maxwidth' => new sfWidgetFormInput,
      'hits_per_page' => new sfWidgetFormInput,
      'accession_mask' => new sfWidgetFormInput,
      'accession_counter' => new sfWidgetFormInput,
      'separator_character' => new sfWidgetFormInput(array(), array('maxlength' => 1)),
      'inherit_code_informationobject' => new sfWidgetFormSelectRadio(array('choices'=>array(1=>'yes', 0=>'no')), array('class'=>'radio')),
      'sort_treeview_informationobject' => new sfWidgetFormSelectRadio(array('choices'=>array('none'=>'manual', 'title'=>'title', 'identifierTitle'=> 'identifier - title')), array('class'=>'radio')),
      'sort_browser_user' => new sfWidgetFormSelectRadio(array('choices'=>array('alphabetic'=>'alphabetic', 'lastUpdated'=> 'last updated', 'identifier' => 'identifier')), array('class'=>'radio')),
      'sort_browser_anonymous' => new sfWidgetFormSelectRadio(array('choices'=>array('alphabetic'=>'alphabetic', 'lastUpdated'=> 'last updated', 'identifier' => 'identifier')), array('class'=>'radio')),
      'default_repository_browse_view' => new sfWidgetFormSelectRadio(array('choices' => array('card' => $i18n->__('card'), 'table' => $i18n->__('table'))), array('class' => 'radio')),
      'multi_repository' => new sfWidgetFormSelectRadio(array('choices'=>array(1=>'yes', 0=>'no')), array('class'=>'radio')),
      'repository_quota' => new sfWidgetFormInput,
      'upload_quota' => new arWidgetFormUploadQuota,
      'explode_multipage_files' => new sfWidgetFormSelectRadio(array('choices'=>array(1=>'yes', 0=>'no')), array('class'=>'radio')),
      'show_tooltips' => new sfWidgetFormSelectRadio(array('choices'=>array(1=>'yes', 0=>'no')), array('class'=>'radio')),
      'defaultPubStatus' => new sfWidgetFormSelectRadio(array('choices'=>array(QubitTerm::PUBLICATION_STATUS_DRAFT_ID=>$i18n->__('Draft'), QubitTerm::PUBLICATION_STATUS_PUBLISHED_ID=>$i18n->__('Published'))), array('class'=>'radio')),
      'sword_deposit_dir' => new sfWidgetFormInput
    ));

    // Add labels
    $this->widgetSchema->setLabels(array(
      'version' => $i18n->__('Application version'),
      'check_for_updates' => $i18n->__('Check for updates'),
      'reference_image_maxwidth' => $i18n->__('Maximum image width (pixels)'),
      'hits_per_page' => $i18n->__('Results per page'),
      'accession_mask' => $i18n->__('Accession mask'),
      'accession_counter' => $i18n->__('Accession counter'),
      'separator_character' => $i18n->__('Reference code separator'),
      'inherit_code_informationobject' => $i18n->__('Inherit reference code (information object)'),
      'sort_treeview_informationobject' => $i18n->__('Sort treeview (information object)'),
      'sort_browser_user' => $i18n->__('Sort browser (users)'),
      'sort_browser_anonymous' => $i18n->__('Sort browser (anonymous)'),
      'default_repository_browse_view' => $i18n->__('Default repository browse view'),
      'multi_repository' => $i18n->__('Multiple repositories'),
      'repository_quota' => $i18n->__('Default %1% upload limit (GB)', array('%1%' => strtolower(sfConfig::get('app_ui_label_repository')))),
      'upload_quota' => $i18n->__('Total space available for uploads'),
      'explode_multipage_files' => $i18n->__('Upload multi-page files as multiple descriptions'),
      'show_tooltips' => $i18n->__('Show tooltips'),
      'defaultPubStatus' => $i18n->__('Default publication status'),
      'sword_deposit_dir' => $i18n->__('SWORD deposit directory'),
      'require_ssl_admin' => $i18n->__('Require SSL for all administrator funcionality'),
      'require_strong_passwords' => $i18n->__('Require strong passwords')
    ));

    // Add helper text
    $this->widgetSchema->setHelps(array(
      'version' => $i18n->__('The current version of the application'),
      'check_for_updates' => $i18n->__('Enable automatic update notification'),
      'reference_image_maxwidth' => $i18n->__('The maximum width for derived reference images'),
      'hits_per_page' => $i18n->__('The number of records shown per page on list pages'),
      // 'accession_mask' => $i18n->__(''),
      // 'accession_counter' => $i18n->__(''),
      'default_repository_browse_view' => $i18n->__('Set the default view template when browsing repositories'),
      'separator_character' => $i18n->__('The character separating hierarchical elements in a reference code'),
      'inherit_code_informationobject' => $i18n->__('When set to &quot;yes&quot;, the reference code string will be built using the information object identifier plus the identifiers of all its ancestors'),
      'sort_treeview_informationobject' => $i18n->__('Determines whether to sort siblings in the information object treeview control and, if so, what sort criteria to use'),
      'multi_repository' => $i18n->__('When set to &quot;no&quot;, the repository name is excluded from certain displays because it will be too repetitive'),
      'repository_quota' => $i18n->__('Default %1% upload limit for a new %2%.  A value of &quot;0&quot; (zero) disables file upload.  A value of &quot;-1&quot; allows unlimited uploads', array('%1%' => strtolower(sfConfig::get('app_ui_label_digitalobject')), '%2%' => strtolower(sfConfig::get('app_ui_label_repository')))),
      'defaultPubStatus' => $i18n->__('Default publication status for newly created or imported %1%', array('%1%' => sfConfig::get('app_ui_label_informationobject')))
      // 'explode_multipage_files' => $i18n->__('')
      // 'show_tooltips' => $i18n->__('')
      // 'sword_deposit_dir' => $i18n->__('')
    ));

    // Reference image max. width validator
    $this->validatorSchema['reference_image_maxwidth'] = new sfValidatorInteger(
      array(
        'required' => true,
        'min' => self::$refImageMaxWidthMin,
        'max' => self::$refImageMaxWidthMax
      ),
      array(
        'required' => $i18n->__('This field is required'),
        'min' => $i18n->__('This value must be at least %min% pixels'),
        'max' => $i18n->__('This value can not be greater than %max% pixels')
      )
    );

    // Hits per page validator
    $this->validatorSchema['hits_per_page'] = new sfValidatorInteger(
      array(
        'required' => true,
        'min' => self::$hitsPerPageMin,
        'max' => self::$hitsPerPageMax
      ),
      array(
        'required' => $i18n->__('This field is required'),
        'min'=> $i18n->__('You must show at least %min% hits per page'),
        'max'=> $i18n->__('You cannot show more than %max% hits per page')
      )
    );

    $this->validatorSchema['version'] = new sfValidatorString(array('required' => false));
    $this->validatorSchema['check_for_updates'] = new sfValidatorString(array('required' => false));
    $this->validatorSchema['accession_mask'] = new sfValidatorString(array('required' => false));
    $this->validatorSchema['separator_character'] = new sfValidatorString(array('required' => false));
    $this->validatorSchema['accession_counter'] = new sfValidatorString(array('required' => false));
    $this->validatorSchema['inherit_code_informationobject'] = new sfValidatorInteger(array('required' => false));
    $this->validatorSchema['sort_treeview_informationobject'] = new sfValidatorString(array('required' => false));
    $this->validatorSchema['sort_browser_user'] = new sfValidatorString(array('required' => false));
    $this->validatorSchema['sort_browser_anonymous'] = new sfValidatorString(array('required' => false));
    $this->validatorSchema['multi_repository'] = new sfValidatorInteger(array('required' => false));
    $this->validatorSchema['default_repository_browse_view'] = new sfValidatorString(array('required' => false));

    $this->validatorSchema['repository_quota'] = new sfValidatorNumber(
      array('required' => true, 'min' => -1),
      array('min' => $i18n->__('Minimum value is "%min%"')));
    $this->validatorSchema['explode_multipage_files'] = new sfValidatorInteger(array('required' => false));
    $this->validatorSchema['show_tooltips'] = new sfValidatorInteger(array('required' => false));
    $this->validatorSchema['defaultPubStatus'] = new sfValidatorChoice(array('choices' => array(QubitTerm::PUBLICATION_STATUS_DRAFT_ID, QubitTerm::PUBLICATION_STATUS_PUBLISHED_ID)));
    $this->validatorSchema['sword_deposit_dir'] = new sfValidatorString(array('required' => false));

    // Set decorator
    $decorator = new QubitWidgetFormSchemaFormatterList($this->widgetSchema);
    $this->widgetSchema->addFormFormatter('list', $decorator);
    $this->widgetSchema->setFormFormatterName('list');

    // Set wrapper text for global form settings
    $this->widgetSchema->setNameFormat('global_settings[%s]');
  }
}
