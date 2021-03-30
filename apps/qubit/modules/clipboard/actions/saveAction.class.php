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

class ClipboardSaveAction extends sfAction
{
    public function execute($request)
    {
        $slugs = $request->getPostParameter('slugs', []);
        $failMessage = $this->context->i18n->__('Clipboard ID generation failure.');

        if (empty($slugs) || (empty($slugs['informationObject']) && empty($slugs['actor']) && empty($slugs['repository']))) {
            $this->response->setStatusCode(400);
            $responseData = ['error' => $failMessage.' '.$this->context->i18n->__('No items in clipboard to save.')];
        } elseif (null === $validatedSlugs = $this->validateSlugs($slugs)) {
            $this->response->setStatusCode(400);
            $responseData = ['error' => $failMessage.' '.$this->context->i18n->__('No items found.')];
        } elseif (null === $password = $this->getUniquePassword()) {
            $this->response->setStatusCode(500);
            $responseData = ['error' => $failMessage.' '.$this->context->i18n->__('Please try again.')];
        } else {
            $this->saveClipboard($validatedSlugs, $password);

            $itemsCount = count($validatedSlugs['informationObject']) + count($validatedSlugs['actor']) + count($validatedSlugs['repository']);

            $loadUrl = $this->context->routing->generate(null, ['module' => 'clipboard', 'action' => 'load']);
            $message = $this->context->i18n->__('Clipboard saved with %1% items. Clipboard ID is <b>%2%</b>. Please write this number down. When you want to reload this clipboard in the future, open the Clipboard menu, select <a href="%3%">Load clipboard</a>, and enter this number in the Clipboard ID field.', ['%1%' => $itemsCount, '%2%' => $password, '%3%' => $loadUrl]);

            $this->response->setStatusCode(200);
            $responseData = ['success' => $message];
        }

        $this->response->setHttpHeader('Content-Type', 'application/json; charset=utf-8');

        return $this->renderText(json_encode($responseData));
    }

    private function getUniquePassword()
    {
        // Try a max of 100 times before giving up (avoid infinite loops when
        // possible passwords exhausted)
        for ($i = 0; $i < 100; ++$i) {
            $password = $this->generatePassword();

            $criteria = new Criteria();
            $criteria->add(QubitClipboardSave::PASSWORD, $password);

            $result = QubitClipboardSave::getOne($criteria);

            if (null === $result) {
                return $password;
            }
        }
    }

    private function validateSlugs($allSlugs)
    {
        $validatedSlugs = [];

        foreach ($allSlugs as $type => $slugs) {
            $validatedSlugs[$type] = [];

            foreach ($slugs as $slug) {
                $sql = 'SELECT COUNT(s.id) FROM slug s
                    JOIN object o ON s.object_id = o.id
                    WHERE s.slug = ? AND o.class_name = ?
                    AND o.id NOT IN (?, ?, ?)';

                $count = QubitPdo::fetchColumn(
                    $sql,
                    [
                        $slug,
                        'Qubit'.ucfirst($type),
                        QubitInformationObject::ROOT_ID,
                        QubitActor::ROOT_ID,
                        QubitRepository::ROOT_ID,
                    ]
                );

                if (1 == $count) {
                    $validatedSlugs[$type][] = $slug;
                }
            }
        }

        if (
            !empty($validatedSlugs['informationObject'])
            || !empty($validatedSlugs['actor'])
            || !empty($validatedSlugs['repository'])
        ) {
            return $validatedSlugs;
        }
    }

    private function generatePassword()
    {
        $passwordLength = 7;
        $alphabet = '0123456789';
        $alphabetSize = strlen($alphabet);

        $password = '';
        for ($i = 0; $i < $passwordLength; ++$i) {
            $password .= $alphabet[mt_rand(0, $alphabetSize - 1)];
        }

        return $password;
    }

    private function saveClipboard($validatedSlugs, $password)
    {
        // Create save clipboard using password
        $save = new QubitClipboardSave();
        $save->userId = $this->context->user->getUserID();
        $save->password = $password;
        $save->save();

        // Store clipboard items in database
        foreach ($validatedSlugs as $type => $slugs) {
            foreach ($slugs as $slug) {
                $item = new QubitClipboardSaveItem();
                $item->saveId = $save->id;
                $item->itemClassName = 'Qubit'.ucfirst($type);
                $item->slug = $slug;
                $item->save();
            }
        }
    }
}
