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

class ObjectExportAction extends DefaultEditAction
{
  // Arrays not allowed in class constants
  public static
    $NAMES = array(
      'levels',
      'type',
      'format',
      'includeDescendants',
      'includeAllLevels',
      'includeDigitalObjects',
      'includeDrafts');

  private $choices = array();

  protected function earlyExecute()
  {
    sfProjectConfiguration::getActive()->loadHelpers(array('I18N'));

    // Initialize help array: messages added depending on visibility of fields
    $this->helpMessages = array();

    $this->typeChoices = array(
      'informationObject' => sfConfig::get('app_ui_label_informationobject'),
      'actor' => sfConfig::get('app_ui_label_actor'),
      'repository' => sfConfig::get('app_ui_label_repository')
    );

    $this->form->getValidatorSchema()->setOption('allow_extra_fields', true);
  }

  public function execute($request)
  {

    // Get object type and validate
    // Currently 'switch' to process the inbound parameter, validate and set default - could be if/then if preferred
    $this->objectType = trim(strtolower($request->getParameter('type')));
    switch ($this->objectType)
    {
      case 'actor':
        $className = 'QubitActor';

        break;

      case 'repository':
        $className = 'QubitRepository';

        break;

      default:
        $this->objectType = 'informationObject';
        $className = 'QubitInformationObject';
    }

    // Stop direct access to export url or empty export attempts
    // Check that the user has something on the clipboard for the current type - otherwise redirect back to clipboard
    $items = $this->context->user->getClipboard()->countByType();
    if(empty($items) || !array_key_exists($className, $items) || 1 > $items[$className])
    {
      $this->redirect($this->context->routing->generate(null, array('module' => 'user', 'action' => 'clipboard')) . '?type=' . $this->objectType);
    }

    // Get format and validate
    // Currently 'switch' to process the inbound parameter, validate and set default - could be if/then if preferred
    $this->formatType = trim(strtolower($request->getParameter('format')));
    if(!('xml' == $this->formatType || 'csv' == $this->formatType) || 'repository' == $this->objectType)
    {
        $this->formatType = 'csv';
    }

    // Basic permission check to determine whether digital object export should be made available
    $this->digitalObjectsAvailable = false;
    if (sfConfig::get('app_clipboard_export_digitalobjects_enabled', false) && ('informationObject' == $this->objectType || ('actor' == $this->objectType && $this->context->user->isAuthenticated())))
    {
      $this->digitalObjectsAvailable = true;
    }

    // Show export options panel if:
    // information object type
    // or, if actor type and digital objects are on the clipboard
    $this->showOptions = 'informationObject' == $this->objectType || ('actor' == $this->objectType && $this->digitalObjectsAvailable);

    // Get field includeDescendants if:
    // options enabled
    // and, information object type
    $this->descendantsIncluded = $this->showOptions && 'informationObject' == $this->objectType && 'on' == $request->getParameter('includeDescendants');

    // get field includeAllLevels if:
    // descendantsIncluded enabled
    $this->descendantsAllLevels = $this->descendantsIncluded && 'on' == $request->getParameter('includeAllLevels');

    // Get field includeDigitalObjects if:
    // digital objects are on the clipboard
    // and, information object type and descendants are not selected
    // or, if actor type
    $this->includeDigitalObjects = false;
    if ($this->digitalObjectsAvailable)
    {
      $this->includeDigitalObjects = 'on' == $request->getParameter('includeDigitalObjects');
    }

    // Get field includeDrafts if:
    // options enabled
    // and, user is authenticated
    $this->draftsIncluded = false;
    if ($this->showOptions && $this->context->user->isAuthenticated())
    {
      $this->draftsIncluded = 'on' == $request->getParameter('includeDrafts');
    }

    parent::execute($request);

    $this->response->addJavaScript('exportOptions', 'last');

    $this->title = $this->context->i18n->__('Export');

    if ($request->isMethod('post'))
    {
      $this->form->bind($request->getPostParameters());

      if ($this->form->isValid())
      {
        $this->processForm();

        $this->doBackgroundExport($request);

        $this->redirect($this->context->routing->generate(null, array('module' => 'user', 'action' => 'clipboard')) . '?type=' . $this->objectType);
      }
    }
  }

