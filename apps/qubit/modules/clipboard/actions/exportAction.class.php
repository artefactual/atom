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

class ClipboardExportAction extends DefaultEditAction
{
    // Arrays not allowed in class constants
    public static $NAMES = [
        'levels',
        'type',
        'format',
        'includeDescendants',
        'includeAllLevels',
        'includeDigitalObjects',
        'includeDrafts',
        'includeNonVisibleElements',
    ];

    private $choices = [];

    public function execute($request)
    {
        // Get object type and validate
        // Currently 'switch' to process the inbound parameter, validate and set
        // default - could be if/then if preferred
        $this->objectType = trim(strtolower($request->getParameter('type')));

        switch ($this->objectType) {
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

        // Get format and validate
        // Currently 'switch' to process the inbound parameter, validate and set
        // default - could be if/then if preferred
        $this->formatType = trim(strtolower($request->getParameter('format')));
        if ('xml' != $this->formatType || 'repository' == $this->objectType) {
            $this->formatType = 'csv';
        }

        // Basic permission check to determine whether digital object export should
        // be made available
        $this->digitalObjectsAvailable = false;

        if (
            sfConfig::get('app_clipboard_export_digitalobjects_enabled', false)
            && (
                'informationObject' == $this->objectType
                || (
                    'actor' == $this->objectType
                    && $this->context->user->isAuthenticated()
                )
            )
        ) {
            $this->digitalObjectsAvailable = true;
        }

        // Determine if there are non-visible elements that should be hidden
        $this->nonVisibleElementsIncluded = false;
        $defTemplate = sfConfig::get('app_default_template_'.strtolower($this->objectType));

        foreach (sfConfig::getAll() as $setting => $value) {
            if (
                (false !== strpos($setting, 'app_element_visibility_'.$defTemplate))
                && (0 == sfConfig::get($setting))
            ) {
                $this->nonVisibleElementsIncluded = true;
            }
        }

        // Show export options panel if:
        // information object type
        // or, if actor type and digital objects are on the clipboard
        $this->showOptions = 'informationObject' == $this->objectType
            || ('actor' == $this->objectType && $this->digitalObjectsAvailable);

        // Get field includeDescendants if:
        // options enabled
        // and, information object type
        $this->descendantsIncluded = $this->showOptions
            && 'informationObject' == $this->objectType
            && 'on' == $request->getParameter('includeDescendants');

        // Get field includeAllLevels if:
        // descendantsIncluded enabled
        $this->descendantsAllLevels = $this->descendantsIncluded
            && 'on' == $request->getParameter('includeAllLevels');

        // Get field includeDigitalObjects if:
        // digital object export option is available
        $this->includeDigitalObjects = $this->digitalObjectsAvailable
            && 'on' == $request->getParameter('includeDigitalObjects');

        // Get field includeDrafts if:
        // options enabled
        // and, user is authenticated
        $this->draftsIncluded = $this->showOptions
            && $this->context->user->isAuthenticated()
            && 'on' == $request->getParameter('includeDrafts');

        // Get field includeNonVisibleElements if:
        // options enabled
        // and, user is authenticated
        $this->nonVisibleElementsIncluded = $this->showOptions
        && $this->context->user->isAdministrator()
        && 'on' == $request->getParameter('includeNonVisibleElements');

        parent::execute($request);

        $this->response->addJavaScript('exportOptions', 'last');

        $this->title = $this->context->i18n->__('Clipboard export');

        if (!$request->isMethod('post')) {
            return;
        }

        $this->response->setHttpHeader(
            'Content-Type',
            'application/json; charset=utf-8'
        );

        $this->form->bind($request->getPostParameters());

        if (!$this->form->isValid()) {
            $this->response->setStatusCode(400);
            $message = $this->context->i18n->__('Invalid export options.');

            return $this->renderText(json_encode(['error' => $message]));
        }

        $slugs = $request->getPostParameter('slugs', []);

        if (empty($slugs)) {
            $this->response->setStatusCode(400);
            $message = $this->context->i18n->__(
                'The clipboard is empty for this entity type.'
            );

            return $this->renderText(json_encode(['error' => $message]));
        }

        $this->processForm();

        // Create array of selections to pass to background job where
        // Term ID will be key, and Term description is value
        $levelsOfDescription = [];
        foreach ($this->levels as $value) {
            $levelsOfDescription[$value] = $this->choices[$value];
        }

        $options = [
            'params' => ['fromClipboard' => true, 'slugs' => $slugs],
            'current-level-only' => !$this->descendantsIncluded,
            'public' => !$this->draftsIncluded,
            'objectType' => $this->objectType,
            'levels' => $levelsOfDescription,
            'nonVisibleElementsIncluded' => $this->nonVisibleElementsIncluded,
        ];

        $msg = ('xml' == $this->formatType) ? 'XML export' : 'CSV export';
        $options['name'] = $this->context->i18n->__($msg);

        if ($this->includeDigitalObjects) {
            $options['name'] = $this->context->i18n->__(
                '%1% and %2%',
                [
                    '%1%' => sfConfig::get('app_ui_label_digitalobject'),
                    '%2%' => $options['name'],
                ]
            );
            $options['includeDigitalObjects'] = true;
        }

        // When exporting actors, ensure aliases and relations are also exported
        if ('actor' === $this->objectType && 'csv' === $this->formatType) {
            $options['aliases'] = true;
            $options['relations'] = true;
        }

        try {
            return $this->runExportJob($options);
        } catch (Exception $e) {
            $this->response->setStatusCode(500);

            return $this->renderText(json_encode(['error' => $e->getMessage()]));
        }
    }

    protected function earlyExecute()
    {
        sfProjectConfiguration::getActive()->loadHelpers(['I18N']);

        // Initialize help array: messages added depending on visibility of fields
        $this->helpMessages = [];

        $this->typeChoices = [
            'informationObject' => sfConfig::get('app_ui_label_informationobject'),
            'actor' => sfConfig::get('app_ui_label_actor'),
            'repository' => sfConfig::get('app_ui_label_repository'),
        ];

        $this->form->getValidatorSchema()->setOption('allow_extra_fields', true);
    }

    protected function runExportJob($options)
    {
        $responseData = [];

        $jobName = $this->getJobNameString();

        // Check if query matches any records, before attempting export
        if (method_exists($jobName, 'findExportRecords')) {
            $search = $jobName::findExportRecords($options);

            if (0 == $search->count()) {
                throw new sfException($this->context->i18n->__(
                    'No records were exported for your current selection. Please %open_link%refresh the page and choose different export options %close_link%.',
                    [
                        '%open_link%' => '<a class="alert-link" href="javascript:location.reload();">',
                        '%close_link%' => '</a>',
                    ]
                ));
            }
        }

        $job = QubitJob::runJob($jobName, $options);

        // Generate, store and return a token to associate unauthenticated users
        // with their export jobs to be able to download the result later and
        // delete the job.
        if (!$this->context->user->isAuthenticated()) {
            $property = $job->generateUserTokenProperty();
            $responseData['token'] = $property->value;
        }

        $responseData['success'] = '<p><strong>';
        $responseData['success'] .= $this->context->i18n->__(
            'Your %entity_type% export package is being built.',
            ['%entity_type%' => strtolower($this->typeChoices[$this->objectType])]
        );
        $responseData['success'] .= '</strong> ';

        if ($this->context->user->isAuthenticated()) {
            $responseData['success'] .= $this->context->i18n->__(
                'The %open_link%job management page%close_link% will show progress and a download link when complete.',
                [
                    '%open_link%' => sprintf(
                        '<strong><a class="alert-link" href="%s">',
                        $this->context->routing->generate(null, [
                            'module' => 'jobs',
                            'action' => 'browse',
                        ])
                    ),
                    '%close_link%' => '</a></strong>',
                ]
            );
        } else {
            $responseData['success'] .= $this->context->i18n->__(
                'Please %open_link%refresh the page%close_link% to see progress and a download link when complete.',
                [
                    '%open_link%' => '<strong><a class="alert-link" href="javascript:location.reload();">',
                    '%close_link%' => '</a></strong>',
                ]
            );
        }

        $responseData['success'] .= '</p><p>';
        $responseData['success'] .= $this->context->i18n->__(
            '%open_strong_tag%Note:%close_strong_tag% AtoM may remove export packages after a period of time to free up storage space. When your export is ready you should download it as soon as possible.',
            [
                '%open_strong_tag%' => '<strong>',
                '%close_strong_tag%' => '</strong>',
            ]
        );
        $responseData['success'] .= '</p>';

        $this->response->setStatusCode(200);

        return $this->renderText(json_encode($responseData));
    }

    protected function addField($name)
    {
        switch ($name) {
            case 'type':
                $this->form->setValidator('type', new sfValidatorString(
                    ['required' => true]
                ));
                $this->form->setWidget('type', new sfWidgetFormSelect(
                    ['label' => __('Type'), 'choices' => $this->typeChoices]
                ));
                $this->form->setDefault('type', $this->objectType);

                break;

            case 'format':
                $this->form->setValidator('format', new sfValidatorString(
                    ['required' => true]
                ));
                $choices = [];
                $choices['csv'] = $this->context->i18n->__('CSV');
                if ('repository' != $this->objectType) {
                    $choices['xml'] = $this->context->i18n->__('XML');
                }
                $this->form->setWidget('format', new sfWidgetFormSelect(
                    ['label' => __('Format'), 'choices' => $choices]
                ));
                $this->form->setDefault(
                    'format',
                    'actor' != $this->objectType ? 'xml' : 'csv'
                );

                break;

            // Enable field includeDescendants if:
            // options enabled
            // and, information object type
            case 'includeDescendants':
                if ($this->showOptions && 'informationObject' == $this->objectType) {
                    $this->form->setWidget(
                        'includeDescendants',
                        new sfWidgetFormInputCheckbox(
                            ['label' => __('Include descendants')]
                        )
                    );
                    $this->form->setDefault('includeDescendants', false);

                    $this->helpMessages[] = __(
                        'Choosing "Include descendants" will include all lower-level records beneath those currently on the clipboard in the export.'
                    );
                }

                break;

            // Enable field includeAllLevels if:
            // options enabled
            // and, information object type
            case 'includeAllLevels':
                if ($this->showOptions && 'informationObject' == $this->objectType) {
                    $this->form->setWidget(
                        'includeAllLevels',
                        new sfWidgetFormInputCheckbox(
                            ['label' => __('Include all descendant levels of description')]
                        )
                    );
                    $this->form->setDefault('includeAllLevels', true);
                }

                break;

            // Enable field levels if:
            // options enabled
            // and, information object type
            case 'levels':
                $this->form->setValidator('levels', new sfValidatorPass());

                $this->levelChoices = [];
                foreach (QubitTerm::getLevelsOfDescription() as $item) {
                    $this->levelChoices[$item->id] = $item->__toString();
                }

                $size = count($this->levelChoices);
                if (0 === $size) {
                    $size = 4;
                }

                if ($this->showOptions && 'informationObject' == $this->objectType) {
                    $this->form->setWidget('levels', new sfWidgetFormSelect(
                        [
                            'label' => __(
                                'Select levels of descendant descriptions for inclusion'
                            ),
                            'help' => __(
                                'If no levels are selected, the export will fail. You can use the control (Mac ⌘) and/or shift keys to multi-select values from the Levels of description menu. It is necessary to include the level(s) above the desired export level, up to and including the level contained in the clipboard. Otherwise, no records will be included in the export.'
                            ),
                            'choices' => $this->levelChoices,
                            'multiple' => true,
                        ],
                        ['size' => $size]
                    ));
                }

                break;

            // Enable field includeDigitalObjects if:
            // digital objects are available
            case 'includeDigitalObjects':
                if ($this->digitalObjectsAvailable) {
                    if ('informationObject' == $this->objectType) {
                        $this->helpMessages[] = __(
                            'It is not possible to select both digital objects and descendants for export at the same time. Digital objects can only be exported for records that are on the clipboard.'
                        );
                    }

                    $this->helpMessages[] = __(
                        'Digital objects with restricted access or copyright will not be exported.'
                    );

                    $this->form->setWidget(
                        'includeDigitalObjects',
                        new sfWidgetFormInputCheckbox(
                            ['label' => __('Include digital objects')]
                        )
                    );
                    $this->form->setDefault('includeDigitalObjects', true);
                }

                break;

            // Enable field includeDrafts if:
            // information object type
            // and, user is authenticated
            case 'includeDrafts':
                if (
                    'informationObject' == $this->objectType
                    && $this->context->user->isAuthenticated()
                ) {
                    $this->form->setWidget(
                        'includeDrafts',
                        new sfWidgetFormInputCheckbox(
                            ['label' => __('Include draft records')]
                        )
                    );

                    $this->helpMessages[] = __(
                        'Choosing "Include draft records" will include those marked with a Draft publication status in the export. Note: if you do NOT choose this option, any descendants of a draft record will also be excluded, even if they are published.'
                    );
                    $this->form->setDefault('includeDrafts', true);
                }

                break;

            // Enable field includeNonVisibleElements if:
            // information object type
            // and, user is authenticated
            case 'includeNonVisibleElements':
                if (
                    'informationObject' == $this->objectType
                    && $this->context->user->isAdministrator()
                ) {
                    $this->form->setWidget(
                        'includeNonVisibleElements',
                        new sfWidgetFormInputCheckbox(
                            ['label' => __('Include non-visible elements')]
                        )
                    );

                    $this->helpMessages[] = __(
                        'Choosing "Include non-visible elements" will include those not marked as Visible Elements.'
                    );
                    $this->form->setDefault('includeNonVisibleElements', false);
                }

                break;

            default:
                return parent::addField($name);
        }
    }

    protected function processField($field)
    {
        $name = $field->getName();

        switch ($name) {
            case 'levels':
                $this->levels = $this->form->getValue('levels');
                if (empty($this->levels)) {
                    $this->levels = [];
                }

                break;

            case 'type':
            case 'format':
                $this->{$name} = $this->form->getValue($name);

                break;

            default:
                return parent::processField($field);
        }
    }

    private function getJobNameString()
    {
        switch ($this->objectType) {
            case 'informationObject':
                if ('csv' == $this->formatType) {
                    return 'arInformationObjectCsvExportJob';
                }

                return 'arInformationObjectXmlExportJob';

            case 'actor':
                if ('csv' == $this->formatType) {
                    return 'arActorCsvExportJob';
                }

                return 'arActorXmlExportJob';

            case 'repository':
                return 'arRepositoryCsvExportJob';

            default:
                throw new sfException(
                    "Invalid object type specified: {$this->objectType}"
                );
        }
    }
}
