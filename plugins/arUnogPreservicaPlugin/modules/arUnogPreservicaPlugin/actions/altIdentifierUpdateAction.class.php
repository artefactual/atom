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

class arUnogPreservicaPluginAltIdentifierUpdateAction extends QubitApiAction
{
    protected function post($request, $payload)
    {
        $altIdLabel = $payload->label;
        $altIdCurrentId = $payload->current_id;
        $altIdNewId = $payload->new_id;

        // Fail if any of the necessary parameters haven't been provided
        if (empty($altIdLabel) || empty($altIdCurrentId) || empty($altIdNewId)) {
            $error = 'These parameters are required: "label", "current_id", and "new_id".';
            $message = $this->context->i18n->__($error);

            throw new QubitApiBadRequestException($message);
        }

        // Attempt to find alternative identifier
        $criteria = new Criteria();
        $criteria->add(QubitProperty::SCOPE, 'alternativeIdentifiers');
        $criteria->add(QubitProperty::NAME, $altIdLabel);
        $criteria->addJoin(QubitProperty::ID, QubitPropertyI18n::ID);
        $criteria->add(QubitPropertyI18n::VALUE, $altIdCurrentId);

        if (empty($altIdProperty = QubitProperty::getOne($criteria))) {
            $error = 'Alternative identifier not found.';
            $message = $this->context->i18n->__($error);

            throw new QubitApiBadRequestException($message);
        }

        // Change alternative identifier
        $altIdProperty->setValue($altIdNewId, ['sourceCulture' => true]);
        $altIdProperty->save();

        $message = $this->context->i18n->__('Updated.');

        return ['message' => $message];
    }
}
