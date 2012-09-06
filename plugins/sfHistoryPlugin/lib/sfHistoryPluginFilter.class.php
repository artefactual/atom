<?php

/*
 * This file is part of the sfHistoryPlugin package.
 * (c) 2007 Jack Bates <ms419@freezone.co.uk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class sfHistoryPluginFilter extends sfFilter
{
  /**
   * @see Filter::execute()
   */
  public function execute($filterChain)
  {
    $filterChain->execute();

    $user = $this->context->user;
    $user->setAttribute('moduleName', $this->context->getModuleName(), 'sfHistoryPlugin');
    $user->setAttribute('actionName', $this->context->getActionName(), 'sfHistoryPlugin');
    $user->setAttribute('currentInternalUri', $this->context->getRouting()->getCurrentInternalUri(), 'sfHistoryPlugin');
  }
}
