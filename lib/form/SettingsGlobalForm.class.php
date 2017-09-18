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
  protected static $hitsPerPageMin = 5;
  protected static $hitsPerPageMax = 100;

  public function configure()
  {
    $this->i18n = sfContext::getInstance()->i18n;

    // Build widgets
    $this->setWidgets(array(
      'version' => new sfWidgetFormInput(array(), array('class' => 'disabled', 'disabled' => true)),
      'check_for_updates' => new sfWidgetFormSelectRadio(array('choices' => array(1 => 'yes', 0 => 'no')), array('class' => 'radio')),
      'hits_per_page' => new sfWidgetFormInput,
      'accession_mask_enabled' => new sfWidgetFormSelectRadio(array('choices' => array(1 => 'yes', 0 => 'no')), array('class' => 'radio')),
      'accession_mask' => new sfWidgetFormInput,
      'accession_counter' => new sfWidgetFormInput,
      'identifier_mask_enabled' => new sfWidgetFormSelectRadio(array('choices' => array(1 => 'yes', 0 => 'no')), array('class' => 'radio')),
      'identifier_mask' => new sfWidgetFormInput,
      'identifier_counter' => new sfWidgetFormInput,
      'separator_character' => new sfWidgetFormInput(array(), array('maxlength' => 1)),
      'inherit_code_informationobject' => new sfWidgetFormSelectRadio(array('choices' => array(1 => 'yes', 0 => 'no')), array('class' => 'radio')),
      'escape_queries' => new sfWidgetFormInput,
      'sort_browser_user' => new sfWidgetFormSelectRadio(array('choices' => array('alphabetic' => 'alphabetic', 'lastUpdated' => 'last updated', 'identifier' => 'identifier', 'referenceCode' => $this->i18n->__('reference code'))), array('class' => 'radio')),
      'sort_browser_anonymous' => new sfWidgetFormSelectRadio(array('choices' => array('alphabetic' => 'alphabetic', 'lastUpdated' => 'last updated', 'identifier' => 'identifier', 'referenceCode' => $this->i18n->__('reference code'))), array('class' => 'radio')),
      'default_repository_browse_view' => new sfWidgetFormSelectRadio(array('choices' => array('card' => $this->i18n->__('card'), 'table' => $this->i18n->__('table'))), array('class' => 'radio')),
      'default_archival_description_browse_view' => new sfWidgetFormSelectRadio(array('choices' => array('card' => $this->i18n->__('card'), 'table' => $this->i18n->__('table'))), array('class' => 'radio')),
      'multi_repository' => new sfWidgetFormSelectRadio(array('choices' => array(1 => 'yes', 0 => 'no')), array('class' => 'radio')),
      'enable_institutional_scoping' => new sfWidgetFormSelectRadio(array('choices'=>array(1=>'yes', 0=>'no')), array('class'=>'radio')),
      'repository_quota' => new sfWidgetFormInput,
      'upload_quota' => new arWidgetFormUploadQuota,
      'explode_multipage_files' => new sfWidgetFormSelectRadio(array('choices' => array(1 => 'yes', 0 => 'no')), array('class' => 'radio')),
      'show_tooltips' => new sfWidgetFormSelectRadio(array('choices' => array(1 => 'yes', 0 => 'no')), array('class' => 'radio')),
      'slug_basis_informationobject' => $this->getSlugBasisInformationObjectWidget(),
      'defaultPubStatus' => new sfWidgetFormSelectRadio(array('choices' => array(QubitTerm::PUBLICATION_STATUS_DRAFT_ID => $this->i18n->__('Draft'), QubitTerm::PUBLICATION_STATUS_PUBLISHED_ID => $this->i18n->__('Published'))), array('class' => 'radio')),
      'draft_notification_enabled' => new sfWidgetFormSelectRadio(array('choices' => array(1 => 'yes', 0 => 'no')), array('class' => 'radio')),
      'sword_deposit_dir' => new sfWidgetFormInput,
      'google_maps_api_key' => new sfWidgetFormInput,
      'generate_reports_as_pub_user' => new sfWidgetFormSelectRadio(array('choices' => array(1 => 'yes', 0 => 'no')), array('class' => 'radio')),
      'cache_xml_on_save' => new sfWidgetFormSelectRadio(array('choices' => array(1 => 'yes', 0 => 'no')), array('class' => 'radio')),
    ));

    // Add labels
    $this->widgetSchema->setLabels(array(
      'version' => $this->i18n->__('Application version'),
      'check_for_updates' => $this->i18n->__('Check for updates'),
      'hits_per_page' => $this->i18n->__('Results per page'),
      'accession_mask_enabled' => $this->i18n->__('Accession mask enabled'),
      'accession_mask' => $this->i18n->__('Accession mask'),
      'accession_counter' => $this->i18n->__('Accession counter'),
      'identifier_mask_enabled' => $this->i18n->__('Identifier mask enabled'),
      'identifier_mask' => $this->i18n->__('Identifier mask'),
      'identifier_counter' => $this->i18n->__('Identifier counter'),
      'separator_character' => $this->i18n->__('Reference code separator'),
      'inherit_code_informationobject' => $this->i18n->__('Inherit reference code (information object)'),
      'escape_queries' => $this->i18n->__('Escape special chars from searches'),
      'sort_browser_user' => $this->i18n->__('Sort browser (users)'),
      'sort_browser_anonymous' => $this->i18n->__('Sort browser (anonymous)'),
      'default_repository_browse_view' => $this->i18n->__('Default repository browse view'),
      'default_archival_description_browse_view' => $this->i18n->__('Default archival description browse view'),
      'multi_repository' => $this->i18n->__('Multiple repositories'),
      'enable_institutional_scoping' => $this->i18n->__('Enable institutional scoping'),
      'repository_quota' => $this->i18n->__('Default %1% upload limit (GB)', array('%1%' => strtolower(sfConfig::get('app_ui_label_repository')))),
      'upload_quota' => $this->i18n->__('Total space available for uploads'),
      'explode_multipage_files' => $this->i18n->__('Upload multi-page files as multiple descriptions'),
      'show_tooltips' => $this->i18n->__('Show tooltips'),
      'defaultPubStatus' => $this->i18n->__('Default publication status'),
      'draft_notification_enabled' => $this->i18n->__('Show available drafts notification upon user login'),
      'sword_deposit_dir' => $this->i18n->__('SWORD deposit directory'),
      'require_ssl_admin' => $this->i18n->__('Require SSL for all administrator funcionality'),
      'slug_basis_informationobject' => $this->i18n->__('Generate description permalinks from'),
      'require_strong_passwords' => $this->i18n->__('Require strong passwords'),
      'google_maps_api_key' => $this->i18n->__('Google Maps Javascript API key (for displaying dynamic maps)'),
      'generate_reports_as_pub_user' => $this->i18n->__('Generate archival description reports as public user'),
      'cache_xml_on_save' => $this->i18n->__('Cache description XML exports upon creation/modification'),
    ));

    // Add helper text
    $this->widgetSchema->setHelps(array(
      'version' => $this->i18n->__('The current version of the application'),
      'check_for_updates' => $this->i18n->__('Enable automatic update notification'),
      'hits_per_page' => $this->i18n->__('The number of records shown per page on list pages'),
      // 'accession_mask' => $this->i18n->__(''),
      // 'accession_counter' => $this->i18n->__(''),
      'default_repository_browse_view' => $this->i18n->__('Set the default view template when browsing repositories'),
      'default_archival_description_browse_view' => $this->i18n->__('Set the default view template when browsing archival descriptions'),
      'separator_character' => $this->i18n->__('The character separating hierarchical elements in a reference code'),
      'inherit_code_informationobject' => $this->i18n->__('When set to &quot;yes&quot;, the reference code string will be built using the information object identifier plus the identifiers of all its ancestors'),
      'escape_queries' => $this->i18n->__('A list of special chars, separated by coma, to be escaped in string queries'),
      'multi_repository' => $this->i18n->__('When set to &quot;no&quot;, the repository name is excluded from certain displays because it will be too repetitive'),
      'enable_institutional_scoping' => $this->i18n->__('Applies to multi-repository sites only. When set to &quot;yes&quot;, additional search and browse options will be available at the repository level'),
      'repository_quota' => $this->i18n->__('Default %1% upload limit for a new %2%.  A value of &quot;0&quot; (zero) disables file upload.  A value of &quot;-1&quot; allows unlimited uploads', array('%1%' => strtolower(sfConfig::get('app_ui_label_digitalobject')), '%2%' => strtolower(sfConfig::get('app_ui_label_repository')))),
      'defaultPubStatus' => $this->i18n->__('Default publication status for newly created or imported %1%', array('%1%' => sfConfig::get('app_ui_label_informationobject'))),
      'slug_basis_informationobject' => $this->i18n->__('Choose whether permalinks for descriptions are generated from reference code or title'),
      // 'explode_multipage_files' => $this->i18n->__('')
      // 'show_tooltips' => $this->i18n->__('')
      // 'sword_deposit_dir' => $this->i18n->__('')
    ));

    // Hits per page validator
    $this->validatorSchema['hits_per_page'] = new sfValidatorInteger(
      array(
        'required' => true,
        'min' => self::$hitsPerPageMin,
        'max' => self::$hitsPerPageMax
      ),
      array(
        'required' => $this->i18n->__('This field is required'),
        'min'=> $this->i18n->__('You must show at least %min% hits per page'),
        'max'=> $this->i18n->__('You cannot show more than %max% hits per page')
      )
    );

    $this->validatorSchema['version'] = new sfValidatorString(array('required' => false));
    $this->validatorSchema['check_for_updates'] = new sfValidatorString(array('required' => false));
    $this->validatorSchema['accession_mask_enabled'] = new sfValidatorInteger(array('required' => false));
    $this->validatorSchema['accession_mask'] = new sfValidatorString(array('required' => false));
    $this->validatorSchema['separator_character'] = new sfValidatorString(array('required' => false));
    $this->validatorSchema['accession_counter'] = new sfValidatorString(array('required' => false));
    $this->validatorSchema['inherit_code_informationobject'] = new sfValidatorInteger(array('required' => false));
    $this->validatorSchema['escape_queries'] = new sfValidatorString(array('required' => false));
    $this->validatorSchema['sort_browser_user'] = new sfValidatorString(array('required' => false));
    $this->validatorSchema['sort_browser_anonymous'] = new sfValidatorString(array('required' => false));
    $this->validatorSchema['multi_repository'] = new sfValidatorInteger(array('required' => false));
    $this->validatorSchema['enable_institutional_scoping'] = new sfValidatorInteger(array('required' => false));
    $this->validatorSchema['default_repository_browse_view'] = new sfValidatorString(array('required' => false));
    $this->validatorSchema['default_archival_description_browse_view'] = new sfValidatorString(array('required' => false));
    $this->validatorSchema['slug_basis_informationobject'] = $this->getSlugBasisInformationObjectValidator();
    $this->validatorSchema['identifier_mask_enabled'] = new sfValidatorInteger(array('required' => false));
    $this->validatorSchema['identifier_mask'] = new sfValidatorString(array('required' => false));
    $this->validatorSchema['identifier_counter'] = new sfValidatorString(array('required' => false));
    $this->validatorSchema['repository_quota'] = new sfValidatorNumber(
      array('required' => true, 'min' => -1),
      array('min' => $this->i18n->__('Minimum value is "%min%"')));
    $this->validatorSchema['explode_multipage_files'] = new sfValidatorInteger(array('required' => false));
    $this->validatorSchema['show_tooltips'] = new sfValidatorInteger(array('required' => false));
    $this->validatorSchema['defaultPubStatus'] = new sfValidatorChoice(array('choices' => array(QubitTerm::PUBLICATION_STATUS_DRAFT_ID, QubitTerm::PUBLICATION_STATUS_PUBLISHED_ID)));
    $this->validatorSchema['draft_notification_enabled'] = new sfValidatorInteger(array('required' => false));
    $this->validatorSchema['sword_deposit_dir'] = new sfValidatorString(array('required' => false));
    $this->validatorSchema['google_maps_api_key'] = new sfValidatorString(array('required' => false));
    $this->validatorSchema['generate_reports_as_pub_user'] = new sfValidatorInteger(array('required' => false));
    $this->validatorSchema['cache_xml_on_save'] = new sfValidatorInteger(array('required' => false));

    // Set decorator
    $decorator = new QubitWidgetFormSchemaFormatterList($this->widgetSchema);
    $this->widgetSchema->addFormFormatter('list', $decorator);
    $this->widgetSchema->setFormFormatterName('list');

    // Set wrapper text for global form settings
    $this->widgetSchema->setNameFormat('global_settings[%s]');
  }

  private function getSlugBasisInformationObjectWidget()
  {
    $choices = array(
      QubitSlug::SLUG_BASIS_TITLE => $this->i18n->__('title'),
      QubitSlug::SLUG_BASIS_IDENTIFIER => $this->i18n->__('identifier'),
      QubitSlug::SLUG_BASIS_REFERENCE_CODE_NO_COUNTRY_REPO => $this->i18n->__('reference code (repository identifier & country code not included)'),
      QubitSlug::SLUG_BASIS_REFERENCE_CODE => $this->i18n->__('reference code (repository identifier & country code included)'),
    );

    return new sfWidgetFormSelectRadio(array('choices' => $choices), array('class' => 'radio'));
  }

  private function getSlugBasisInformationObjectValidator()
  {
    $choices = array(
      QubitSlug::SLUG_BASIS_REFERENCE_CODE,
      QubitSlug::SLUG_BASIS_TITLE,
      QubitSlug::SLUG_BASIS_IDENTIFIER,
      QubitSlug::SLUG_BASIS_REFERENCE_CODE_NO_COUNTRY_REPO,
    );

    return new sfValidatorChoice(array('choices' => $choices));
  }
}