  protected function addField($name)
  {
    switch ($name)
    {
      case 'type':
        $this->form->setValidator('type', new sfValidatorString(array('required' => true)));
        $this->form->setWidget('type', new sfWidgetFormSelect(array('label' => __('Type'), 'choices' => $this->typeChoices)));
        $this->form->setDefault('type', $this->objectType);

        break;

      case 'format':
        $this->form->setValidator('format', new sfValidatorString(array('required' => true)));
        $choices = array();
        $choices['csv'] = $this->context->i18n->__('CSV');
        if('repository' != $this->objectType)
        {
          $choices['xml'] = $this->context->i18n->__('XML');
        }
        $this->form->setWidget('format', new sfWidgetFormSelect(array('label' => __('Format'), 'choices' => $choices)));
        $this->form->setDefault('format', 'actor' != $this->objectType ? 'xml' : 'csv');

        break;

      // Enable field includeDescendants if:
      // options enabled
      // and, information object type
      case 'includeDescendants':
        if ($this->showOptions && 'informationObject' == $this->objectType)
        {
          $this->form->setWidget('includeDescendants', new sfWidgetFormInputCheckbox(array('label' => __('Include descendants'))));
          $this->form->setDefault('includeDescendants', false);
          $this->helpMessages[] = __('Choosing "Include descendants" will include all lower-level records beneath those currently on the clipboard in the export.');
        }

        break;

      // Enable field includeAllLevels if:
      // options enabled
      // and, information object type
      case 'includeAllLevels':
        if ($this->showOptions && 'informationObject' == $this->objectType)
        {
          $this->form->setWidget('includeAllLevels', new sfWidgetFormInputCheckbox(array('label' => __('Include all descendant levels of description'))));
          $this->form->setDefault('includeAllLevels', true);
        }

        break;

      // Enable field levels if:
      // options enabled
      // and, information object type
      case 'levels':

        $this->form->setValidator('levels', new sfValidatorPass);

        $this->levelChoices = array();
        foreach (QubitTerm::getLevelsOfDescription() as $item)
        {
          $this->levelChoices[$item->id] = $item->__toString();
        }

        $size = count($this->levelChoices);
        if ($size === 0)
        {
          $size = 4;
        }

        if ($this->showOptions && 'informationObject' == $this->objectType)
        {
          $this->form->setWidget('levels', new sfWidgetFormSelect(
            array(
              'label' => __('Select levels of descendant descriptions for inclusion'), 
              'help' => __('If no levels are selected, the export will fail. You can use the control (Mac ⌘) and/or shift keys to multi-select values from the Levels of description menu. ' . 
                           'It is necessary to include the level(s) above the desired export level, up to and including the level contained in the clipboard. ' . 
                           'Otherwise, no records will be included in the export.'), 
              'choices' => $this->levelChoices, 
              'multiple' => true
            ), 
            array(
              'size' => $size
            )
          ));
        }
        
        break;

      // Enable field includeDigitalObjects if:
      // digital objects are available
      case 'includeDigitalObjects':
        if ($this->digitalObjectsAvailable)
        {
          switch ($this->objectType)
          {
            case 'informationObject':
              $this->helpMessages[] = __('Note: Digital objects with restricted access or copyright will not be exported.') . ' ' . __('It is not possible to select both digital objects and descendants for export at the same time. Digital objects can only be exported for records that are on the clipboard.');

              break;

            case 'actor':
              $this->helpMessages[] = __('Note: Digital objects with restricted access or copyright will not be exported.');

              break;
          }
          $this->form->setWidget('includeDigitalObjects', new sfWidgetFormInputCheckbox(array('label' => __('Include digital objects'))));
          $this->form->setDefault('includeDigitalObjects', true);
        }

        break;

      // Enable field includeDrafts if:
      // options enabled
      // and, user is authenticated
      case 'includeDrafts':
        if ($this->showOptions && $this->context->user->isAuthenticated())
        {
          $this->form->setWidget('includeDrafts', new sfWidgetFormInputCheckbox(array('label' => __('Include draft records'))));
          $this->helpMessages[] = __('Choosing "Include draft records" will include those marked with a Draft publication status in the export. Note: if you do NOT choose this option, any descendants of a draft record will also be excluded, even if they are published.');
          $this->form->setDefault('includeDrafts', true);
        }

        break;

      default:
        return parent::addField($name);
    }
  }

