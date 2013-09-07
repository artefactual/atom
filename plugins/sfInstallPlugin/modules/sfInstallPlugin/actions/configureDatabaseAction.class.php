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

class sfInstallPluginConfigureDatabaseAction extends sfAction
{
  public function execute($request)
  {
    $this->database = array();

    $this->form = new sfForm;

    // Do *NOT* load defaults from existing database configuration because
    // anyone can access install actions if the database can't be accessed.
    // Never expose the database configuration, even to administrators

    $this->form->setDefault('databaseHost', 'localhost');
    $this->form->setValidator('databaseHost', new sfValidatorString);
    $this->form->setWidget('databaseHost', new sfWidgetFormInput);

    $this->form->setDefault('databaseName', 'atom');
    $this->form->setValidator('databaseName', new sfValidatorString(array('required' => true)));
    $this->form->setWidget('databaseName', new sfWidgetFormInput);

    $this->form->setValidator('databasePassword', new sfValidatorString);
    $this->form->setWidget('databasePassword', new sfWidgetFormInputPassword);

    $this->form->setDefault('databasePort', '3306');
    $this->form->setValidator('databasePort', new sfValidatorString);
    $this->form->setWidget('databasePort', new sfWidgetFormInput);

    $this->form->setDefault('databaseUsername', 'root');
    $this->form->setValidator('databaseUsername', new sfValidatorString);
    $this->form->setWidget('databaseUsername', new sfWidgetFormInput);

    if ($request->isMethod('post'))
    {
      $this->form->bind($request->getPostParameters());

      if ($this->form->isValid())
      {
        $this->database = sfInstall::configureDatabase($this->form->getValues());
        if (count($this->database) < 1)
        {
          $symlinks = sfInstall::addSymlinks();

          $this->redirect(array('module' => 'sfInstallPlugin', 'action' => 'configureSearch'));
        }
      }
    }
  }
}
