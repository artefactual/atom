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

class UserClipboardSaveAction extends sfAction
{
  public function execute($request)
  {
    $allSlugs = $this->context->user->getClipboard()->getAllByClassName();

    if (!count($allSlugs))
    {
      $message = $this->context->i18n->__('No items in clipboard to save.');
      $this->context->user->setFlash('error', $message);
    }
    else
    {
      // Generate unique password
      if (false === $password = $this->getUniquePassword())
      {
        $message = $this->context->i18n->__('Clipboard ID generation failure. Please try again.');
        $this->context->user->setFlash('error', $message);
      }
      else
      {
        $this->saveClipboard($allSlugs, $password);

        // Inform user of progress
        $messageText = 'Clipboard saved. Clipoard ID is <b>%1%</b>. Please write this number down. ';
        $messageText .= 'When you want to reload this clipboard in the future, open the Clipboard menu, ';
        $messageText .= 'select <a href="%2%">Load clipboard</a>, and enter this number in the Clipboard ID field.';

        $loadUrl = $this->context->routing->generate(null, array('module' => 'user', 'action' => 'clipboardLoad'));
        $message = $this->context->i18n->__($messageText, array('%1%' => $password, '%2%' => $loadUrl));
        $this->context->user->setFlash('notice', $message);
      }
    } 

    $this->redirect(array('module' => 'user', 'action' => 'clipboard'));
  }

  private function getUniquePassword()
  {
    // Try a max of 100 times before giving up (avoid infinite loops when
    // possible passwords exhausted)
    for ($i = 0; $i < 100; $i++)
    {
      $password = $this->generatePassword();

      $criteria = new Criteria;
      $criteria->add(QubitClipboardSave::PASSWORD, $password);

      $result = QubitClipboardSave::getOne($criteria);

      if (null === $result)
      {
        return $password;
      }
    }

    return false;
  }

  private function generatePassword()
  {
    $passwordLength = 7;
    $alphabet = '0123456789';
    $alphabetSize = strlen($alphabet);

    $password = '';
    for ($i = 0; $i < $passwordLength; $i++)
    {
      $password .= $alphabet[mt_rand(0, $alphabetSize - 1)];
    }

    return $password;
  }

  private function saveClipboard($allSlugs, $password)
  {
    // Create save clipboard using password
    $save = new QubitClipboardSave;
    $save->userId = $this->context->user->getUserID();
    $save->password = $password;
    $save->save();

    // Store clipboard items in database
    foreach($allSlugs as $className => $slugs)
    {
      foreach($slugs as $slug)
      {
        $item = new QubitClipboardSaveItem;
        $item->saveId = $save->id;
        $item->itemClassName = $className;
        $item->slug = $slug;
        $item->save();
      }
    }
  }
}