  protected function processField($field)
  {
    $name = $field->getName();
    switch ($name)
    {
      case 'levels':
        $this->levels = $this->form->getValue('levels');
        if (empty($this->levels))
        {
          $this->levels = array();
        }

        break;

      case 'type':
      case 'format':
        $this->$name = $this->form->getValue($name);

        break;

      default:
        return parent::processField($field);
    }
  }

  protected function doBackgroundExport($request)
  {
    // Create array of selections to pass to background job where 
    // Term ID will be key, and Term description is value
    $levelsOfDescription = array();
    foreach ($this->levels as $value)
    {
      $levelsOfDescription[$value] = $this->choices[$value];
    }

    $options = array(
      'params' => array('fromClipboard' => true, 'slugs' => $this->context->user->getClipboard()->getAll()),
      'current-level-only' => !$this->descendantsIncluded,
      'public' => !$this->draftsIncluded,
      'objectType' => $this->objectType,
      'levels' => $levelsOfDescription
    );

    $options['name'] = $this->context->i18n->__('xml' === $this->formatType ? 'XML export' : 'CSV export' );

    if($this->includeDigitalObjects)
    {
      $options['name'] = $this->context->i18n->__('%1% and %2%', 
        array(
          '%1%' => sfConfig::get('app_ui_label_digitalobject'),
          '%2%' => $options['name']
        )
      );
      $options['includeDigitalObjects'] = true;
    }

    // When exporting actors, ensure aliases and relations are also exported
    if ('actor' === $this->objectType && 'csv' === $this->formatType)
    {
      $options['aliases'] = true;
      $options['relations'] = true;
    }

    try
    {
      $jobName = $this->getJobNameString();

      // Preview export before attempting, if job supports this
      $exportPreviewSupported = method_exists($jobName, 'exportOrCheckForResults');

      if ($exportPreviewSupported && !call_user_func(array($jobName, 'exportOrCheckForResults'), $options))
      {
        throw new sfException($this->context->i18n->__(
          'No records were exported for your current selection. Please %1%refresh the page and choose different export options%2%.', 
          array(
            '%1%' => '<a href="javascript:location.reload();">',
            '%2%' => '</a>',
          )
        ));
      }
      else
      {
        $job = QubitJob::runJob($jobName, $options);

        // If anonymous user, store job ID in session
        if (!$this->context->user->isAuthenticated())
        {
          $manager = new QubitUnauthenticatedUserJobManager($this->context->user);
          $manager->addJobAssociation($job);
        }

        $message = array();

        $message[] = $this->context->i18n->__(
          '%1%Your %2% export package is being built.%3% ' . ($this->context->user->isAuthenticated() ? 'The %4%job management page%5% will show progress and a download link when complete.' : 'Please %4%refresh the page%5% to see progress and a download link when complete.'), 
          array(
            '%1%' => '<strong>',
            '%2%' => strtolower($this->typeChoices[$this->objectType]),
            '%3%' => '</strong>',
            '%4%' => ($this->context->user->isAuthenticated() ? sprintf('<a href="%s">', $this->context->routing->generate(null, array('module' => 'jobs', 'action' => 'browse'))) : '<a href="javascript:location.reload();">') . '<strong>',
            '%5%' => '</strong></a>'
          )
        );

        $message[] = $this->context->i18n->__(
          '%1%Note:%2% AtoM may remove export packages after a period of time to free up storage space. When your export is ready you should download it as soon as possible.', 
          array(
            '%1%' => '<strong>',
            '%2%' => '</strong>'
          )
        );

      }
      $this->context->user->setFlash('info', '<p>' . implode('</p><p>', $message) . '</p>');
    }
    catch (sfException $e)
    {
      $this->context->user->setFlash('error', $e->getMessage());
      return sfView::SUCCESS;
    }
  }

  private function getJobNameString()
  {
    switch ($this->objectType)
    {
      case 'informationObject':
        if ('csv' == $this->formatType)
        {
          return 'arInformationObjectCsvExportJob';
        }
        else
        {
          return 'arInformationObjectXmlExportJob';
        }

      case 'actor':
        if ('csv' == $this->formatType)
        {
          return 'arActorCsvExportJob';
        }
        else
        {
          return 'arActorXmlExportJob';
        }

      case 'repository':
        return 'arRepositoryCsvExportJob';

      default:
        throw new sfException("Invalid object type specified: {$this->objectType}");
    }
  }
}