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
 * Finding Aid form definition for settings module - with validation.
 */
class SettingsFindingAidForm extends sfForm
{
    public function configure()
    {
        $i18n = sfContext::getInstance()->i18n;

        // Build widgets
        $this->setWidgets(
            [
                'finding_aids_enabled' => new sfWidgetFormSelectRadio(
                    [
                        'choices' => ['1' => 'Enabled', '0' => 'Disabled'],
                    ],
                    ['class' => 'radio']
                ),
                'finding_aid_format' => new sfWidgetFormSelect(
                    ['choices' => ['pdf' => 'PDF', 'rtf' => 'RTF']]
                ),
                'finding_aid_model' => new sfWidgetFormSelect(
                    [
                        'choices' => [
                            'inventory-summary' => 'Inventory summary',
                            'full-details' => 'Full details',
                        ],
                    ]
                ),
                'public_finding_aid' => new sfWidgetFormSelectRadio(
                    [
                        'choices' => ['1' => 'Yes', '0' => 'No'],
                    ],
                    ['class' => 'radio']
                ),
            ]
        );

        // Add labels
        $this->widgetSchema->setLabels(
            [
                'finding_aids_enabled' => $i18n->__('Finding Aids enabled'),
                'finding_aid_format' => $i18n->__('Finding Aid format'),
                'finding_aid_model' => $i18n->__('Finding Aid model'),
                'public_finding_aid' => $i18n->__(
                    'Generate Finding Aid from public records'
                ),
            ]
        );

        // Add helper text
        $this->widgetSchema->setHelps(
            [
                'finding_aids_enabled' => $i18n->__(
<<<'EOL'
When disabled: Finding Aid links are not displayed, Finding Aid generation is
disabled, and the 'Advanced Search > Finding Aid' filter is hidden
EOL
                ),
                'finding_aid_format' => $i18n->__(
<<<'EOL'
Choose the file format for generated Finding Aids (PDF or 'Rich Text Format')
EOL
                ),
                'finding_aid_model' => $i18n->__(
<<<'EOL'
Finding Aid model:
- Inventory summary: will include only key details for lower-level descriptions
  (file, item, part) in a table
- Full details: includes full lower-level descriptions in the same format used
  throughout the finding aid
EOL
                ),
                'public_finding_aid' => $i18n->__(
<<<'EOL'
When set to 'yes' generated Finding Aids will exclude unpublished records and
hidden elements
EOL
                ),
            ]
        );

        $this->validatorSchema = new sfValidatorSchema(
            [
                'finding_aids_enabled' => new sfValidatorChoice([
                    'choices' => ['0', '1'],
                ]),
                'finding_aid_format' => new sfValidatorChoice([
                    'choices' => ['pdf', 'rtf'],
                ]),
                'finding_aid_model' => new sfValidatorChoice([
                    'choices' => ['inventory-summary', 'full-details'],
                ]),
                'public_finding_aid' => new sfValidatorChoice([
                    'choices' => ['0', '1'],
                ]),
            ]
        );

        // Set decorator
        if (!sfConfig::get('app_b5_theme', false)) {
            $decorator = new QubitWidgetFormSchemaFormatterList(
                $this->widgetSchema
            );
            $this->widgetSchema->addFormFormatter('list', $decorator);
            $this->widgetSchema->setFormFormatterName('list');
        }

        // Set wrapper text for Finding Aid settings
        $this->widgetSchema->setNameFormat('finding_aid[%s]');
    }
}
