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
 * Information Object - showRad.
 *
 * @author     Peter Van Garderen <peter@artefactual.com>
 */
class sfRadPluginIndexAction extends InformationObjectIndexAction
{
    public function execute($request)
    {
        parent::execute($request);

        $this->rad = new sfRadPlugin($this->resource);

        if (1 > strlen($title = $this->resource->__toString())) {
            $title = $this->context->i18n->__('Untitled');
        }

        // Set creator history label
        $this->creatorHistoryLabels = [
            null => $this->context->i18n->__('Administrative history / Biographical sketch'),
            QubitTerm::CORPORATE_BODY_ID => $this->context->i18n->__('Administrative history'),
            QubitTerm::PERSON_ID => $this->context->i18n->__('Biographical sketch'),
            QubitTerm::FAMILY_ID => $this->context->i18n->__('Biographical sketch'),
        ];

        $this->response->setTitle("{$title} - {$this->response->getTitle()}");

        if (QubitAcl::check($this->resource, 'update')) {
            $validatorSchema = new sfValidatorSchema();
            $values = [];

            $validatorSchema->dates = new QubitValidatorCountable(
                ['required' => true],
                ['required' => $this->context->i18n->__('This archival description requires at least one date.')]
            );
            $values['dates'] = $this->resource->getDates();

            // Dates consistency
            $validatorSchema->dateRange = new QubitValidatorDates(
                [],
                ['invalid' => $this->context->i18n->__(
                    'Date(s) - are not consistent with %1%higher levels%2%.',
                    [
                        '%1%' => '<a class="alert-link" href="%ancestor%">',
                        '%2%' => '</a>',
                    ]
                )]
            );
            $values['dateRange'] = $this->resource;

            $validatorSchema->extentAndMedium = new sfValidatorString(
                ['required' => true],
                ['required' => $this->context->i18n->__('Physical description - This field is marked as mandatory in the relevant descriptive standard.').'"<span>*</span><span class="visually-hidden">'.$this->context->i18n->__('This field is marked as mandatory in the relevant descriptive standard.').'</span>']);
            $values['extentAndMedium'] = $this->resource->getExtentAndMedium(['cultureFallback' => true]);

            $validatorSchema->title = new sfValidatorString(
                ['required' => true],
                ['required' => $this->context->i18n->__('Title - This field is marked as mandatory in the relevant descriptive standard.').'"<span>*</span><span class="visually-hidden">'.$this->context->i18n->__('This field is marked as mandatory in the relevant descriptive standard.').'</span>']
            );
            $values['title'] = $this->resource->getTitle(['cultureFallback' => true]);

            $this->addField($validatorSchema, 'levelOfDescription');
            $validatorSchema->levelOfDescription->setMessage('forbidden', $this->context->i18n->__('Level of description - Value "%value%" is not consistent with higher levels.'));
            $validatorSchema->levelOfDescription->setMessage('required', $this->context->i18n->__('Level of description - This field is marked as mandatory in the relevant descriptive standard.').'"<span>*</span><span class="visually-hidden">'.$this->context->i18n->__('This field is marked as mandatory in the relevant descriptive standard.').'</span>');

            if (isset($this->resource->levelOfDescription)) {
                $values['levelOfDescription'] = $this->resource->levelOfDescription->getName(['sourceCulture' => true]);
            }

            // Class of materials specific details
            foreach ($this->resource->getMaterialTypes() as $materialType) {
                switch ($materialType->term->getName(['sourceCulture' => true])) {
                    case 'Architectural drawing':
                        $validatorSchema->statementOfScaleArchitectural = new sfValidatorString(
                            ['required' => true],
                            ['required' => $this->context->i18n->__('Statement of scale (architectural) - This is a mandatory element for architectural drawing.')]
                        );
                        $values['statementOfScaleArchitectural'] = $this->rad->getProperty('statementOfScaleArchitectural');

                        break;

                    case 'Cartographic material':
                        $validatorSchema->statementOfCoordinates = new sfValidatorString(
                            ['required' => true],
                            ['required' => $this->context->i18n->__('Statement of coordinates (cartographic) - This is a mandatory element for cartographic material.')]
                        );
                        $values['statementOfCoordinates'] = $this->rad->getProperty('statementOfCoordinates');

                        $validatorSchema->statementOfProjection = new sfValidatorString(
                            ['required' => true],
                            ['required' => $this->context->i18n->__('Statement of projection (cartographic) - This is a mandatory element for cartographic material.')]
                        );
                        $values['statementOfProjection'] = $this->rad->getProperty('statementOfProjection');

                        $validatorSchema->statementOfScaleCartographic = new sfValidatorString(
                            ['required' => true],
                            ['required' => $this->context->i18n->__('Statement of scale (cartographic) - This is a mandatory element for cartographic material.')]
                        );
                        $values['statementOfScaleCartographic'] = $this->rad->getProperty('statementOfScaleCartographic');

                        break;

                    case 'Philatelic record':
                        $validatorSchema->issuingJurisdictionAndDenomination = new sfValidatorString(
                            ['required' => true],
                            ['required' => $this->context->i18n->__('Issuing jurisdiction and denomination (philatelic) - This is a mandatory element for philatelic record.')]
                        );
                        $values['issuingJurisdictionAndDenomination'] = $this->rad->getProperty('issuingJurisdictionAndDenomination');

                        break;
                }
            }

            if (isset($this->resource->levelOfDescription)) {
                switch ($this->resource->levelOfDescription->getName(['sourceCulture' => true])) {
                    // Only if top level of description
                    /* Disable custodial history validation temporary (see issue 1984)
                    case 'Series':
                    case 'Fonds':
                    case 'Collection':

                        if (!isset($this->resource->parent->parent))
                        {
                        $validatorSchema->custodialHistory = new sfValidatorString(array('required' => true), array('required' => $this->context->i18n->__('Custodial history - This is a mandatory element for top level of description.')));
                        $values['custodialHistory'] = $this->resource->getArchivalHistory(array('cultureFallback' => true));
                        }

                        break;
                    */

                    case 'Series':
                    case 'Fonds':
                    case 'Collection':
                    case 'Subseries':
                    case 'Subfonds':
                        $validatorSchema->scopeAndContent = new sfValidatorString(
                            ['required' => true],
                            ['required' => $this->context->i18n->__('Scope and content - This field is marked as mandatory in the relevant descriptive standard.').'"<span>*</span><span class="visually-hidden">'.$this->context->i18n->__('This field is marked as mandatory in the relevant descriptive standard.').'</span>']
                        );
                        $values['scopeAndContent'] = $this->resource->getScopeAndContent(['cultureFallback' => true]);

                        break;

                    case 'Item':
                        // No publication events?
                        $isPublication = false;
                        foreach ($this->resource->eventsRelatedByobjectId as $item) {
                            if (QubitTerm::PUBLICATION_ID == $item->typeId) {
                                $isPublication = true;

                                break;
                            }
                        }

                        if ($isPublication) {
                            $validatorSchema->edition = new sfValidatorString(
                                ['required' => true],
                                ['required' => $this->context->i18n->__('Edition statement - This is a mandatory element for published items if there are multiple editions.')]
                            );
                            $values['edition'] = $this->resource->getEdition(['cultureFallback' => true]);
                        }
                }
            }

            try {
                $validatorSchema->clean($values);
            } catch (sfValidatorErrorSchema $e) {
                $this->errorSchema = $e;
            }
        }

        // Term ID constants for Rights and Conservation terms
        $this->rightsTermID = $this->getRADTermID('Rights');
        $this->conservationTermID = $this->getRADTermID('Conservation');
    }

    protected function getRADTermID($term)
    {
        $sql = "SELECT term.id FROM term
            JOIN term_i18n ON term_i18n.id = term.id
            WHERE term.taxonomy_id=?
            AND term_i18n.culture='en'
            AND term_i18n.name=?";

        return QubitPdo::fetchColumn($sql, [QubitTaxonomy::RAD_NOTE_ID, $term]);
    }
}
