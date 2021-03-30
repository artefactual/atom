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
 * Information Object - showDc.
 *
 * @author     Peter Van Garderen <peter@artefactual.com>
 */
class sfDcPluginIndexAction extends InformationObjectIndexAction
{
    public function execute($request)
    {
        parent::execute($request);

        $this->dc = new sfDcPlugin($this->resource);

        if (1 > strlen($title = $this->resource->__toString())) {
            $title = $this->context->i18n->__('Untitled');
        }

        $this->response->setTitle("{$title} - {$this->response->getTitle()}");

        if (QubitAcl::check($this->resource, 'update')) {
            $validatorSchema = new sfValidatorSchema();
            $values = [];

            $validatorSchema->identifier = new sfValidatorString(
                ['required' => true],
                ['required' => $this->context->i18n->__(
                    '%1%Identifier%2% - This is a mandatory element.',
                    [
                        '%1%' => '<a href="http://dublincore.org/documents/dcmi-terms/#elements-identifier">',
                        '%2%' => '</a>',
                    ]
                )]
                );
            $values['identifier'] = $this->resource->identifier;

            $validatorSchema->title = new sfValidatorString(
                ['required' => true],
                ['required' => $this->context->i18n->__(
                    '%1%Title%2% - This is a mandatory element.',
                    [
                        '%1%' => '<a href="http://dublincore.org/documents/dcmi-terms/#elements-title">',
                        '%2%' => '</a>',
                    ]
                )]
            );
            $values['title'] = $this->resource->getTitle(['cultureFallback' => true]);

            $validatorSchema->repository = new sfValidatorString(
                ['required' => true],
                ['required' => $this->context->i18n->__(
                    '%1%Relation%2% (%3%isLocatedAt%4%) - This is a mandatory element for this resource or one of its higher descriptive levels (if part of a collection hierarchy).',
                    [
                        '%1%' => '<a href="http://dublincore.org/documents/dcmi-terms/#elements-relation">',
                        '%2%' => '</a>',
                        '%3%' => '<a href="http://dublincore.org/groups/collections/collection-application-profile/#colcldisLocatedAt">',
                        '%4%' => '</a>',
                    ]
                )]
            );

            foreach ($this->resource->ancestors->andSelf() as $item) {
                $values['repository'] = $item->repository;
                if (isset($values['repository'])) {
                    break;
                }
            }

            try {
                $validatorSchema->clean($values);
            } catch (sfValidatorErrorSchema $e) {
                $this->errorSchema = $e;
            }
        }
    }
}
