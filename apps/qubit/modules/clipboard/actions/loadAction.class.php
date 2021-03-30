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

class ClipboardLoadAction extends DefaultEditAction
{
    // Arrays not allowed in class constants
    public static $NAMES = [
        'password',
        'mode',
    ];

    public function execute($request)
    {
        parent::execute($request);

        if (!$request->isMethod('post')) {
            return;
        }

        $this->response->setHttpHeader('Content-Type', 'application/json; charset=utf-8');

        $this->form->bind($request->getPostParameters());

        if (!$this->form->isValid()) {
            $this->response->setStatusCode(400);
            $message = $this->context->i18n->__('Incorrect clipboard ID and/or action.');

            return $this->renderText(json_encode(['error' => $message]));
        }

        $this->processForm();

        $criteria = new Criteria();
        $criteria->add(QubitClipboardSave::PASSWORD, $this->password);
        $save = QubitClipboardSave::getOne($criteria);

        if (!isset($save)) {
            $this->response->setStatusCode(404);
            $message = $this->context->i18n->__('Clipboard ID not found.');

            return $this->renderText(json_encode(['error' => $message]));
        }

        $criteria = new Criteria();
        $criteria->add(QubitClipboardSaveItem::SAVE_ID, $save->id);
        $items = QubitClipboardSaveItem::get($criteria);

        $clipboard = [
            'informationObject' => [],
            'actor' => [],
            'repository' => [],
        ];
        $addedCount = 0;

        foreach ($items as $item) {
            // Add slug to clipboard if the object exists and the user can read it
            $object = QubitObject::getBySlug($item->slug);

            if (isset($object) && QubitAcl::check($object, 'read')) {
                $type = lcfirst(str_replace('Qubit', '', $item->itemClassName));
                array_push($clipboard[$type], $item->slug);
                ++$addedCount;
            }
        }

        if ('replace' == $this->mode) {
            $actionDescription = $this->context->i18n->__('added');
        } else {
            $actionDescription = $this->context->i18n->__('merged with current clipboard');
        }

        $message = $this->context->i18n->__(
            'Clipboard %1% loaded, %2% records %3%.',
            ['%1%' => $this->password, '%2%' => $addedCount, '%3%' => $actionDescription]
        );

        $this->response->setStatusCode(200);

        return $this->renderText(json_encode(['success' => $message, 'clipboard' => $clipboard]));
    }

    protected function addField($name)
    {
        switch ($name) {
            case 'password':
                $this->form->setValidator('password', new sfValidatorString(['required' => true]));
                $this->form->setWidget('password', new sfWidgetFormInput());

                break;

            case 'mode':
                $this->form->setDefault('mode', 'merge');
                $this->form->setValidator('mode', new sfValidatorString());
                $choices = [
                    'merge' => $this->context->i18n->__('Merge saved clipboard with existing clipboard results'),
                    'replace' => $this->context->i18n->__('Replace existing clipboard results with saved clipboard'),
                ];
                $this->form->setWidget('mode', new sfWidgetFormSelect(['choices' => $choices]));

                break;
        }
    }

    protected function processField($field)
    {
        switch ($field->getName()) {
            case 'password':
                $this->password = $this->form->getValue($field->getName());

                break;

            case 'mode':
                $this->mode = $this->form->getValue($field->getName());

                break;
        }
    }
}
