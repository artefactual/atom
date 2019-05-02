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

class SettingsClipboardAction extends SettingsEditAction
{
  // Arrays not allowed in class constants
  public static
    $NAMES = array(
      'clipboard_save_max_age',
      'clipboard_send_enabled',
      'clipboard_send_url',
      'clipboard_send_button_text',
      'clipboard_send_message_html',
      'clipboard_send_http_method');

  public function earlyExecute()
  {
    parent::earlyExecute($request);

    $this->updateMessage = $this->i18n->__('Clipboard settings saved.');

    $this->settingDefaults = array(
      'clipboard_save_max_age' => '0',
      'clipboard_send_enabled' => '0',
      'clipboard_send_button_text' => $this->i18n->__('Send'),
      'clipboard_send_message_html' => $this->i18n->__('%1%Sending...%2%', array('%1%' => '<h1>', '%2%' => '</h1>')),
      'clipboard_send_http_method' => 'POST'
    );
  }

  protected function addField($name)
  {
    $this->setFormFieldDefault($name);

    // Set form field format
    switch ($name)
    {
      case 'clipboard_save_max_age':
      case 'clipboard_send_url':
      case 'clipboard_send_button_text':
      case 'clipboard_send_message_html':
        $this->form->setValidator($name, new sfValidatorString());
        $this->form->setWidget($name, new sfWidgetFormInput);

        break;

      case 'clipboard_send_enabled':
      case 'clipboard_send_http_method':
        if ($name == 'clipboard_send_enabled')
        {
          $options = array($this->i18n->__('No'), $this->i18n->__('Yes'));
        }
        else
        {
          $options = array('POST' => 'POST', 'GET' => 'GET');
        }

        $this->form->setValidator($name, new sfValidatorString(array('required' => false)));
        $this->form->setWidget($name, new sfWidgetFormSelectRadio(array('choices' => $options), array('class' => 'radio')));

        break;
    }
  }
}
