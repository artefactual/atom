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
 * Display bootstrap 2 theme deprecation message component.
 *
 * @author Anvit Srivastav <asrivastav@artefactual.com>
 */
class DefaultBS2DeprecationMessageComponent extends sfComponent
{
    public function execute($request)
    {
        $hasTranslateOrEditAccess = $this->context->user->isAdministrator()
            || $this->getUser()->hasGroup(QubitAclGroup::TRANSLATOR_ID)
            || $this->getUser()->hasGroup(QubitAclGroup::EDITOR_ID);

        // Only display this banner to editors, translators, and admins and
        // do not display if the theme uses BS5 if it has previously been dismissed
        if (!$hasTranslateOrEditAccess || sfConfig::get('app_b5_theme', false)
            || null !== $this->context->user->getAttribute('bs2_deprecation_message_dismissed')
        ) {
            return sfView::NONE;
        }
    }
}
