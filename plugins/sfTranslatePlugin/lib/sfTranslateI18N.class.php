<?php

/*
 * This file is part of the sfTranslatePlugin package.
 * (c) 2007 Jack Bates <ms419@freezone.co.uk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class sfTranslateI18N extends sfI18N
{
  /**
   * @see I18N::getMessageFormat()
   */
  public function getMessageFormat()
  {
    if (!isset($this->messageFormat))
    {
      $this->messageFormat = new sfTranslateMessageFormat($this->getMessageSource(), sfConfig::get('sf_charset'));

      if (sfConfig::get('sf_debug') && sfConfig::get('sf_i18n_debug'))
      {
        $this->messageFormat->setUntranslatedPS(array(sfConfig::get('sf_i18n_untranslated_prefix'), sfConfig::get('sf_i18n_unstranslated_suffix')));
      }
    }

    return $this->messageFormat;
  }
}
