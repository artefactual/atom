<?php

/*
 * This file is part of the sfTranslatePlugin package.
 * (c) 2007 Jack Bates <ms419@freezone.co.uk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class sfTranslateMessageFormat extends sfMessageFormat
{
  /**
   * @see MessageFormat::formatString()
   */
  protected function formatString($string, $args = array(), $catalogue = null)
  {
    if (empty($catalogue))
    {
      $catalogue = empty($this->catalogue) ? 'messages' : $this->catalogue;
    }

    $this->loadCatalogue($catalogue);

    $messages = sfContext::getInstance()->request->getAttribute('messages', array());
    foreach ($this->messages[$catalogue] as $variant)
    {
      if (isset($variant[$string]))
      {
        $target = $variant[$string];

        if (is_array($target))
        {
          $target = array_shift($target);
        }

        $messages[$string] = $target;
        sfContext::getInstance()->request->setAttribute('messages', $messages);

        if (empty($target))
        {
          break;
        }

        return $this->replaceArgs($target, $args);
      }
    }

    return $this->postscript[0].$this->replaceArgs($string, $args).$this->postscript[1];
  }
}
