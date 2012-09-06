<?php

/*
 */

class sfInstallPluginIndexAction extends sfAction
{
  public function execute($request)
  {
    $this->redirect(array('module' => 'sfInstallPlugin', 'action' => 'checkSystem'));
  }
}
