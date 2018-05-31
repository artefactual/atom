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

 class SettingsPrivacyNotificationAction extends DefaultEditAction
 {
   // Arrays not allowed in class constants
   public static
     $NAMES = array(
       'privacy_notification_enabled',
       'privacy_notification');

   protected function earlyExecute()
   {
     $this->i18n = sfContext::getInstance()->i18n;
   }

   protected function addField($name)
   {
     switch ($name)
     {
       case 'privacy_notification':
         // Determine default value
         $default = (null !== $this->$name = QubitSetting::getByName($name))
           ? $this->$name->getValue(array('sourceCulture' => true))
           : '';

         // Set default, validator, and widget
         $this->form->setDefault($name, $default);
         $this->form->setValidator($name, new sfValidatorString(array('required' => true)));
         $this->form->setWidget($name, new sfWidgetFormInput);

         break;

       case 'privacy_notification_enabled':
         // Determine default value
         // (accession mask enabled setting doesn't get created in DB by default)
         $defaults = array('privacy_notification_enabled' => 1);

         $default = (null !== $this->$name = QubitSetting::getByName($name))
           ? $this->$name->getValue(array('sourceCulture' => true))
           : $defaults[$name];

         // Set default, validator, and widget
         $options = array($this->i18n->__('No'), $this->i18n->__('Yes'));
         $this->form->setDefault($name, $default);
         $this->form->setValidator($name, new sfValidatorString(array('required' => false)));
         $this->form->setWidget($name, new sfWidgetFormSelectRadio(array('choices' => $options), array('class' => 'radio')));

         break;
     }
   }

   protected function processField($field)
   {
     switch ($name = $field->getName())
     {
       case 'privacy_notification_enabled':
       case 'privacy_notification':
         if (null === $this->$name)
         {
           $this->$name = new QubitSetting;
           $this->$name->name = $name;
         }
         $this->$name->setValue($field->getValue(), array('sourceCulture' => true));
         $this->$name->save();

         break;
     }
   }

   public function execute($request)
   {
     parent::execute($request);

     if ($request->isMethod('post'))
     {
       $this->form->bind($request->getPostParameters());

       if ($this->form->isValid())
       {
         $this->processForm();

         QubitCache::getInstance()->removePattern('settings:i18n:*');

         $this->getUser()->setFlash('notice', $this->i18n->__('Privacy Notification settings saved.'));

         $this->redirect(array('module' => 'settings', 'action' => 'privacyNotification'));
       }
     }
   }
 }
