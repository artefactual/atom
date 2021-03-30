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

class AccessionAlternativeIdentifiersComponent extends sfComponent
{
    public function execute($request)
    {
        // Cache alternative identifier types (used in each identifier's type select form field)
        $criteria = new Criteria();
        $criteria->add(QubitTerm::TAXONOMY_ID, QubitTaxonomy::ACCESSION_ALTERNATIVE_IDENTIFIER_TYPE_ID);

        $this->identifierTypes = [];
        foreach (QubitTerm::get($criteria) as $term) {
            $this->identifierTypes[$term->id] = $term->getName(['cultureFallback' => true]);
        }

        // Define form used to add/edit identifiers
        $this->form = new sfForm();
        $this->form->getValidatorSchema()->setOption('allow_extra_fields', true);

        $this->addField('identifierType');
        $this->addField('identifier');
        $this->addField('note');

        // Summarize/cache existing alternative identifier data
        $this->alternativeIdentifierData = [];

        foreach ($this->resource->getAlternativeIdentifiers() as $identifier) {
            $this->alternativeIdentifierData[] = [
                'id' => $identifier->id,
                'value' => $identifier->getName(['sourceCulture' => true]),
                'typeId' => $identifier->typeId,
                'hasNote' => !empty($identifier->getNote(['cultureFallback' => true])),
                'note' => $identifier->getNote(),
                'object' => $identifier,
            ];
        }
    }

    public function processForm()
    {
        $finalAlternativeIdentifiers = [];

        if (is_array($this->request->alternativeIdentifiers)) {
            foreach ($this->request->alternativeIdentifiers as $item) {
                // Continue only if both fields are populated
                if (1 > strlen($item['identifierType']) || 1 > strlen($item['identifier'])) {
                    continue;
                }

                if (!empty($item['id'])) {
                    $finalAlternativeIdentifiers[] = $item['id'];

                    $otherName = QubitOtherName::getById($item['id']);
                } else {
                    $otherName = new QubitOtherName();
                }

                $otherName->object = $this->resource;
                $otherName->typeId = $item['identifierType'];
                $otherName->name = $item['identifier'];
                $otherName->note = $item['note'];
                $otherName->save();
            }
        }

        // Delete the old alternative identifiers if they don't appear in the table (removed by multiRow.js)
        foreach ($this->alternativeIdentifierData as $identifier) {
            if (false === array_search($identifier['id'], $finalAlternativeIdentifiers)) {
                $identifier['object']->delete();
            }
        }
    }

    protected function addField($name)
    {
        switch ($name) {
            case 'identifierType':
                $this->form->setValidator($name, new sfValidatorInteger());
                $this->form->setWidget($name, new sfWidgetFormSelect(['choices' => $this->identifierTypes]));

                break;

            case 'identifier':
                $this->form->setValidator($name, new sfValidatorString());
                $this->form->setWidget($name, new sfWidgetFormInput());

                break;

            case 'note':
                $this->form->setValidator($name, new sfValidatorString());
                $widget = new sfWidgetFormTextarea(['label' => false]);
                $widget->setAttribute('placeholder', $this->context->i18n->__('Notes'));
                $this->form->setWidget($name, $widget);

                break;
        }
    }
}
