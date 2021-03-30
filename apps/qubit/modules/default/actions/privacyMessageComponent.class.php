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
 * Display privacy message component.
 *
 * @author Steven Breker <sbreker@artefactual.com>
 */
class DefaultPrivacyMessageComponent extends sfComponent
{
    public function execute($request)
    {
        // Do not display the banner if it has previously been dismissed or is deactivated.
        if (
            null !== $this->context->user->getAttribute('privacy_message_dismissed')
            || !sfConfig::get('app_privacy_notification_enabled', false)
        ) {
            return sfView::NONE;
        }

        // Set the notification message content for the privacy banner.
        $this->notificationMessage = QubitSetting::getByName('privacy_notification');
    }
}
