<?php

/*
 * This file is part of the sfTranslatePlugin package.
 * (c) 2007 Jack Bates <ms419@freezone.co.uk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * i18n actions.
 *
 * @package    symfony
 * @subpackage i18n
 * @author     Your name here
 */
class sfTranslatePluginTranslateAction extends sfAction
{
  /**
   * Executes index action
   *
   */
  public function execute($request)
  {
    if (!QubitAcl::check('userInterface', 'translate'))
    {
      QubitAcl::forwardUnauthorized();
    }

    $user = $this->context->user;

    $error = array();
    $status = array();

    $messageSource = $this->context->i18n->getMessageSource();

    $sourceMessages = $request->getParameter('source', array());
    $targetMessages = $request->getParameter('target', array());
    foreach ($sourceMessages as $key => $sourceMessage)
    {
      if (!$messageSource->update($sourceMessage, $targetMessages[$key], null))
      {
        $error[] = $sourceMessage.$targetMessages[$key];
      }
      else
      {
        $status[] = $sourceMessage.$targetMessages[$key];
      }
    }

    if (!empty($error))
    {
      $this->forward($user->getAttribute('moduleName', 'default', 'sfHistoryPlugin'), $user->getAttribute('actionName', 'index', 'sfHistoryPlugin'));
    }

    $messageSource->getCache()->clean();

    $this->redirect($request->getReferer());
  }
}
