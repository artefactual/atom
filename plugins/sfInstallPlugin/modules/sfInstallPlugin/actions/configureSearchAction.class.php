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

class sfInstallPluginConfigureSearchAction extends sfAction
{
  public function execute($request)
  {
    $this->search = array();

    $this->form = new sfForm;

    // Do *NOT* load defaults from existing search configuration because
    // anyone can access install actions if the database can't be accessed.
    // Never expose the search configuration, even to administrators

    $this->form->setDefault('searchHost', 'localhost');
    $this->form->setValidator('searchHost', new sfValidatorString);
    $this->form->setWidget('searchHost', new sfWidgetFormInput);

    $this->form->setDefault('searchPort', '9200');
    $this->form->setValidator('searchPort', new sfValidatorString);
    $this->form->setWidget('searchPort', new sfWidgetFormInput);

    $this->form->setDefault('searchIndex', 'atom');
    $this->form->setValidator('searchIndex', new sfValidatorString(array('required' => true)));
    $this->form->setWidget('searchIndex', new sfWidgetFormInput);

    if ($request->isMethod('post'))
    {
      $this->form->bind($request->getPostParameters());

      if ($this->form->isValid())
      {
        $this->errors = sfInstall::configureSearch($this->form->getValues());
        if (count($this->errors) < 1)
        {
          $symlinks = sfInstall::addSymlinks();

          $this->redirect(array('module' => 'sfInstallPlugin', 'action' => 'loadData'));
        }
      }
    }
  }
}
