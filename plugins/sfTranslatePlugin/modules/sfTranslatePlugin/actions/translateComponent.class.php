<?php

/*
 * This file is part of the sfTranslatePlugin package.
 * (c) 2007 Jack Bates <ms419@freezone.co.uk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class sfTranslatePluginTranslateComponent extends sfComponent
{
  public function execute($request)
  {
    $this->messages = $request->getAttribute('messages');
    if (empty($this->messages))
    {
      return sfView::NONE;
    }

    ksort($this->messages);
  }
}
