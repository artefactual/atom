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
 * Item list report.
 *
 * @author     Peter Van Garderen <peter@artefactual.com>
 * @author     David Juhasz <david@artefactual.com>
 */
class InformationObjectItemOrFileListAction extends sfAction
{
    // Arrays not allowed in class constants
    public static $NAMES = [
        'sortBy',
        'includeThumbnails',
        'format',
    ];

    public function execute($request)
    {
        sfContext::getInstance()->getConfiguration()->loadHelpers(['Url']);
        $this->resource = $this->getRoute()->resource;
        $this->type = isset($request->type) ? ucfirst($request->type) : $this->context->i18n->__('Item');

        if (!isset($this->resource)) {
            $this->forward404();
        }

        $this->form = new sfForm();

        foreach ($this::$NAMES as $name) {
            $this->addField($name);
        }

        if ($request->isMethod('post')) {
            $this->form->bind($request->getPostParameters());

            if ($this->form->isValid()) {
                $this->initiateReportGeneration();
                $this->redirect([$this->resource, 'module' => 'informationobject']);
            }
        }

        return 'Criteria';
    }

    protected function addField($name)
    {
        switch ($name) {
            case 'sortBy':
                $choices = [
                    'referenceCode' => $this->context->i18n->__('Reference code'),
                    'title' => $this->context->i18n->__('Title'),
                    'startDate' => $this->context->i18n->__('Date (based on start date)'),
                ];

                if ($this->getUser()->isAuthenticated()) {
                    $choices['locations'] = $this->context->i18n->__('Retrieval information');
                }

                $this->form->setDefault($name, 'referenceCode');
                $this->form->setValidator($name, new sfValidatorChoice(['choices' => array_keys($choices)]));
                $this->form->setWidget($name, new sfWidgetFormChoice([
                    'expanded' => true,
                    'choices' => $choices,
                ]));

                break;

            case 'includeThumbnails':
                $choices = ['1' => $this->context->i18n->__('Yes')];

                $this->form->setValidator($name, new sfValidatorChoice([
                    'choices' => array_keys($choices),
                    'multiple' => true,
                ]));

                $this->form->setWidget($name, new sfWidgetFormChoice([
                    'expanded' => true,
                    'multiple' => true,
                    'choices' => $choices,
                ]));

                break;

            case 'format':
                $choices = ['html' => 'HTML', 'csv' => 'CSV'];
                $this->form->setDefault($name, 'html');
                $this->form->setValidator($name, new sfValidatorChoice(['choices' => array_keys($choices)]));
                $this->form->setWidget($name, new sfWidgetFormChoice(['expanded' => true, 'choices' => $choices]));
        }
    }

    private function initiateReportGeneration()
    {
        $reportType = (false === strpos(strtolower($this->type), 'item')) ? 'fileList' : 'itemList';

        if (
            is_array($this->form->includeThumbnails->getValue())
            && '1' === array_pop($this->form->includeThumbnails->getValue())
        ) {
            $includeThumbnails = true;
        } else {
            $includeThumbnails = false;
        }

        $params = [
            'objectId' => $this->resource->id,
            'reportType' => $reportType,
            'reportTypeLabel' => $this->type,
            'sortBy' => $this->form->sortBy->getValue(),
            'reportFormat' => $this->form->format->getValue(),
            'includeThumbnails' => $includeThumbnails,
        ];

        QubitJob::runJob('arGenerateReportJob', $params);

        $reportsUrl = url_for([$this->resource, 'module' => 'informationobject', 'action' => 'reports']);
        $message = $this->context->i18n->__(
            'Report generation has started, please check the <a href="%1">reports</a> page again soon.',
            ['%1' => $reportsUrl]
        );

        $this->getUser()->setFlash('notice', $message);
    }
}
