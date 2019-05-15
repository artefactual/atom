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

class UserClipboardSendAction extends sfAction
{
  public function execute($request)
  {
    if (!sfConfig::get('app_clipboard_send_enabled', false))
    {
      // 403 - Forbidden
      $this->getResponse()->setStatusCode(403);

      return sfView::HEADER_ONLY;
    }

    $this->allSlugs = $this->context->user->getClipboard()->getAllByClassName();

    if (!count($this->allSlugs))
    {
      // Inform user that there's nothing to send and return to clipboard page
      $message = $this->context->i18n->__('No items in clipboard to send.');
      $this->context->user->setFlash('error', $message);

      $this->redirect(array('module' => 'user', 'action' => 'clipboard'));
    }
    else
    {
      // Set message shown to user while message is sent
      $sendMessageHtml = $this->context->i18n->__('Sending...');
      $this->sendMessageHtml = sfConfig::get('app_clipboard_send_message_html', $sendMessageHtml);

      // Set where and how data is to be sent
      $this->sendUrl = sfConfig::get('app_clipboard_send_url', '');
      $this->sendHttpMethod = sfConfig::get('app_clipboard_send_http_method', 'POST');

      // Set payload data (site base URL and slugs in clipboard)
      $this->siteBaseUrl = sfConfig::get('app_siteBaseUrl');
      $this->classSlugFieldNames = array();

      // Create human-friendly form input names based on clipboard item's class name
      foreach ($this->allSlugs as $className => $slugs)
      {
        $this->classSlugFieldNames[$className] = sfInflector::underscore(str_replace('Qubit', '', $className)) .'_slugs';
      }
    }
  }
}
