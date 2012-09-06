<?php

/*
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

    $this->form->setValidator('databaseHost', new sfValidatorString);
    $this->form->setWidget('databaseHost', new sfWidgetFormInput);

    $this->form->setDefault('databaseName', 'qubit');
    $this->form->setValidator('databaseName', new sfValidatorString(array('required' => true)));
    $this->form->setWidget('databaseName', new sfWidgetFormInput);

    $this->form->setValidator('databasePassword', new sfValidatorString);
    $this->form->setWidget('databasePassword', new sfWidgetFormInputPassword);

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

          $this->redirect(array('module' => 'sfInstallPlugin', 'action' => 'loadData'));
        }
      }
    }
  }
}
