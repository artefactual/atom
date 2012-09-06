<?php

/*
 */

class sfInstallPluginCallbackAction extends sfAction
{
  public function execute($request)
  {
    // Say the magic words
    echo 'Open-Source PHP Web Framework';

    return sfView::NONE;
  }
}
