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

class DeaccessionIndexAction extends sfAction
{
    public function execute($request)
    {
        $this->resource = $this->getRoute()->resource;

        // Check user authorization
        if (!QubitAcl::check($this->resource, 'read')) {
            QubitAcl::forwardToSecureAction();
        }

        if (1 > strlen($title = $this->resource->__toString())) {
            $title = $this->context->i18n->__('Untitled');
        }

        $this->response->setTitle("{$title} - {$this->response->getTitle()}");

        if (QubitAcl::check($this->resource, 'update')) {
            $validatorSchema = new sfValidatorSchema();
            $values = [];

            $validatorSchema->identifier = new sfValidatorString(
                ['required' => true],
                ['required' => $this->context->i18n->__('Identifier - This field is marked as mandatory in the relevant descriptive standard.').'"<span>*</span><span class="visually-hidden">'.$this->context->i18n->__('This field is marked as mandatory in the relevant descriptive standard.').'</span>']
            );
            $values['identifier'] = $this->resource->identifier;

            $validatorSchema->date = new sfValidatorString(
                ['required' => true],
                ['required' => $this->context->i18n->__('Date of acquisition - This field is marked as mandatory in the relevant descriptive standard.').'"<span>*</span><span class="visually-hidden">'.$this->context->i18n->__('This field is marked as mandatory in the relevant descriptive standard.').'</span>']
            );
            $values['date'] = $this->resource->date;

            $validatorSchema->scope = new sfValidatorString(
                ['required' => true],
                ['required' => $this->context->i18n->__('Scope - This field is marked as mandatory in the relevant descriptive standard.').'"<span>*</span><span class="visually-hidden">'.$this->context->i18n->__('This field is marked as mandatory in the relevant descriptive standard.').'</span>']
            );
            $values['scope'] = $this->resource->scope;

            $validatorSchema->description = new sfValidatorString(
                ['required' => true],
                ['required' => $this->context->i18n->__('Description - This field is marked as mandatory in the relevant descriptive standard.').'"<span>*</span><span class="visually-hidden">'.$this->context->i18n->__('This field is marked as mandatory in the relevant descriptive standard.').'</span>']
            );
            $values['description'] = $this->resource->getDescription(['culltureFallback' => true]);

            try {
                $validatorSchema->clean($values);
            } catch (sfValidatorErrorSchema $e) {
                $this->errorSchema = $e;
            }
        }
    }
}
