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

class DeaccessionEditAction extends DefaultEditAction
{
    // Arrays not allowed in class constants
    public static $NAMES = [
        'date',
        'description',
        'extent',
        'identifier',
        'reason',
        'scope',
    ];

    public function earlyExecute()
    {
        $this->form->getValidatorSchema()->setOption('allow_extra_fields', true);

        $this->resource = new QubitDeaccession();

        if (isset($this->getRoute()->resource)) {
            $this->resource = $this->getRoute()->resource;

            // Check user authorization
            if (!QubitAcl::check($this->resource, 'update')) {
                QubitAcl::forwardUnauthorized();
            }
        } else {
            $this->form->setDefault('accessionId', $this->request->accession);
            $this->form->setValidator('accessionId', new sfValidatorInteger());
            $this->form->setWidget('accessionId', new sfWidgetFormInputHidden());

            $this->resource->accessionId = $this->request->accession;

            // Check user authorization
            if (!QubitAcl::check($this->resource, 'create')) {
                QubitAcl::forwardUnauthorized();
            }
        }

        $title = $this->context->i18n->__('Add new deaccession record');
        if (isset($this->getRoute()->resource)) {
            if (1 > strlen($title = $this->resource->__toString())) {
                $title = $this->context->i18n->__('Untitled');
            }

            $title = $this->context->i18n->__('Edit %1%', ['%1%' => $title]);
        }

        $this->response->setTitle("{$title} - {$this->response->getTitle()}");
    }

    public function execute($request)
    {
        parent::execute($request);

        if ($request->isMethod('post')) {
            $this->form->bind($request->getPostParameters());
            if ($this->form->isValid()) {
                $this->processForm();

                $this->resource->save();

                $this->redirect([$this->resource->accession, 'module' => 'accession']);
            }
        }

        QubitDescription::addAssets($this->response);
    }

    protected function addField($name)
    {
        switch ($name) {
            case 'scope':
                $this->form->setDefault('scope', $this->context->routing->generate(null, [$this->resource->scope, 'module' => 'term']));
                $this->form->setValidator('scope', new sfValidatorString());

                $choices = [];
                $choices[null] = null;
                foreach (QubitTaxonomy::getTermsById(QubitTaxonomy::DEACCESSION_SCOPE_ID) as $item) {
                    $choices[$this->context->routing->generate(null, [$item, 'module' => 'term'])] = $item;
                }

                $this->form->setWidget('scope', new sfWidgetFormSelect(['choices' => $choices]));

                break;

            case 'description':
            case 'extent':
            case 'reason':
                $this->form->setDefault($name, $this->resource[$name]);
                $this->form->setValidator($name, new sfValidatorString());
                $this->form->setWidget($name, new sfWidgetFormTextarea());

                break;

            case 'date':
                $this->form->setDefault('date', Qubit::renderDate($this->resource['date']));

                if (!isset($this->resource->id)) {
                    $dt = new DateTime();
                    $this->form->setDefault('date', $dt->format('Y-m-d'));
                }

                $this->form->setWidget('date', new sfWidgetFormInput());
                $this->form->setValidator('date', new sfValidatorDate([
                    'date_format' => '/^(?P<year>\d{4})-(?P<month>\d{2})-(?P<day>\d{2})$/',
                    'date_format_error' => 'YYYY-MM-DD',
                ]));

                break;

            case 'identifier':
                $this->form->setDefault($name, $this->resource[$name]);
                $this->form->setValidator($name, new sfValidatorString());
                $this->form->setWidget($name, new sfWidgetFormInput());

                break;

            default:
                return parent::addField($name);
        }
    }

    protected function processField($field)
    {
        switch ($field->getName()) {
            case 'scope':
                unset($this->resource->scope);

                $value = $this->form->getValue('scope');
                if (isset($value)) {
                    $params = $this->context->routing->parse(Qubit::pathInfo($value));
                    $this->resource->scope = $params['_sf_route']->resource;
                }

                break;

            default:
                return parent::processField($field);
        }
    }
}
